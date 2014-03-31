<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Scraper class -> Used for scraping the acronyms and their definitions for the system. 
 
-->

<?php

// Tutorial used for web scraping: http://anchetawern.github.io/blog/2013/08/07/getting-started-with-web-scraping-in-php/
// Parts of the tutorial or modified parts are marked as: "Part of tutorial" in the comments. 
// Websites used for scraping acronyms: acronymslist.com. All Acronyms were gathered from there in order to gather sufficient acronyms for my final year project. 
// The acronyms gathered will not be used for financial gain, merely to have sufficient acronyms to work on the system. 

$counter = 0; 

$acronym_list = new DOMDocument(); // Creating a new DOMDocument, which represents the HTML Document
$first_page = 1; // first page to be scraped
$last_page = 5; // last page to be scraped. 

// http://www.acronymslist.com/cat/us-military-acronyms-p'.$i.'.html

for($i = $first_page; $i <= $last_page; $i++)
{ 
    //@ suppresses warnings. Loading the html file based on a given url. 
    // To gather more acronyms on this site. We simply change the url on the site for the relevant URL, e.g. http://www.acronymslist.com/cat/us-military-acronyms-p'.$i.'.html will be used 
  
	 @$acronym_list->loadHTMLFile('http://www.acronymslist.com/cat/computer-acronyms-(common)-p'. $i .'.html'); // Part of tutorial
	  $path = new DOMXPATH($acronym_list); // Declare an instance of the DOMXPATH, which allows us to query the DOMDocument. Part of tutorial. 
    $acronyms = $path->query('//a[@class="special"]'); // get all a elements with class="special" -> class="special" is used in each instance of the Acronyms being displayed on the site. Part of tutorial. 
    $definitions = $path->query('//td[@width="450"]'); // get all td elements with width="450" -> width="450" is unique for each definition on the page. Part of tutorial. 

  		//for loop which is used for each acronym and associated definition
      foreach($acronyms as $acronym)
      {
        // Append the acronym and definition together, separated by an "=" . So it the variable will look something like: "OS = Operating System". ->nodeValue gets the value of the acronym. 
      	
        $acronym_definition = $acronym->nodeValue . " = " . $definitions->item($counter)->nodeValue . PHP_EOL; // Part of tutorial
		    $open_file = fopen('C:\wamp\www\acronyms\IT.txt', 'a'); // Opening the file to be appended. 
		    fwrite($open_file, $acronym_definition); // Write to the file. 

        $counter++; 
      }

      $counter = 0; 
}

fclose($open_file); // Close the file

$dup_lines = file('C:\wamp\www\acronyms\IT.txt'); // Load the file into the variable $dup_lines. For each file needed, we just change the IT to Medical, Business, Mili-Gov, etc... 
$dup_lines = array_unique($dup_lines); // array unique is a php function which removes duplicates in the array. In this case, it will remove any duplicate lines where the acronym and definition occur multiple times

file_put_contents('C:\wamp\www\acronyms\IT.txt', implode($dup_lines)); // put the file back together, without any duplicates. 

?>


<html>
<head>
</head>
<body>
</body>
</html>