<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: About page -> Provides basic information on the site.

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

Tutorial(s) used: 
http://www.startutorial.com/articles/view/how-to-build-a-file-upload-form-using-dropzonejs-and-php 

-->


<?php

	session_start(); // Start session

	// checking if the category is set and if so assign it to $category variable
	if(isset($_POST['category']))
    {
    	$category = $_POST['category'];
    }

    // checking if the user is logged in before they can upload the file
	if(isset($_SESSION['username']))
	{
		$username = $_SESSION['username'];
		$path = ".\uploads\\" . $username . "\\"; // creating a path to where the file should be uploaded. Might look like: uploads/user1@hotmail.com

		//checking if the directory exists already and if not create the directory. 
		if(!file_exists($path))
		{
			@mkdir($path,0777,true);
		}

		// The next 3 lines are following the tutorial of moving the temp file to a non temp file to handle the uploaded file. 
		@$temporaryFile = $_FILES['file']['tmp_name'];  // Creating a temporary file     
		@$targetFile =  $path . $_FILES["file"]["name"];  // Where to the move the file to
		move_uploaded_file($temporaryFile,$targetFile); // Move the temporary file to the targetfile location for further use. 

		$dir_files = glob($path . '*', GLOB_MARK); // get the list of files in the directory
		
		// sort the files by their modification time, thus always getting latest file uploaded by the user. See: http://ie1.php.net/usort and http://ie1.php.net/filemtime for more information
		usort($dir_files, function($file1, $file2) 
		{
	   	 	return filemtime($file1) < filemtime($file2);
		});

		header('Location: index.php?category=' . $category . '&filename=' . basename($dir_files[0])); // return to index page, with the filename
	} 
	else
	{
		header('Location: index.php?category=' . $category . '&message=Please log in'); 
	}
?>


