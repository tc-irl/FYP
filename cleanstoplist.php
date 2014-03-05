<?php

// Tutorial used: http://anchetawern.github.io/blog/2013/08/07/getting-started-with-web-scraping-in-php/
// Websites used for scraping acronyms: acronymslist.com. All Acronyms were gathered from there in order to gather sufficient acronyms for my final year project. 
// The acronyms gathered will not be used for financial gain, merely to have sufficient acronyms to work on the system. 
/*
$counter = 0; 

$acronym_list = new DOMDocument();
$first_page = 36;
$last_page = 64;
*/
/*
for($i = $first_page; $i <= $last_page; $i++)
{
	@$acronym_list->loadHTMLFile('http://www.acronymslist.com/cat/computer-acronyms-(common)-p'.$i.'.html'); //@ suppresses warnings. Loading the html file based on a given url
	$path = new DOMXPATH($acronym_list);
    $acronyms = $path->query('//a[@class="special"]'); // get all a elements with class="special" -> class="special" is used in each instance of the Acronyms being displayed
    $definitions = $path->query('//td[@width="450"]'); // get all td elements with width="450" -> width="450" is unique for each definition on the page

  		//for loop which is used for each acronym and associated definition
      foreach($acronyms as $acronym)
      {

      	$acronym_definition = $acronym->nodeValue . " = " . $definitions->item($counter)->nodeValue .PHP_EOL;
		$open_file = fopen('C:\wamp\www\acronyms\IT.txt', 'a');
		fwrite($open_file, $acronym_definition);

        $counter++;
      }

      $counter = 0; 
}

*/
/*
for($i = $first_page; $i <= $last_page; $i++)
{
	@$acronym_list->loadHTMLFile('http://www.acronymslist.com/cat/us-military-acronyms-p'.$i.'.html'); //@ suppresses warnings. Loading the html file based on a given url
	$path = new DOMXPATH($acronym_list);
    $acronyms = $path->query('//a[@class="special"]'); // get all a elements with class="special" -> class="special" is used in each instance of the Acronyms being displayed
    $definitions = $path->query('//td[@width="450"]'); // get all td elements with width="450" -> width="450" is unique for each definition on the page

  		//for loop which is used for each acronym and associated definition
      foreach($acronyms as $acronym)
      {

      	$acronym_definition = $acronym->nodeValue . " = " . $definitions->item($counter)->nodeValue .PHP_EOL;
		$open_file = fopen('C:\wamp\www\acronyms\mili_gov.txt', 'a');
		fwrite($open_file, $acronym_definition);

        $counter++;
      }

      $counter = 0; 
}
*/



	$dup_lines = file('C:\wamp\www\acronyms\stoplist.txt');
	$dup_lines = array_unique($dup_lines);

	file_put_contents('C:\wamp\www\acronyms\stoplist.txt', implode($dup_lines));


?>


<html>
<head>
</head>
<body>
</body>
</html>