<!--

Author: Tony Cullen (C10385847)
Project: Acronym Identification System
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Page: Index page -> The main page and first page of the site where users enter the site. It contains the dropzone, 
where users can uploaded their file and it will return 2 tables. The first table will return all acronyms found in the text which matches the category selected.
If the user selected "None" as the category (instead of IT, Medical, Business or Mili-Gov, the document will be automatically classified. 
The second table will return acronyms found which are found in a different category in the database. 
Any acronyms found in the document, but not found in the database, will suggest different possible acronyms that were meant. For example, "OK" might have meant OS.
This is done using the levenshtein (edit) distance. 

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

The page also contains a dropzone using Dropzone JS. See: http://www.dropzonejs.com/ 

This page also uses a PosTagger, which helps return only the nouns found within the text. This is used because the system identifies acronyms as either capitalized
words or nouns (examples: laser, scuba)

This page also makes use of Doc2Txt class in order to convert doc/docx files to be converted to txt files. 
Class can be downloaded from: http://www.phpclasses.org/package/7934-PHP-Convert-MS-Word-Docx-files-to-text.html
 
-->

<?php

require("classifier.php");
require("header.php");
require("doc2txt.class.php");
require("tagger.class.php");
require("db.class.php");


use Camspiers\StatisticalClassifier\Classifier\ComplementNaiveBayes; // using complement naive bayes class
use Camspiers\StatisticalClassifier\Model\CachedModel; // using a cached model
use Camspiers\StatisticalClassifier\DataSource\DataArray; // using a data array

$tagger = new PosTagger('./acronyms/lexicon.txt'); // creating an instance of the Part of speech tagger

$done = "false";
$display_table = ""; 
$display_Main = "";
$display_Other= "";

$j = 0;
$count2 = 0;


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
          echo "<div class='alert alert-success alert-dismissable' style='margin-right: 225px; margin-left: 195px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Thank you for tagging your acronym</b></div>"; // display message
      }
      else
      {
          echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Acronym, Definition and/or Category is incorrect </b></div>"; // display message
      }

  }
  else
  {
     echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>An error occured with the acronym, category and/or definition.</b></div>"; // display message
  }
}

if(isset($_GET['message']))
{

  echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b> ". $_GET['message'] ."</b></div>"; // display message
}
else
{
  // server-side validation, ensuring the category and file names are set
  if(isset($_GET['filename']) && isset($_GET['category']))
  {
    // server-side validation, ensuring the user is logged in
    if(isset($_SESSION['username']))
    {
      $path = "./uploads/" . $_SESSION['username']; // setting the path to where to store the uploaded file (temporarily)
      $fname = $_GET['filename'];

      $ext = pathinfo($fname, PATHINFO_EXTENSION); // getting the pathinfo extension i.e. file type -> .txt, .doc etc..

      // ensuring that the file type is txt, doc or docx
      if($ext == "txt" || $ext == "doc" || $ext == "docx")
      {
          if(file_exists($path. "/" . $fname))
          {
              $file_info = file_get_contents($path . "/" . $fname); // get the file

              // making sure the file is not empty
              if(!empty($file_info))
              { 
                // if the file extension is 
                if($ext == "doc" || $ext == "docx")
                {
                  $docObj = new Doc2Txt($path . "/" . $fname); // create a new instance of the doc2txt class
                  $txt = $docObj->convertToText(); // using the Doc2Txt class to convert the file to a text file

                  $tags = $tagger->tag($txt); // use the tagger on the text i.e. return the types of each word such as noun, adverb etc..

                  $oldtxt = cleanOldTxt($txt); // cleans up the file
                  $cleanTxt = cleanUpFile($txt, $tags); // cleans up the file to a further extent.
                  $classified_cat = classifyDocument($cleanTxt); // gets the documents category using classification
                  displayTable($classified_cat,$oldtxt,$cleanTxt,$db_connect); // displays the table
                }
                else
                {
                  $tags = $tagger->tag($file_info); // use the tagger on the text i.e. return the types of each word such as noun, adverb etc..
                  $oldtxt = cleanOldTxt($file_info); // cleans up the file
                  $cleanTxt = cleanUpFile($file_info, $tags); // cleans up the file to a further extent.
                  $classified_cat = classifyDocument($cleanTxt); // gets the documents category using classification
                  displayTable($classified_cat,$oldtxt,$cleanTxt,$db_connect); // displays the table
                }
              }
              else
              {
                echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>File is empty </b></div>"; // Display message
              }
          }
          else
          {
            echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Sorry, an error has occurred. This may have been caused by modifying the url.</b></div>"; // Display message
          }
    }
    else
    {
      echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Sorry, only .txt, .doc and .docx files are allowed</b></div>"; // Display message
    }

  }
  else
  {
    echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Please login to continue. You can login <a href=loginreg.php?page=login' class='alert-link'> by clicking here </a></b></div>"; // Display message
  }
 }
}

// function which returns the category
function getCategory()
{
  $cat =  $_GET['category'];
  echo "value=" . $cat;
}

// cleans the file, to a lesser extent than cleanUpFile() function. This function is used for cleaning the text, but not removing stop words or nouns. 
// We do this because we need the original text in order to search an n-word window to see if the acronym's definition is left or right of it in the text
function cleanOldTxt($oldtxt)
{
  $oldtxt = str_replace(array('.', ',','-','/','(',')','[',']','\\','!','&','"','*','_'), '' , $oldtxt); // removing full stops, commas, dashes etc...
  $oldtxt = str_replace(array("\n","\r"), " ", $oldtxt); // replacing \n and \r tags with a space, so we can successfully read a new line
  $oldtxt = preg_replace('/\s\s+/', ' ', $oldtxt); // replacing multiple spaces with a single space

  return $oldtxt;
}

// Checks if the 2 acronyms passed in match each other and if they don't cross out the $a acronym. 
// This is used for the levensthein distance where we cross out the acronyms found but not recognized in the database, and show suggested acronyms 
function checkMatch($acronymB, $a)
{
    if($acronymB != $a) 
    { 
      return "<del>" . $a . "</del>";
    } 
}

// function which is used to display the tables
function displayTable($classified_cat, $oldTxt,$cleanTxt,$db_connect)
{
  global $display_table;
  global $display_Other;
  global $display_Main;
  global $j;
  global $count2;

  $i = 0;
  $k = 0;
  $count = 0;
  $qCount = 0;
  $qCount2 = 0;
  $pos = 0;

  $query = array(); // array of queries
  $query2 = array(); // second array of queries for the second table
  $afterDefinition = array(); // definitions after acronyms
  $beforeDefinition = array(); // definitions before acronyms
  $tag_count = array(); // array for the number of tags each acronym has in the first table
  $tag_count2 = array(); // second array for tags in the other table
  $percentage = array(); // array of percentages of progress bar for first table
  $percentage2 = array(); // second array of percentages of progress bar for other table
  $acronymLength = array(); // array for the length of each acronym
  $acronyms = explode(" ", $cleanTxt); // explode the acronyms into array 
  $defs = explode(" ", $oldTxt); // explode the text (before non-nouns and stop words are removed)
  $categories = array("None","Business","IT", "Medical", "Mili-Gov"); // list of categoires
  $category = mysqli_real_escape_string($db_connect,$_GET['category']); // category selected
  $select_All_Tags = array(); // select tag query
  $select_Other_Tags = array(); // select other tags query
  $sum = array(); // sum of tags
  $sum_other = array(); // sum of tags in other table
  $allCatAcronyms = "";

  // checking if the category is none and if thats the case, use the category found through document classification
  if($category == "None")
  {
    $category = $classified_cat;
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

    $display_Main = $display_table . "<div class='alert alert-success alert-dismissable' style='margin-right: 120px; margin-left: 90px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Successful Search!</b></div>";
    $display_Other = "<div class='alert alert-warning alert-dismissable' style='margin-right: 120px; margin-left: 90px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>The following table contains acronyms found from a different category and/or suggested acronyms for acronyms that were found in the text but not recognized in the database. </b></div>" . $display_table;

// making sure the category selected is in the array of categories such as where the user modifies the category in the url.
if(in_array($category, $categories))
{
    // looping through each acronym
     foreach ($acronyms as $acronym) 
     {  
        // assign the acronym to the variable $a and using real escape string to help prevent sql injection
        $a = mysqli_real_escape_string($db_connect,$acronym);

        // making sure the acronym is in the original text
        if(in_array($acronym, $defs)) 
        {
           $acronymLength = strlen($acronym); // getting the length of the acronym
           $pos = array_search($acronym, $defs); // getting the position of the acronym in the original text
           
           for($l = 1; $l <= $acronymLength; $l++)
           {
              // We get the acronym length for the n-word window to search. We find the n words after the acronym by getting the pos + (acronym length - (acronym length - l))
              // where l is being incremented, starting at 1 up to the length of the acronym. Example, OS is of length 2. So we search the position of acronym in the text,
              // then we get the next words based on how long the acronym is -> such as OS (Operating System) 
              @$afterDefinition[$qCount] = @$afterDefinition[$qCount] . $defs[$pos + ($acronymLength - ($acronymLength - $l))] . " "; 
           }
           for($l = 0; $l < $acronymLength; $l++)
           {
              // We get the acronym length for the n-word window to search. We find the n words before the acronym by getting the pos - (acronym length - l)
              // where l is being incremented, starting at 0 up to the length of the acronym. Example, OS is of length 2. So we search the position of acronym in the text,
              // then we get the previous words based on how long the acronym is -> such as (Operating System) OS 
              @$beforeDefinition[$qCount] = @$beforeDefinition[$qCount] . $defs[$pos - ($acronymLength - $l)] . " ";
           }
           
          @$checkForDefBefore = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$beforeDefinition[$qCount]'"); // query which checks if the definition before matches that of the acronym
          @$checkForDefAfter = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$afterDefinition[$qCount]'"); // query which checks if the definition after matches that of the acronym

           if(@mysqli_num_rows($checkForDefBefore) >= 1)
           { 
               @$query[$qCount] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$beforeDefinition[$qCount]' AND Category='$category'"); // query which checks if the definition before matches that of the acronym and is in the category
               @$query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$beforeDefinition[$qCount]' AND Category !='$category'"); // query which checks if the definition before matches that of the acronym and is in another category
           }
           else if (@mysqli_num_rows($checkForDefAfter) >= 1) 
           {
               @$query[$qCount] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$afterDefinition[$qCount]' AND Category='$category'"); // query which checks if the definition after matches that of the acronym and is in the category
               @$query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$afterDefinition[$qCount]' AND Category !='$category'"); // query which checks if the definition after matches that of the acronym and is in another category
           }
           else
           {
             @$query[$qCount] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Category='$category'"); // query the database using acronyms found
             @$query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where (Acronym = '$a' AND Category != '$category') AND not exists (Select * from acronymlist where Acronym = '$a' AND Category = '$category')"); // query for the second table which returns the acronyms found, so long as they're not in the first table
           }

           // check if the acronyms exists, by checking if the query returns rows or not
           if(mysqli_num_rows($query[$qCount]) >= 1)
           {   
               $select_All_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym = '$a' AND Category='$category'"); // gets the tag count
               $sum =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym = '$a' AND Category='$category'"); // gets the sum of the tags for that acronym (in case multiple definitions of the same acronym exists)
               $total_sum = mysqli_fetch_array($sum); // gets the total sum for that acronym
           }

           // check if remaining acronyms exists, by checking if the query returns rows or not
           if(mysqli_num_rows($query2[$qCount2]) >= 1)
           {   
               $select_Other_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym = '$a' AND Category !='$category'"); // gets the tag count for acronym
               $sum_other =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym = '$a' AND Category !='$category'"); //  gets the sum of the tags for that acronym (in case multiple definitions of the same acronym exists)
               $total_other_sum = mysqli_fetch_array($sum_other); // gets the total sum for that acronym
           } 

          while(@$row = mysqli_fetch_array($select_Other_Tags))
          {
              @$tag_count2[$count2] = $row['Tag_Count']; // get the tag count of each acronym
              @$percentage2[$count2] = ($tag_count2[$count2] / $total_other_sum['Total_Sum']) * 100; // calculate the likely percentage
              $count2++; // increment counter
          }

          // checking if an acronym is found and is not in the database
          if ((mysqli_num_rows($query2[$qCount2]) < 1) &&  mysqli_num_rows($query[$qCount]) < 1)
          {
            $pos = 0;

            if(ctype_upper($acronym))
            {
              $allCatAcronyms = mysqli_query($db_connect, "Select * from acronymlist where Category = '$category'"); // selecting all acronyms for the category

              while($dbAcr = mysqli_fetch_array($allCatAcronyms))
              {
                 $dbAcronym = $dbAcr['Acronym']; // store database acronym into variable
                 $levenshtein[$pos] = levenshtein($a, $dbAcronym); // use php levenshtein function to calculate similar acronyms in the database to unidentified acronym

                // if difference between acronyms is 1 i.e. 1 character in the difference, then select it from the database.  
                if($levenshtein[$pos] == 1)
                {
                   $query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$dbAcronym'");

                   // get the tag count for that acronym and it's sum (in case multiple definitions of the same acronym exists)
                   $select_Other_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym = '$dbAcronym'");
                   $sum_other =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym = '$dbAcronym'");
                   $total_other_sum = mysqli_fetch_array($sum_other);

                   // loop through the array $select_Other_Tags, to the get the number of tags for each acronym being displayed
                  while(@$row = mysqli_fetch_array($select_Other_Tags))
                  { 
                      @$tag_count2[$count2] = $row['Tag_Count'];
                      @$percentage2[$count2] = ($tag_count2[$count2] / $total_other_sum['Total_Sum']) * 100;
                      $count2++;
                  }
                    // loop through each result of the query, getting the acronym, definition and category found.
                    while($row2 = mysqli_fetch_array($query2[$qCount2]))
                    { 

                        $acronymB = $row2['Acronym'];
                        $definitionB = $row2['Definition'];
                        $categoryB = $row2['Category'];

                        // appending to the display_Other variable, adding rows to the table in order to populate it. 
                        // the "modal" aspect of the table is referring to the pop up box which appears when you click on a row
                        // in order to tag the acronym you were looking for. The percentage variable will display the percentage of likely or unlikely
                        // Percentage refers to the likely percentage and 100 - percentage will then return the unlikely percentage
                        // The modal, progress bar and table layout are all usable features of Twitter Bootstrap
                        @$display_Other = $display_Other . "<tr id='$j' data-toggle='modal' data-target='#myModal2$j'>"
                        . "<td>" . checkMatch($acronymB, $a) . " " . $acronymB . "</td>"
                        . "<td>" . $definitionB . "</td>"
                        . "<td>" . $categoryB . "</td>"
                        . "<td><div class='progress'><div class='progress-bar progress-bar-success' data-title=". round($percentage2[$j],2) . '%' . " data-placement='left' data-trigger='hover' style='width:" . $percentage2[$j] . "%'> 
                        <span class='sr-only'>50% Likely </span></div><div class='progress-bar progress-bar-danger' data-title=" . round((100 - $percentage2[$j]),2) . '%' . " data-placement='right' data-trigger='hover' style='width: " . (100 - $percentage2[$j]) . "%'>
                        <span class='sr-only'>50% Unlikely </span></div></td><div class='modal fade' id='myModal2$j'>
                                              <div class='modal-dialog'>
                                                <div class='modal-content'>
                                                  <div class='modal-header'>
                                                    <h4 class='modal-title'>Tag this acronym</h4>
                                                  </div>
                                                  <div class='modal-body'>
                                                    <p><b> Is this the acronym you were searching for:  </b></p>
                                                    <p> Acronym: " . $acronymB . " </p>
                                                    <p> Definition: ". $definitionB . " </p>
                                                    <p> Category: " . $categoryB . "  </p>
                                                    <p> If so please click tag, else click close to return. </p>
                                                  </div>
                                                  <div class='modal-footer'>
                                                    <form method='post' action='index.php?category=$categoryB&definition=$definitionB&acronym=$acronymB]'> 
                                                    <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                                                    <button type='submit' name='tag' class='btn btn-primary'>Tag</button>
                                                    </form>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                        </tr>";
                        $j++;
                    } 
                  $qCount2++;
                }

                $pos++;
              }
            }

           }

           // loop through the array $select_All_Tags, to the get the number of tags for each acronym being displayed
           while(@$row = mysqli_fetch_array($select_All_Tags))
           {
              $tag_count[$count] = $row['Tag_Count'];
              $percentage[$count] = ($tag_count[$count] / $total_sum['Total_Sum']) * 100;
              $count++;
           }

            // loop through each result of the query, getting the acronym, definition and category found.
            while($row = mysqli_fetch_array($query[$qCount]))
            {

                $acronym = $row['Acronym'];
                $definition = $row['Definition'];
                $category = $row['Category'];

                // appending to the display_Main variable, adding rows to the table in order to populate it. 
                // the "modal" aspect of the table is referring to the pop up box which appears when you click on a row
                // in order to tag the acronym you were looking for. The percentage variable will display the percentage of likely or unlikely
                // Percentage refers to the likely percentage and 100 - percentage will then return the unlikely percentage
                // The modal, progress bar and table layout are all usable features of Twitter Bootstrap

                @$display_Main = $display_Main . "<tr id='$i' data-toggle='modal' data-target='#myModal$i'>"
                . "<td>" . $acronym . "</td>"
                . "<td>" . $definition . "</td>"
                . "<td>" . $category . "</td>"
                . "<td><div class='progress'><div class='progress-bar progress-bar-success' data-title=". round($percentage[$i],2) . '%' . " data-placement='left' data-trigger='hover' style='width:" . $percentage[$i] . "%'> 
                <span class='sr-only'>50% Likely </span></div><div class='progress-bar progress-bar-danger' data-title=" . round((100 - $percentage[$i]),2) . '%' . " data-placement='right' data-trigger='hover' style='width: " . (100 - $percentage[$i]) . "%'>
                <span class='sr-only'>50% Unlikely </span></div></td><div class='modal fade' id='myModal$i'>
                                      <div class='modal-dialog'>
                                        <div class='modal-content'>
                                          <div class='modal-header'>
                                            <h4 class='modal-title'>Tag this acronym</h4>
                                          </div>
                                          <div class='modal-body'>
                                            <p><b> Is this the acronym you were searching for:  </b></p>
                                            <p> Acronym:" . $acronym . " </p>
                                            <p> Definition:" . $definition . "  </p>
                                            <p> Category: " . $category . "  </p>
                                            <p> If so please click tag, else click close to return. </p>
                                          </div>
                                          <div class='modal-footer'>
                                            <form method='post' action='index.php?category=$category&definition=$definition&acronym=$acronym'> 
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

            // loop through each result of the query, getting the acronym, definition and category found.
            while(@$row3 = mysqli_fetch_array($query2[$qCount2]))
            { 

                $acronymC = $row3['Acronym'];
                $definitionC = $row3['Definition'];
                $categoryC = $row3['Category'];

                // Appending other rows to the display_Other table
                @$display_Other = $display_Other . "<tr id='$j' data-toggle='modal' data-target='#myModal3$j'>"
                . "<td>" . checkMatch($acronymC, $a) . $acronymC . "</td>"
                . "<td>" . $definitionC. "</td>"
                . "<td>" . $categoryC . "</td>"
                . "<td><div class='progress'><div class='progress-bar progress-bar-success' data-title=". round($percentage2[$j],2) . '%' . " data-placement='left' data-trigger='hover' style='width:" . $percentage2[$j] . "%'> 
                <span class='sr-only'></span></div><div class='progress-bar progress-bar-danger' data-title=" . round((100 - $percentage2[$j]),2) . '%' . " data-placement='right' data-trigger='hover' style='width: " . (100 - $percentage2[$j]) . "%'>
                <span class='sr-only'></span></div></td><div class='modal fade' id='myModal3$j'>
                                      <div class='modal-dialog'>
                                        <div class='modal-content'>
                                          <div class='modal-header'>
                                            <h4 class='modal-title'>Tag this acronym</h4>
                                          </div>
                                          <div class='modal-body'>
                                            <p><b> Is this the acronym you were searching for:  </b></p>
                                            <p> Acronym: " . $acronymC . " </p>
                                            <p> Definition: " . $definitionC . " </p>
                                            <p> Category:" . $categoryC . "</p>
                                            <p> If so please click tag, else click close to return. </p>
                                          </div>
                                          <div class='modal-footer'>
                                            <form method='post' action='index.php?category=$categoryC&definition=$definitionC&acronym=$acronymC'> 
                                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                                            <button type='submit' name='tag' class='btn btn-primary'>Tag</button>
                                            </form>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                </tr>";
                
                $j++;
            } 
        }
        $qCount++;
        $qCount2++;
  }
} 
 
 // close off tables
 $display_Main = $display_Main . "</tbody></table></div>";
 $display_Other = $display_Other. "</tbody></table></div>";
}

// function which cleans up the file in order for the acronyms to be easily identified
function cleanUpFile($file_info,$tags)
{
    $i = 0;

    $file_info = str_replace(array(',','.','-','/','(',')','[',']','!','&','"','*','_'), '' , $file_info); // removing full stops, commas, dashes etc...
    $file_info = str_replace(array("\n","\r"), " ", $file_info); // replacing \n and \r tags with a space, so we can successfully read a new line
    $file_info = preg_replace('/\s\s+/', ' ', $file_info); // replacing multiple spaces with a single space

    //checking that the first character isn't a space and if it is we add a space. This way, we can get the first word into an array (splitting by spaces) 
    if($file_info[0] !== " ")
    {
        $file_info = " " . $file_info; //prepend an empty string to the file
    }

    $words = explode(" ", $file_info); // explode the text into an array of words, separating by spaces
    $words = array_unique($words); // remove any duplicates in the array. For example, if "OS" occurs multiple times, it will remove the duplicates

    $file_info = implode(" ",$words); // implode the array back into text

    $stoplist = file_get_contents("./acronyms/stoplist.txt"); // reads in the stop list of words
    $stopwords = explode("\r\n", $stoplist); // explode the stop words into an array, separating by new lines

    // Remove the stop words from the file based on the conditions that the word is in the file and it is not capitalized. 
    foreach($stopwords as $word)
    {
      // for each stop word, check if the file contains the stopword and if so remove it (if the word is lower case or not capitalized)
      if(stripos($file_info,$word))
      {
          $lower_case_pos = array_search($word, $words); // search for lowercase
          $start_upper_pos = array_search(UcFirst($word), $words); // search for first letter upper case stopwords

          $file_info = preg_replace("/\b". $words[$lower_case_pos] . "\b/", "", $file_info); // replacing lower case stopwords
          $file_info = preg_replace("/\b". $words[$start_upper_pos] . "\b/", "", $file_info); // replacing first letter upper case stopwords

      }
    }

    // Remove non-nouns based on the conditions that it is not tagged as "NN" i.e. Noun and it is not fully capitalized 
    // loop through the tags of the tagger and remove all words which are not either capitalized or tagged with NN
    foreach($tags as $t) 
    {
        if($t['tag'] !== "NN" && (!ctype_upper($t['token'])))
        {
          $file_info = preg_replace("/\b". $t['token']. "\b/", "", $file_info);
        }
    }

    return $file_info;     
}

// function to classify the document. See http://php-classifier.com/ for more information
function classifyDocument($clean_file)
{ 
  require("classifier.php"); // creates the model and source of data. See the classifier.php class for more information.

  $classifier = new ComplementNaiveBayes($source, $model); // create an instance of the ComplementNaiveBayes class.
  $category = $classifier->classify($clean_file);  // Classify the document
  return $category; // return the document
}

?>

<html>

<head>
<link href="css/dropzone.css" type="text/css" rel="stylesheet" />
<link href="css/basic.css" type="text/css" rel="stylesheet" />
<script src="js/dropzone.min.js"></script> <!-- -->
</head>

<body>
<div class="message" id="message"></div>
<div class ="container"> <!-- Twitter bootstrap container for common fixed-width layout -->
	<div class="jumbotron"> <!-- Twitter bootstrap jumbotron for the grey background of body -->
     <ul class="nav nav-tabs nav-justified" id="myTab"> <!-- Navigation bar for the categories. updateMenu() is used highlight the active tab. Navigation bar is part of Twitter bootstrap-->
            <li <?=updateMenu("/index.php?category=None")?>><a href="?category=None"> None </a></li>
            <li <?=updateMenu("/index.php?category=Medical")?>><a href="?category=Medical"> Medical </a></li>
            <li <?=updateMenu("/index.php?category=IT")?>><a href="?category=IT"> IT </a></li>
            <li <?=updateMenu("/index.php?category=Business")?>><a href="?category=Business"> Business </a></li>
            <li <?=updateMenu("/index.php?category=Mili-Gov")?>><a href="?category=Mili-Gov"> Military/Government </a></li>  
    </ul>

  <script>

$(document).ready(function()
{
  $(".progress-bar").tooltip(); // displaying a tooltip when the user hoovers over it. Part of Twitter Bootstrap. 
});       

    // Dropzone JS is an open source library which provides a drop zone for uploading files. 
    // See for the options you can set and for more information at: http://www.dropzonejs.com/
    // The options set for this system are max number of files 1, add remove links so users can remove the file and set auto process files to false, 
    // so we wait for the submit button to be clicked before we submit

    Dropzone.options.myDropzone = {
    maxFiles: 1,
    autoProcessQueue: false,
    addRemoveLinks: true,
    accept: function(file, done) 
    {
      // checking if the document type is not txt, doc or docx and if they aren't, we remove the file from the dropzone
      if((file.name).indexOf(".docx") == -1 && (file.name).indexOf(".doc") == -1 && (file.name).indexOf(".txt") == -1)
      {
          document.getElementById('message').innerHTML = "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b> Only .doc, .docx and .txt files are accepted </b></div>";
          this.removeFile(file); // remove file from dropzone
      }
      else
      {
          done(); // done, process file
      }
    },
    init: function(file) 
    {
      var submitButton = document.querySelector("#submit-form"); // get the submit button 
      myDropzone = this; // closure

      //wait for the user to click the submit button, then upload the form. 
      submitButton.addEventListener("click", function() 
      {
        myDropzone.processQueue(); // Tell Dropzone to process all queued files.

        myDropzone.on("complete", function(file) 
        {
           $("#myDropzone").submit(); // submit the file
         });

      });

      this.on("maxfilesexceeded", function(file)
      {
          document.getElementById('message').innerHTML = "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b> Max number of files: 1</b></div>"; // display error message if the user tries to upload more than one file
          this.removeFile(file); // remove the file
      });
    }
  };
  </script>

  <br/>
  <p> Note - Allowed File Extensions: <b>.doc, .docx, .txt </b> </p>
  <div id="dropzone"> <!-- Dropzone -> Part of Dropzone JS -->
  <form action="upload.php" class="dropzone" id="myDropzone" method ="post" enctype="multipart/form-data">
  <div class="fallback"> <!-- Fallback in case dropzone isn't working -->
    <input name="file" type="file" multiple />
  </div>
  <input type="hidden" <?php getCategory() ?> name="category"> <!-- Sending the category when we submit the form -->
  </form>
       <button type="Submit" name="submit" id="submit-form" class="btn btn-primary submit-form">Upload</button>
  </div>
</form>
</div>
<div id="table-pos">
  <?php

   echo "$display_Main"; // display main table
   echo "<br/>";
   echo "$display_Other"; // display other table

  ?>
</div>
</body>
</html>