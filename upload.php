<!--
Tutorial(s) used: 
http://www.startutorial.com/articles/view/how-to-build-a-file-upload-form-using-dropzonejs-and-php 
-->

<?php

	session_start();


	if(isset($_POST['category']))
    {
    	$category = $_POST['category'];
    }


	if(isset($_SESSION['username']))
	{
		$username = $_SESSION['username'];
		$path = "C:\wamp\www\uploads\\" . $username . "\\"; 

		if(!file_exists($path))
		{
			@mkdir($path,0777,true);
		}


		@$temporaryFile = $_FILES['file']['tmp_name'];          
		@$targetFile =  $path . $_FILES["file"]["name"];  
		move_uploaded_file($temporaryFile,$targetFile);

		$dir_files = glob($path . '*', GLOB_MARK);
		
		usort($dir_files, function($file1, $file2) 
		{
	   	 	return filemtime($file1) < filemtime($file2);
		});

		header('Location: index.php?category=' . $category . '&filename=' . basename($dir_files[0])); // note -> files[2] because files[0] and files[1] are '.' and '..' 
	} 
	else
	{
		header('Location: index.php?category=' . $category . '&message=Please log in'); // note -> files[2] because files[0] and files[1] are '.' and '..' 
	}
?>


