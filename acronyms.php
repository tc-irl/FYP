<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Acronyms page -> Allows the users to search for single acronyms, such as "OS", on this page. 

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

This page also uses a JQuery Validation Plugin to help with client side validation. See: http://jqueryvalidation.org/validate/
-->

<?php

include("header.php"); // including the header page (for the title, navigation bar, and other functions)
include ("db.class.php"); // including the db class (connecting to the database)

global $display_table;

$i = 0;
$count = 0;

$tag_count = array();
$percentage = array();


// checking if the user has submitted the tag form. 
if(isset($_POST['tag']))
{
    // server-side validation, ensuring that all the required fields are set in order to update the site. 
    if(isset($_GET['acronym']) && isset($_GET['category']) && isset($_GET['definition']))
    {
       // here we assign the filled in fields to their relevant variables. Note, mysqli_real_escape_string helps prevent against sql injection 
       // by escaping special characters in the string. See: http://www.w3schools.com/php/func_mysqli_real_escape_string.asp for more information. 
        $acr = mysqli_real_escape_string($db_connect,$_GET['acronym']); 
        $cat = mysqli_real_escape_string($db_connect,$_GET['category']);
        $def = mysqli_real_escape_string($db_connect,$_GET['definition']);

        // checking if the category selected is none or not. The query itself is checking if the acronym and definition exists. 
        if($cat == "None")
        {
             $select_tag = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym='$acr' AND Definition ='$def'");
        }
        else
        {
             $select_tag = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym='$acr' AND Definition ='$def' AND Category='$cat'");
        }

        
        // check if the acronym and definition exists, by checking if the query returns a row or not
        if(mysqli_num_rows($select_tag) == 1)
        {   
            $update_count = mysqli_query($db_connect, "Update acronymlist SET Tag_Count = Tag_Count + 1 where Acronym='$acr' AND Definition ='$def' AND Category='$cat'"); //Increases the number of times the acronym has been tagged by 1
            echo "<div class='alert alert-success alert-dismissable' style='margin-right: 232px; margin-left: 202px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Thank you for tagging your acronym</b></div>"; // display message
        }
        else
        {
            echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 120px; margin-left: 90px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Acronym, Definition and/or Category is incorrect </b></div>"; // display message
        }

    }
    else
    {
       echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 120px; margin-left: 90px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>An error occured with the acronym, category and/or definition.</b></div>"; // display message
    }
}

// checking if the user has clicked the search button
if(isset($_POST['submit']))
 {
    // server-side validation, ensuring they have entered the acronym
    if(isset($_POST['acronym']))
    {
        // server-side validation, ensuring a category has been selected. 
        if(isset($_GET['category']))
        { 
            $categories = array("None","Business","IT", "Medical", "Mili-Gov"); // array containing the categories


            // here we assign the filled in the category and acronym to variables. Note, mysqli_real_escape_string helps prevent against sql injection 
           // by escaping special characters in the string. See: http://www.w3schools.com/php/func_mysqli_real_escape_string.asp for more information.
            $category = mysqli_real_escape_string($db_connect,$_GET['category']);
            $acronym = mysqli_real_escape_string($db_connect,$_POST['acronym']);

            // making sure the category selected is in the array of categories such as where the user modifies the category in the url.
            if(in_array($category, $categories))
            {
                 if($category == "None")
                 {
                    $query = mysqli_query($db_connect, "Select * from acronymlist where Acronym LIKE '$acronym%'"); // querying database for the acronyms
                 }
                 else
                 {
                    $query = mysqli_query($db_connect, "Select * from acronymlist where Acronym LIKE '$acronym%' AND Category='$category'"); // querying database for the acronyms
                 }

                 // check if the acronym exists, by checking if the query returns a row or not
                 if(mysqli_num_rows($query) >= 1)
                 {   
                    // checking if the category selected is none or not
                    if($category == "None")
                    {
                     $select_All_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym LIKE '$acronym%'"); // getting the tag count of relevant acronyms from the database
                     $sum =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym LIKE '$acronym%'"); // getting the sum of the acronyms (in case multiple definitions of the same acronym exists)
                     $total_sum = mysqli_fetch_array($sum); // total sum 
                    }
                    else
                    {
                     $select_All_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym LIKE '$acronym%' AND Category='$category'"); // getting the tag count of relevant acronyms from the database
                     $sum =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym LIKE '$acronym%' AND Category='$category'"); // getting the sum of the acronyms (in case multiple definitions of the same acronym exists)
                     $total_sum = mysqli_fetch_array($sum); // total sum
                    }

                    // loop through the array $select_all_tags, to the get the number of tags for each acronym being displayed
                    while($row = mysqli_fetch_array($select_All_Tags))
                    {
                        $tag_count[$count] = $row['Tag_Count']; // store the tags into an array
                        $percentage[$count] = ($tag_count[$count] / $total_sum['Total_Sum']) * 100; // calculate the percentage of likelihood for each 
                        $count++; // increase count for next loop
                    }
                        // creating the table and setting the table headings 
                         $display_table = "
                         <div class='table-responsive'>
                         <table class='table table-striped table-condensed table-bordered table-hover' id='table-id'>
                          <thead>
                              <tr>
                                <th>Acronym</th>
                                <th>Definition</th>
                                <th>Category</th>
                                <th>Likelihood</th>
                              </tr>
                            </thead>
                            <tbody>";

                            $display_table = $display_table . "<div class='alert alert-success alert-dismissable' style='margin-right: 120px; margin-left: 90px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Successful Search!</b></div>";

                            // loop through each result of the query, getting the acronym, definition and category found. 
                            while($row = mysqli_fetch_array($query))
                            {
                                $acronym = $row['Acronym']; // store acronym into the acronym variable
                                $definition = $row['Definition']; // store definition into the definition variable
                                $category = $row['Category']; // store category into category variable

                                // appending to the display_table variable, adding rows to the table in order to populate it. 
                                // the "modal" aspect of the table is referring to the pop up box which appears when you click on a row
                                // in order to tag the acronym you were looking for. The percentage variable will display the percentage of likely or unlikely
                                // Percentage refers to the likely percentage and 100 - percentage will then return the unlikely percentage
                                // The modal, progress bar and table layout are all usable features of Twitter Bootstrap
                                $display_table = $display_table . "<tr id='$i' data-toggle='modal' data-target='#myModal$i'>"
                                . "<td>" . $acronym . "</td>"
                                . "<td>" . $definition. "</td>"
                                . "<td>" . $category . "</td>"
                                . "<td><div class='progress'><div class='progress-bar progress-bar-success' data-title=". round($percentage[$i],2) . '%' . " data-placement='left' data-trigger='hover' style='width:" . $percentage[$i]. "%'> 
                                <span class='sr-only'></span></div><div class='progress-bar progress-bar-danger' data-title=" . round((100 - $percentage[$i]),2) . '%' . " data-placement='right' data-trigger='hover' style='width: " . (100 - $percentage[$i]) . "%'>
                                <span class='sr-only'></span></div></td><div class='modal fade' id='myModal$i'>
                                                      <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                          <div class='modal-header'>
                                                            <h4 class='modal-title'>Tag this acronym</h4>
                                                          </div>
                                                          <div class='modal-body'>
                                                            <p><b> Is this the acronym you were searching for:  </b></p>
                                                            <p> Acronym: " . $acronym . " </p>
                                                            <p> Definition: " . $definition . " </p>
                                                            <p> Category: " . $category . "</p>
                                                            <p> If so please click tag, else click close to return. </p>
                                                          </div>
                                                          <div class='modal-footer'>
                                                            <form method='post' action='acronyms.php?category=$category&definition=$definition&acronym=$acronym'> 
                                                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                                                            <button type='submit' name='tag' class='btn btn-primary'>Tag</button>
                                                            </form>
                                                          </div>
                                                        </div>
                                                      </div>
                                                    </div>
                                </tr>";
                                $i++;

                            } 

                            $display_table = $display_table . "</tbody></table></div>"; // close the table
                 }
                 else
                 {

                    echo "<div class='alert alert-warning alert-dismissable' style='margin-right: 232px; margin-left: 202px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Sorry, No acronym found. You can suggest the meaning of this acronym
                     <a href='suggest.php' class='alert-link'> by clicking here </a></b></div>"; // display message
                 }
            }
            else
            {
                echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 232px; margin-left: 202px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Category does not exist! </b></div>"; // display message
            }

        }
        else
        {
            echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 232px; margin-left: 202px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Category does not exist! This may have been cause by modifiying the URL</b></div>"; // display message
        }
    }
    else
    {
        echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 232px; margin-left: 202px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Please Enter an Acronym!</b></div>"; // display message
    }
 }
?>  

<html>
<head>
   <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script> 
</head>
<body>
<div class="message" id="message"></div>
<div class ="container"> <!-- Twitter bootstrap container for common fixed-width layout -->
	<div class="jumbotron"> <!-- Twitter bootstrap jumbotron for the grey background of body -->
		<ul class="nav nav-tabs nav-justified" id="myTab"> <!-- Navigation bar for the categories. updateMenu() is used highlight the active tab. Navigation bar is part of Twitter bootstrap--> 
            <li <?=updateMenu("/acronyms.php?category=None")?>><a href="?category=None"> None </a></li>
            <li <?=updateMenu("/acronyms.php?category=Medical")?>><a href="?category=Medical"> Medical </a></li>
            <li <?=updateMenu("/acronyms.php?category=IT")?>><a href="?category=IT"> IT </a></li>
            <li <?=updateMenu("/acronyms.php?category=Business")?>><a href="?category=Business"> Business </a></li>
            <li <?=updateMenu("/acronyms.php?category=Mili-Gov")?>><a href="?category=Mili-Gov"> Military/Government </a></li> 
    </ul>

     <br/>
  <!-- Acronym Search Form. Twitter bootstrap form elements --> 
  <form class="form-horizontal" id="acronym-form" method="post" role="form">
	 	<div class="form-group">
	   	  <label for="inputAcronym" class="col-sm-3 control-label"> Acronym </label>
	   		<div class="col-sm-6">
	     		<input type="text" name="acronym" class="form-control" id="inputAcronym" minLength="1" maxLength="10" placeholder="Search..." required>
	   		</div>
	   </div>
        <button type="submit" name="submit" class="btn btn-primary button-right"> Search </button>
	</form>	
</div>
<!-- JQuery validation plugin. validate() - Validates the form. highlight() - specifies how to highlight the invalid fields and unhighlight() specifies how to respond to valid fields. See: http://jqueryvalidation.org/validate/ --> 
<script>
          $(document).ready(function(){
              $("#acronym-form").validate({
                  highlight: function (element) {
                      $(element).closest('.form-group').removeClass('has-success').addClass('has-error'); 
                  },
                  unhighlight: function (element) {
                      $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                  }
              });

              $(".progress-bar").tooltip(); // displaying a tooltip when the user hoovers over it. Part of Twitter Bootstrap. 

          });       


        </script>

<div id="table-pos">
 <?php
    echo "$display_table"; // displaying the table
 ?>
</div>
</div>
      
</body>
</html>