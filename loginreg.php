<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Loginreg page -> The base page of where users can either login, register or if they forgot their password. 
If the users select, Login, Register or Forgot password, it will update only that part of the page. The rest of the page remains the same. 

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap
 
-->

<?php
	include("header.php");	 // including the header page (for the title, navigation bar, and other functions)
	include("db.class.php"); // including the db class (connecting to the database)
?>
<html>
<head>

    <script src="//code.jquery.com/jquery-1.9.1.js"></script> <!-- Using JQuery with a CDN, allowing for users to download jQuery quicker. -->
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script> <!-- Using JQuery Validate with a CDN, allowing for users to download jQuery validate quicker. -->

	<script type="text/javascript">

		// Function for displaying an messages for the page. 
	 	function alertMessage(message)
	 	{	
	 		var newMessage = message;

	 		if(newMessage == "Email has been sent" || newMessage == "You have successfully registered!" || newMessage == "Your password has been reset!")
	 		{
	 		 	document.getElementById('message').innerHTML = "<div class='alert alert-success alert-dismissable' style='margin-right: 117px; margin-left: 113px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>" + newMessage + "</b></div>";
	 		}
	 		else
	 		{
	 			document.getElementById('message').innerHTML = "<div class='alert alert-danger alert-dismissable' style='margin-right: 117px; margin-left: 113px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>" + newMessage + "</b></div>";
	 		}
	 	}
	 </script>

</head>
<body>
<div class="message" id="message"></div> <!-- Messages will be display here -->
<div class ="container"> <!-- Twitter bootstrap container for common fixed-width layout -->
	<div class="jumbotron"> <!-- Twitter bootstrap jumbotron for the grey background of body -->
		<ul class="nav nav-tabs nav-justified" id="myTab"> <!-- Creating a navigation bar (Twitter bootstrap navbar) for the Login, Forgot password and Register page. updateMenu() is used to highlight the active tab -->
            <li <?=updateMenu("/loginreg.php?page=login")?>><a href="?page=login">Login</a></li> 
            <li <?=updateMenu("/loginreg.php?page=forgot")?>><a href="?page=forgot"> Forgot your password? </a></li>
            <li <?=updateMenu("/loginreg.php?page=register")?>><a href="?page=register"> Register </a></li> 
   		 </ul>


	<!-- PHP -->
    <?php 
    	// checking if a message has been passed to the page and if so call alert message function. 
		if(isset($_GET['message']))
		{
			$message = $_GET['message'];
			echo '<script type="text/javascript"> alertMessage("'. $message . '"); </script>';
		}

	    $page = isset($_GET['page']) ? $_GET['page'] : "loginreg.php"; // check if the login/register/forgot page is selected, if not default to loginreg page

	    // Using a switch statement for the 3 sub pages of login, registration and forgot password. If the user clicks on the login tab, it will load the login form
	    // if the user clicks the forgot tab it will load the forgot form and if the user clicks the registration tab it will load the registration form. 
	    // By using a switch statement, we only have to load in the relevant forms to the page, rather than loading a separate page each time. 

	    switch($page)
	    {
	    	case 'forgot':
	    		require_once('forgot.php');
	    		break;
	    	case 'register':
	    		require_once('register.php');
	    		break;
	    	default:
	    		require_once('login.php');
	    		break;
	    }

    ?>

	</div>
</div>
</body>
</html>