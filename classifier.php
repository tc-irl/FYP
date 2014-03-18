<?php

require_once("C:\wamp\www\\vendor\autoload.php");

use Camspiers\StatisticalClassifier\Classifier\ComplementNaiveBayes;
use Camspiers\StatisticalClassifier\Model\CachedModel;
use Camspiers\StatisticalClassifier\DataSource\DataArray;

	$source = new DataArray();

	$IT = "C:/wamp/www/FYPDocs/Train/IT/";
	$Medical = "C:/wamp/www/FYPDocs/Train/Medical/";
	$Business = "C:/wamp/www/FYPDocs/Train/Business/";
	$MiliGov = "C:/wamp/www/FYPDocs/Train/MiliGov/";

    if(is_dir($IT))
    {
      $dir_files = glob($IT . '*', GLOB_MARK);

      foreach ($dir_files as $df)
      {
      	
        $file = file_get_contents($df);

        $lines = explode("\n", $file);
  		  $file = implode("\n", array_slice($lines, 1));

  		  $file = str_replace(array(',','.','/','(',')','[',']','!','&','"','*','_'), '' , $file); // removing full stops, commas, dashes etc...
     		$file = str_replace(array("\n","\r"), " ", $file); // removing full stops, commas, dashes etc...
     		$file = preg_replace('/\s\s+/', ' ', $file);

     		$words = explode(" ", $file);
      	$words = array_unique($words);

      	$file = implode(" ",$words);

      	$stoplist = file_get_contents("C:/wamp/www/acronyms/stoplist.txt");
      	$stopwords = explode("\r\n", $stoplist);

   		// Remove the stop words based on the conditions that the word is in the file and it is not fully capitalized. 

	    foreach($stopwords as $word)
	    {
	      if(stripos($file,$word))
	      {
	          $lower_case_pos = array_search($word, $words);
	          $start_upper_pos = array_search(UcFirst($word), $words);

	          $file = preg_replace("/\b". $words[$lower_case_pos] . "\b/", "", $file); // replacing lower case stopwords
	          $file = preg_replace("/\b". $words[$start_upper_pos] . "\b/", "", $file); // replacing first letter upper case stopwords

	      }
	    }

   		$source->addDocument('IT', $file);
      }

    }
    if(is_dir($Medical))
    {
      $dir_files = glob($Medical. '*', GLOB_MARK);

      foreach ($dir_files as $df)
      {
      	
      	$file = file_get_contents($df);

        $lines = explode("\n", $file);
		$file = implode("\n", array_slice($lines, 1));

		$file = str_replace(array(',','.','/','(',')','[',']','!','&','"','*','_'), '' , $file); // removing full stops, commas, dashes etc...
   		$file = str_replace(array("\n","\r"), " ", $file); // removing full stops, commas, dashes etc...
   		$file = preg_replace('/\s\s+/', ' ', $file);

   		$words = explode(" ", $file);
    	$words = array_unique($words);

    	$file = implode(" ",$words);

    	$stoplist = file_get_contents("C:/wamp/www/acronyms/stoplist.txt");
    	$stopwords = explode("\r\n", $stoplist);

	    foreach($stopwords as $word)
	    {
	      if(stripos($file,$word))
	      {
	          $lower_case_pos = array_search($word, $words);
	          $start_upper_pos = array_search(UcFirst($word), $words);

	          $file = preg_replace("/\b". $words[$lower_case_pos] . "\b/", "", $file); // replacing lower case stopwords
	          $file = preg_replace("/\b". $words[$start_upper_pos] . "\b/", "", $file); // replacing first letter upper case stopwords

	      }
	    }

   		$source->addDocument('Medical', $file);
      }

    }

	if(is_dir($Business))
    {
      $dir_files = glob($Business . '*', GLOB_MARK);

      foreach ($dir_files as $df)
      {
      	
      	$file = file_get_contents($df);

        $lines = explode("\n", $file);
		$file = implode("\n", array_slice($lines, 1));

		$file = str_replace(array(',','.','/','(',')','[',']','!','&','"','*','_'), '' , $file); // removing full stops, commas, dashes etc...
   		$file = str_replace(array("\n","\r"), " ", $file); // removing full stops, commas, dashes etc...
   		$file = preg_replace('/\s\s+/', ' ', $file);

   		$words = explode(" ", $file);
    	$words = array_unique($words);

    	$file = implode(" ",$words);

    	$stoplist = file_get_contents("C:/wamp/www/acronyms/stoplist.txt");
    	$stopwords = explode("\r\n", $stoplist);
    	
   		// Remove the stop words based on the conditions that the word is in the file and it is not fully capitalized. 

	    foreach($stopwords as $word)
	    {
	      if(stripos($file,$word))
	      {
	          $lower_case_pos = array_search($word, $words);
	          $start_upper_pos = array_search(UcFirst($word), $words);

	          $file = preg_replace("/\b". $words[$lower_case_pos] . "\b/", "", $file); // replacing lower case stopwords
	          $file = preg_replace("/\b". $words[$start_upper_pos] . "\b/", "", $file); // replacing first letter upper case stopwords

	      }
	    }

   		$source->addDocument('Business', $file);
      }

    }

if(is_dir($MiliGov))
    {
      $dir_files = glob($MiliGov . '*', GLOB_MARK);

      foreach ($dir_files as $df)
      {

      	$file = file_get_contents($df);

        $lines = explode("\n", $file);
		$file = implode("\n", array_slice($lines, 1));

		$file = str_replace(array(',','.','/','(',')','[',']','!','&','"','*','_'), '' , $file); // removing full stops, commas, dashes etc...
   		$file = str_replace(array("\n","\r"), " ", $file); // removing full stops, commas, dashes etc...
   		$file = preg_replace('/\s\s+/', ' ', $file);

   		$words = explode(" ", $file);
    	$words = array_unique($words);

    	$file = implode(" ",$words);

    	$stoplist = file_get_contents("C:/wamp/www/acronyms/stoplist.txt");
    	$stopwords = explode("\r\n", $stoplist);
   		// Remove the stop words based on the conditions that the word is in the file and it is not fully capitalized. 

	    foreach($stopwords as $word)
	    {
	      if(stripos($file,$word))
	      {
	          $lower_case_pos = array_search($word, $words);
	          $start_upper_pos = array_search(UcFirst($word), $words);

	          $file = preg_replace("/\b". $words[$lower_case_pos] . "\b/", "", $file); // replacing lower case stopwords
	          $file = preg_replace("/\b". $words[$start_upper_pos] . "\b/", "", $file); // replacing first letter upper case stopwords

	      }
	    }

   		$source->addDocument('Mili-Gov', $file);
      }

    }

$model = new CachedModel(
    'mycachename',
    new CacheCache\Cache(
        new CacheCache\Backends\File(
            array(
                'dir' => __DIR__
            )
        )
    )
);

?>