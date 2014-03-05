<?php
	include("header.php");	
	include("db.class.php");
?>
<html>
<head>

    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>

	<script type="text/javascript">

	 	function alertMessage(message)
	 	{	
	 		var newMessage = message.replace("%20"," ");

	 		if(newMessage == "Email has been sent" || newMessage == "You have successfully registered!" || newMessage == "Your password has been reset!")
	 		{
	 		 	document.getElementById('message').innerHTML = "<div class='alert alert-success alert-dismissable'> <b>" + newMessage + "</b></div>";
	 		}
	 		else
	 		{
	 			document.getElementById('message').innerHTML = "<div class='alert alert-danger alert-dismissable'> <b>" + newMessage + "</b></div>";
	 		}
	 	}
	 </script>

</head>
<body>
<div class="message" id="message"></div>
<div class ="container">
	<div class="jumbotron">
		<ul class="nav nav-tabs nav-justified" id="myTab">
            <li <?=updateMenu("/loginreg.php?page=login")?>><a href="?page=login">Login</a></li>
            <li <?=updateMenu("/loginreg.php?page=forgot")?>><a href="?page=forgot"> Forgot your password? </a></li>
            <li <?=updateMenu("/loginreg.php?page=register")?>><a href="?page=register"> Register </a></li> 
   		 </ul>


	<!-- PHP -->
    <?php 

		if(isset($_GET['message']))
		{
			$message = $_GET['message'];
			echo '<script type="text/javascript"> alertMessage("'. $message . '"); </script>';
		}

	    $page = isset($_GET['page']) ? $_GET['page'] : "loginreg.php";

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