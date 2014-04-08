<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Classifier page -> Page used to train the classifier for the site. 

The classifier classes were downloaded from: http://php-classifier.com/

Note * -> Code used from the downloaded classes are identified by: "Part of the PHP Classifier Classes" in the comments. 
 
-->

<?php

  require_once(".\\vendor\autoload.php"); // Loads the classes when needed, without having to use multiple includes/requires. 

  use Camspiers\StatisticalClassifier\Classifier\ComplementNaiveBayes; // Use Complement Naive Bayes as the Algorithm
  use Camspiers\StatisticalClassifier\Model\CachedModel; // Use a cached model, which caches the trained data into a file. 
  use Camspiers\StatisticalClassifier\DataSource\DataArray; // Use DataArray class for handling the documents into a suitable data array. 

  $source = new DataArray(); // creating the data array. Part of the PHP Classifier Classes. 

  $dir = "./FYPDocs/Train/";
  $cats = array("IT","Medical","Business","Mili-Gov");

  // For loop which reads all the files in the training folder, 

  foreach ($cats as $cat) 
  {
    # code...

    $category = $dir . $cat . "/";

    if(is_dir($category))
    {

      // finds all the files within the directory. Glob_Mark adds a "/" to each directory returned. 
      // For more info see: http://ie1.php.net/glob

      $dir_files = glob($category . '*', GLOB_MARK);  // finds all the files within the directory. Glob_Mark adds a "/" to each directory returned. 

      // for each file in the directory
      foreach ($dir_files as $df)
      {
        $file = file_get_contents($df); // read the file

        $lines = explode("\n", $file); // explode file into array of lines, where "\n" tag is found.  
  		  $file = implode("\n", array_slice($lines, 1)); // 

  		  $file = str_replace(array(',','.','/','(',')','[',']','!','&','"','*','_'), '' , $file); // removing full stops, commas, dashes etc...
     		$file = str_replace(array("\n","\r"), " ", $file); // removing \n and \r hidden tags with " " 
     		
        // stripping the excess whitespace. If there are multiple spaces, replace with a single space.
        // \s means whitespace character and the + means multiple times. So it replaces multiple whitespaces with a space each time they occurs.
        //  For more information see: http://ie1.php.net/preg_replace

        $file = preg_replace('/\s\s+/', ' ', $file); 

     		$words = explode(" ", $file); // explode the file (text string) into an array of words based, exploding each time a space occurs. 
      	$words = array_unique($words); // remove duplicates 

      	$file = implode(" ",$words); // convert the array back into a file (text string)
 
      	$stoplist = file_get_contents("./acronyms/stoplist.txt"); // read the stop words into a string
      	$stopwords = explode("\r\n", $stoplist); // explode the words into an array, where a new line is found i.e. each stop word. 

   		// Remove the stop words based on the conditions that the word is in the file and it is not fully capitalized. 
	    foreach($stopwords as $word)
	    {
	      if(stripos($file,$word))
	      {
	          $lower_case_pos = array_search($word, $words); // search for the stop words in the file
	          $start_upper_pos = array_search(UcFirst($word), $words); // search for the stop words in the file where the first letter is uppercased

	          $file = preg_replace("/\b". $words[$lower_case_pos] . "\b/", "", $file); // replacing lower case stopwords
	          $file = preg_replace("/\b". $words[$start_upper_pos] . "\b/", "", $file); // replacing first letter upper case stopwords

	      }
	    }

   		$source->addDocument($cat, $file); // Adds the document to the data array. Part of the PHP Classifier Classes. 
      }

    }
}

// Creating a cached naive bayes model. Part of the PHP Classifier Classes. 
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