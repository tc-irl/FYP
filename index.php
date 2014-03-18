<?php

/*
TO DO: Separate table for: 
-> Acronyms in the document that are not in the category of the document
-> Levenshtein distance to calculate similar acronyms. 

*/

require("classifier.php");
require("header.php");
require("doc2txt.class.php");
require("tagger.class.php");
require("db.class.php");

use Camspiers\StatisticalClassifier\Classifier\ComplementNaiveBayes;
use Camspiers\StatisticalClassifier\Model\CachedModel;
use Camspiers\StatisticalClassifier\DataSource\DataArray;

$tagger = new PosTagger('C:/wamp/www/acronyms/lexicon.txt');

$cap_array = Array();
$display_table = "";
$display_Main = "";
$display_Other= "";

$j = 0;
$count2 = 0;

$acs = array();
$acs2 = array();
$defs = array();
$defs2 = array();
$cats = array();
$cats2 = array();

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
          echo "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Thank you for tagging your acronym</b></div>";
      }
      else
      {
          echo "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Acronym, Definition and/or Category is incorrect </b></div>";
      }

  }
  else
  {
     echo "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>An error occured with the acronym, category and/or definition.</b></div>";
  }
}

if(isset($_GET['message']))
{
  echo "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b> ". $_GET['message'] ."</b></div>";
}
else
{
  if(isset($_GET['filename']) && isset($_GET['category']))
  {
    if(isset($_SESSION['username']))
    {

      $path = "C:/wamp/www/uploads/" . $_SESSION['username'];
      $fname = $_GET['filename'];

      $ext = pathinfo($fname, PATHINFO_EXTENSION);

      if($ext == "txt" || $ext == "doc" || $ext == "docx")
      {
          if(file_exists($path. "/" . $fname))
          {
              $file_info = file_get_contents($path . "/" . $fname);

              if(!empty($file_info))
              { 
                if($ext == "doc" || $ext == "docx")
                {
                  $docObj = new Doc2Txt($path . "/" . $fname);
                  $txt = $docObj->convertToText();

                  $tags = $tagger->tag($txt);

                  $oldtxt = cleanOldTxt($txt);
                  $cleanTxt = cleanUpFile($txt, $tags);
                  displayTable($oldtxt,$cleanTxt,$db_connect);
                }
                else
                {
                  $tags = $tagger->tag($file_info);
                  $oldtxt = cleanOldTxt($file_info);
                  $cleanTxt = cleanUpFile($file_info, $tags);
                  $classified_cat = classifyDocument($cleanTxt);
                  displayTable($classified_cat,$oldtxt,$cleanTxt,$db_connect); 
                }
              }
              else
              {
                echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>File is empty </b></div>";
              }
          }
          else
          {
            echo "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Sorry, an error has occurred. This may have been caused by modifying the url.</b></div>";
          }
    }
    else
    {
      echo "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Sorry, only .txt, .doc and .docx files are allowed</b></div>"; 
    }

  }
  else
  {
    echo "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Please login to continue. You can login <a href=loginreg.php?page=login' class='alert-link'> by clicking here </a></b></div>";
  }
 }
}

function getCategory()
{
  $cat =  $_GET['category'];
  echo "value=" . $cat;
}

function cleanOldTxt($oldtxt)
{
  $oldtxt = str_replace(array('.', ',','-','/','(',')','[',']','\\','!','&','"','*','_'), '' , $oldtxt); // removing full stops, commas, dashes etc...
  $oldtxt = str_replace(array("\n","\r"), " ", $oldtxt); // removing full stops, commas, dashes etc...
  $oldtxt = preg_replace('/\s\s+/', ' ', $oldtxt);

  return $oldtxt;
}

function checkMatch($acronymB, $a)
{
    if($acronymB != $a) 
    { 
      return "<del>" . $a . "</del>";
    } 
}

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
  $test = 0;

  $query = array();
  $query2 = array();
  $afterDefinition = array();
  $beforeDefinition = array();
  $tag_count = array();
  $tag_count2 = array();
  $percentage = array();
  $percentage2 = array();
  $fail = array();
  $acrList = array();
  $acronymLength = array();
  $acronyms = explode(" ", $cleanTxt);
  $defs = explode(" ", $oldTxt);
  $categories = array("None","Business","IT", "Medical", "Mili-Gov");
  $category = mysqli_real_escape_string($db_connect,$_GET['category']);
  $select_All_Tags = array();
  $select_Other_Tags = array();
  $sum = array();
  $sum_other = array();
  $counter = 0;
  $allCatAcronyms = "";

  if($category == "None")
  {
    $category = $classified_cat;
  }

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

    $display_Main = $display_table . "<div class='alert alert-success alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Successful Search!</b></div>";
    $display_Other = "<div class='alert alert-warning alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>The following table contains acronyms found from a different category and/or suggested acronyms for acronyms that were found in the text but not recognized in the database. </b></div>" . $display_table;


if(in_array($category, $categories))
{

     foreach ($acronyms as $acronym) 
     {  
        $a = mysqli_real_escape_string($db_connect,$acronym);

        if(in_array($acronym, $defs)) 
        {
           $acronymLength = strlen($acronym);
           $pos = array_search($acronym, $defs);
           
           for($l = 1; $l <= $acronymLength; $l++)
           {
              @$afterDefinition[$qCount] = @$afterDefinition[$qCount] . $defs[$pos + ($acronymLength - ($acronymLength - $l))] . " ";
           }
           for($l = 0; $l < $acronymLength; $l++)
           {
              @$beforeDefinition[$qCount] = @$beforeDefinition[$qCount] . $defs[$pos - ($acronymLength - $l)] . " ";
           }
           
          @$checkForDefBefore = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$beforeDefinition[$qCount]'");
          @$checkForDefAfter = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$afterDefinition[$qCount]'");

           if(@mysqli_num_rows($checkForDefBefore) >= 1)
           { 
               @$query[$qCount] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$beforeDefinition[$qCount]' AND Category='$category'");
               @$query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$beforeDefinition[$qCount]' AND Category !='$category'");
           }
           else if (@mysqli_num_rows($checkForDefAfter) >= 1) 
           {
               @$query[$qCount] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$afterDefinition[$qCount]' AND Category='$category'");
               @$query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Definition = '$beforeDefinition[$qCount]' AND Category !='$category'");
           }
           else
           {
             @$query[$qCount] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$a' AND Category='$category'");
             @$query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where (Acronym = '$a' AND Category != '$category') AND not exists (Select * from acronymlist where Acronym = '$a' AND Category = '$category')");
           }

           if(mysqli_num_rows($query[$qCount]) >= 1)
           {   
               $select_All_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym = '$a' AND Category='$category'");
               $sum =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym = '$a' AND Category='$category'");
               $total_sum = mysqli_fetch_array($sum);
           }

           if(mysqli_num_rows($query2[$qCount2]) >= 1)
           {   
               $select_Other_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym = '$a' AND Category !='$category'");
               $sum_other =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym = '$a' AND Category !='$category'");
               $total_other_sum = mysqli_fetch_array($sum_other);
           }
          while(@$row = mysqli_fetch_array($select_Other_Tags))
          {
              @$tag_count2[$count2] = $row['Tag_Count'];
              @$percentage2[$count2] = ($tag_count2[$count2] / $total_other_sum['Total_Sum']) * 100;
              $count2++;
          }

          if ((mysqli_num_rows($query2[$qCount2]) < 1) &&  mysqli_num_rows($query[$qCount]) < 1)
          {
        
            $pos = 0;

            if(ctype_upper($acronym))
            {
              $allCatAcronyms = mysqli_query($db_connect, "Select * from acronymlist where Category = '$category'");

              while($dbAcr = mysqli_fetch_array($allCatAcronyms))
              {
                 $dbAcronym = $dbAcr['Acronym'];
                 $levenshtein[$pos] = levenshtein($a, $dbAcronym);

                if($levenshtein[$pos] == 1)
                {

                   $query2[$qCount2] = mysqli_query($db_connect, "Select * from acronymlist where Acronym = '$dbAcronym'");

                   $select_Other_Tags = mysqli_query($db_connect, "Select Tag_Count from acronymlist where Acronym = '$dbAcronym'");
                   $sum_other =  mysqli_query($db_connect, "Select SUM(Tag_Count) as Total_Sum from acronymlist where Acronym = '$dbAcronym'");
                   $total_other_sum = mysqli_fetch_array($sum_other);

                  while(@$row = mysqli_fetch_array($select_Other_Tags))
                  { 
                      @$tag_count2[$count2] = $row['Tag_Count'];
                      @$percentage2[$count2] = ($tag_count2[$count2] / $total_other_sum['Total_Sum']) * 100;
                      $count2++;
                  }

                    while($row2 = mysqli_fetch_array($query2[$qCount2]))
                    { 

                        $acronymB = $row2['Acronym'];
                        $definitionB = $row2['Definition'];
                        $categoryB = $row2['Category'];

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
                                                    <p> Acronym:" . $acronymB . " </p>
                                                    <p> Definition:". $definitionB . " </p>
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

           while(@$row = mysqli_fetch_array($select_All_Tags))
           {
              $tag_count[$count] = $row['Tag_Count'];
              $percentage[$count] = ($tag_count[$count] / $total_sum['Total_Sum']) * 100;
              $count++;
           }

            while($row = mysqli_fetch_array($query[$qCount]))
            {

                $acronym = $row['Acronym'];
                $definition = $row['Definition'];
                $category = $row['Category'];

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
                                            <p> Acronym:" . $acronym. " </p>
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

            while(@$row3 = mysqli_fetch_array($query2[$qCount2]))
            { 

                $acronymC = $row3['Acronym'];
                $definitionC = $row3['Definition'];
                $categoryC = $row3['Category'];

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

 $display_Main = $display_Main . "</tbody></table></div>";
 $display_Other = $display_Other. "</tbody></table></div>";
}

function cleanUpFile($file_info,$tags)
{
    $i = 0;
    // remove words in stop list such as the and or etc.. (full stops etc..)
    // remove everything that isn't either capitalized or a noun (brills tagger).

    $file_info = str_replace(array(',','.','-','/','(',')','[',']','!','&','"','*','_'), '' , $file_info); // removing full stops, commas, dashes etc...
    $file_info = str_replace(array("\n","\r"), " ", $file_info); // removing full stops, commas, dashes etc...
    $file_info = preg_replace('/\s\s+/', ' ', $file_info);

    if($file_info[0] !== " ")
    {
        $file_info = " " . $file_info; //prepend an empty string to the fi
    }

    $words = explode(" ", $file_info);
    $words = array_unique($words);

    $file_info = implode(" ",$words);

    $stoplist = file_get_contents("C:/wamp/www/acronyms/stoplist.txt");
    $stopwords = explode("\r\n", $stoplist);

    // Remove the stop words based on the conditions that the word is in the file and it is not fully capitalized. 
    foreach($stopwords as $word)
    {
      if(stripos($file_info,$word))
      {
          $lower_case_pos = array_search($word, $words);
          $start_upper_pos = array_search(UcFirst($word), $words);

          $file_info = preg_replace("/\b". $words[$lower_case_pos] . "\b/", "", $file_info); // replacing lower case stopwords
          $file_info = preg_replace("/\b". $words[$start_upper_pos] . "\b/", "", $file_info); // replacing first letter upper case stopwords

      }
    }

      // Remove non-nouns based on the conditions that it is not tagged as "NN" i.e. Noun and it is not fully capitalized 
    foreach($tags as $t) 
    {
        if($t['tag'] !== "NN" && (!ctype_upper($t['token'])))
        {
          $file_info = preg_replace("/\b". $t['token']. "\b/", "", $file_info);
        }
    }

    return $file_info;     
}

function classifyDocument($clean_file)
{ 
  require("classifier.php");

  $classifier = new ComplementNaiveBayes($source, $model);
  $category = $classifier->classify($clean_file); 
  return $category;
}

?>

<html>

<head>
<link href="css/dropzone.css" type="text/css" rel="stylesheet" />
<link href="css/basic.css" type="text/css" rel="stylesheet" />
<script src="js/dropzone.min.js"></script>
</head>

<body>
<div class="message" id="message"></div>
<div class ="container">
	<div class="jumbotron">
     <ul class="nav nav-tabs nav-justified" id="myTab">
            <li <?=updateMenu("/index.php?category=None")?>><a href="?category=None"> None </a></li>
            <li <?=updateMenu("/index.php?category=Medical")?>><a href="?category=Medical"> Medical </a></li>
            <li <?=updateMenu("/index.php?category=IT")?>><a href="?category=IT"> IT </a></li>
            <li <?=updateMenu("/index.php?category=Business")?>><a href="?category=Business"> Business </a></li>
            <li <?=updateMenu("/index.php?category=Mili-Gov")?>><a href="?category=Mili-Gov"> Military/Government </a></li>  
    </ul>

  <script>
$(document).ready(function()
{
  $(".progress-bar").tooltip();
});       


    Dropzone.options.myDropzone = {
    maxFiles: 1,
    autoProcessQueue: false,
    addRemoveLinks: true,
    accept: function(file, done) 
    {
      if((file.name).indexOf(".docx") == -1 && (file.name).indexOf(".doc") == -1 && (file.name).indexOf(".txt") == -1)
      {
          document.getElementById('message').innerHTML = "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b> Only .doc, .docx and .txt files are accepted </b></div>";
          this.removeFile(file);
      }
      else
      {
          done(); 
      }
    },
    init: function(file) 
    {
      var submitButton = document.querySelector("#submit-form")
      myDropzone = this; // closure

      submitButton.addEventListener("click", function() 
      {
        myDropzone.processQueue(); // Tell Dropzone to process all queued files.

        myDropzone.on("complete", function(file) 
        {
           $("#myDropzone").submit();
         });

      });

      this.on("maxfilesexceeded", function(file)
      {
          document.getElementById('message').innerHTML = "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b> Max number of files: 1</b></div>";
          this.removeFile(file);
      });
    }
  };
  </script>

  <br/>
  <p> Note - Allowed File Extensions: <b>.doc, .docx, .txt </b> </p>
  <div id="dropzone">
  <form action="upload.php" class="dropzone" id="myDropzone" method ="post" enctype="multipart/form-data">
  <div class="fallback">
    <input name="file" type="file" multiple />
  </div>
  <input type="hidden" <?php getCategory() ?> name="category">
  </form>
       <button type="Submit" name="submit" id="submit-form" class="btn btn-primary submit-form">Upload</button>
  </div>
</form>
</div>
<div id="table-pos">
  <?php

   echo "$display_Main";
   echo "<br/>";
   echo "$display_Other";
  ?>
</div>
</body>
</html>