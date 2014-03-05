<?php
	$dbHost = "localhost";
    $dbUser = "root";
    $dbPass = "";
    $dbName = "project";

    // Connect to db using Mysqli
    $db_connect = mysqli_connect($dbHost,$dbUser,$dbPass) or die("Connection to the database has failed");

    mysqli_select_db($db_connect,$dbName) or die("Selecting the database has failed");
    
?>
