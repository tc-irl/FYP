<!--

Author: Tony Cullen (C10385847)
Project: Acronym Identification System
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Page: Create Queries Class -> Converts the web scraped acronyms and definitions into suitable queries 
which can be run to insert the acronyms and defintions into the database
 
-->

<?php

// Simply change the file names below from mili_gov to IT (for example) when needed for each category 

$open_file = fopen('C:\wamp\www\acronyms\mili_gov.txt', 'r'); // Open the mili_gov.txt file where the acronyms and defintions are contained. 
$open_file2 = fopen('C:\wamp\www\acronyms\mili_gov_queries.txt', 'a'); // Open another file which will store the queries. 

// loop through the file containing acronyms and defintions line by line. 
while(!feof($open_file))
{	
	$line = fgets($open_file); // get the line
	$split_line = explode(" = ", $line); // split the line into an array, where " = " occurs

	$newline = "Insert into acronymlist VALUES ('". trim($split_line[0]) . "','" . trim($split_line[1]) . "','Mili-Gov', 1);" .PHP_EOL; // Create the string for the query. Trim removes the whitespace characters as without trim it was returning extra whitespace chracters
	fwrite($open_file2, $newline); // write the query to the file
}

?>