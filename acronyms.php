<?php


include("header.php");
include ("db.class.php");

$display_table = "";
$i = 0;
$count = 0;
$total = 0;

$tag_count = array();
$percentage = array();
$fail = array();

if(isset($_POST['tag']))
{
    if(isset($_GET['acronym']) && isset($_GET['category']) && isset($_GET['definition']))
    {
        $acr = mysqli_real_escape_string($db_connect,$_GET['acronym']);
        $cat = mysqli_real_escape_string($db_connect,$_GET['category']);
        $def = mysqli_real_escape_string($db_connect,$_GET['definition']);

        if($cat == "None")
        {
             $select_tag = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym='$acr' AND Definition ='$def'");
        }
        else
        {
             $select_tag = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym='$acr' AND Definition ='$def' AND Category='$cat'");
        }

        

        if(mysqli_num_rows($select_tag) == 1)
        {   
            $update_count = mysqli_query($db_connect, "Update acronymlist SET Tag_Count = Tag_Count + 1 where Acronym='$acr' AND Definition ='$def' AND Category='$cat'");
            echo "<div class='alert alert-success alert-dismissable'> Thank you for tagging your acronym<b></b></div>";
        }
        else
        {
            echo "<div class='alert alert-danger alert-dismissable'> Acronym, Definition and/or Category is incorrect <b></b></div>";
        }

    }
    else
    {
       echo "<div class='alert alert-danger alert-dismissable'><b>An error occured with the acronym, category and/or definition.</b></div>";
    }
}

if(isset($_POST['submit']))
 {
    if(isset($_POST['acronym']))
    {
        if(isset($_GET['category']))
        {
            $categories = array("None","Business","IT", "Medical", "Mili-Gov");
            $category = mysqli_real_escape_string($db_connect,$_GET['category']);
            $acronym = mysqli_real_escape_string($db_connect,$_POST['acronym']);

            if(in_array($category, $categories))
            {
                 if($category == "None")
                 {
                    $query = mysqli_query($db_connect, "Select * from acronymlist where Acronym LIKE '$acronym%'");
                 }
                 else
                 {
                    $query = mysqli_query($db_connect, "Select * from acronymlist where Acronym LIKE '$acronym%' AND Category='$category'");
                 }

                 if(mysqli_num_rows($query) >= 1)
                 {   

                    if($category == "None")
                    {
                     $select_All_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym LIKE '$acronym%'");
                     $sum =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym LIKE '$acronym%'");
                     $total_sum = mysqli_fetch_array($sum);
                    }
                    else
                    {
                     $select_All_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym LIKE '$acronym%' AND Category='$category'");
                     $sum =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym LIKE '$acronym%' AND Category='$category'");
                     $total_sum = mysqli_fetch_array($sum);
                    }

                    while($row = mysqli_fetch_array($select_All_Tags))
                    {
                        $tag_count[$count] = $row['Tag_Count'];
                        $percentage[$count] = ($tag_count[$count] / $total_sum['Total_Sum']) * 100;
                        $count++;
                    }

                     $display_table = "<div class='alert alert-success alert-dismissable'><b>Successful Search!</b></div>
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
                        
                            $acs = array();
                            $defs = array();
                            $cats = array();

                            while($row = mysqli_fetch_array($query))
                            {
                                $acronym = $row['Acronym'];
                                $acs[$i] = $acronym;

                                $definition = $row['Definition'];
                                $defs[$i] = $definition;

                                $category = $row['Category'];
                                $cats[$i] = $category;

                                $display_table = $display_table . "<tr id='$i' data-toggle='modal' data-target='#myModal$i'>"
                                . "<td>" . $acronym . "</td>"
                                . "<td>" . $definition. "</td>"
                                . "<td>" . $category . "</td>"
                                . "<td><div class='progress'><div class='progress-bar progress-bar-success' style='width:" . $percentage[$i]. "%'> 
                                <span class='sr-only'>50% Likely </span></div><div class='progress-bar progress-bar-danger' style='width: " . (100 - $percentage[$i]) . "%'>
                                <span class='sr-only'>50% Unlikely </span></div></td><div class='modal fade' id='myModal$i'>
                                                      <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                          <div class='modal-header'>
                                                            <h4 class='modal-title'>Tag this acronym</h4>
                                                          </div>
                                                          <div class='modal-body'>
                                                            <p><b> Is this the acronym you were searching for:  </b></p>
                                                            <p> Acronym: $acs[$i] </p>
                                                            <p> Definition: $defs[$i] </p>
                                                            <p> Category: $cats[$i] </p>
                                                            <p> If so please click tag, else click close to return. </p>
                                                          </div>
                                                          <div class='modal-footer'>
                                                            <form method='post' action='acronyms.php?category=$cats[$i]&definition=$defs[$i]&acronym=$acs[$i]'> 
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

                            $display_table = $display_table . "</tbody></table></div>";
                 }
                 else
                 {

                    echo "<div class='alert alert-warning alert-dismissable'><b>Sorry, No acronym found. You can suggest the meaning of this acronym
                     <a href='suggest.php' class='alert-link'> by clicking here </a></b></div>";
                 }
            }
            else
            {
                echo "<div class='alert alert-danger alert-dismissable'><b>Category does not exist! </b></div>";
            }

        }
        else
        {
            echo "<div class='alert alert-danger alert-dismissable'><b>Category does not exist! This may have been cause by modifiying the URL</b></div>";
        }
    }
    else
    {
        echo "<div class='alert alert-danger alert-dismissable'><b>Please Enter an Acronym!</b></div>";
    }
 }
?>  

<html>
<head>
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
</head>
<body>
<div class ="container">
	<div class="jumbotron">
		<ul class="nav nav-tabs nav-justified" id="myTab">
            <li <?=updateMenu("/acronyms.php?category=None")?>><a href="?category=None"> None </a></li>
            <li <?=updateMenu("/acronyms.php?category=Medical")?>><a href="?category=Medical"> Medical </a></li>
            <li <?=updateMenu("/acronyms.php?category=IT")?>><a href="?category=IT"> IT </a></li>
            <li <?=updateMenu("/acronyms.php?category=Business")?>><a href="?category=Business"> Business </a></li>
            <li <?=updateMenu("/acronyms.php?category=Mili-Gov")?>><a href="?category=Mili-Gov"> Military/Government </a></li> 
    </ul>

     <br/>

     <form class="form-horizontal" id="acronym-form" method="post" role="form">
	 	<div class="form-group">
	   	  <label for="inputAcronym" class="col-sm-3 control-label">Acronym</label>
	   		<div class="col-sm-6">
	     		<input type="text" name="acronym" class="form-control" id="inputAcronym" minLength="1" placeholder="Search..." required>
	   		</div>
	    </div>
        <button type="submit" name="submit" class="btn btn-primary button-right"> Search </button>
	</form>	
</div>
</div>
<div id="table-pos">
 <?php
    echo "$display_table";
 ?>
</div>
      <script>
            $("#acronym-form").validate();
        </script>
</body>
</html>