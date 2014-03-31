<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Db class-> Simplifies the connecting to the database so it's not necessary to write this code on each page, but simply include it in the page it's needed.
 
-->

<?php
	
	//Details required to connect to the database
	$dbHost = "localhost";
    $dbUser = "root";
    $dbPass = "";
    $dbName = "project";

    // Connect to db using Mysqli
    $db_connect = mysqli_connect($dbHost,$dbUser,$dbPass) or die("Connection to the database has failed");

    // Selecting the database for use. 
    mysqli_select_db($db_connect,$dbName) or die("Selecting the database has failed");
    
?>
