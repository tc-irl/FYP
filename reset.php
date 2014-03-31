<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Reset page -> Allows the user to reset their password.

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

This page also uses a JQuery Validation Plugin to help with client side validation. See: http://jqueryvalidation.org/validate/
-->

<?php
	include("header.php");
	include("db.class.php");

	// checking if the users submitted the reset form
	if(isset($_POST['submit']))
	{
		// server-side validation, ensuring that each field of the form was successfully filled in. 
		if(isset($_POST['password']) && isset($_POST['newPassword']))
		{
			// Assigning the old password and new password to the $old_pass and $pass variables. Here was hash the passwords for security. 
	        // Hashing is similar to encryption except you cannot decrypt the password, thus improving the security of the passwords provided. 
	        // The sha256 is the hashing algorithm used.
	        // For more information, see: http://us2.php.net/manual/en/function.hash.php. 

			$old_pass = hash('sha256',mysqli_real_escape_string($db_connect, $_POST['password']));
			$pass = hash('sha256', mysqli_real_escape_string($db_connect, $_POST['newPassword']));
			@$email = mysqli_real_escape_string($db_connect, $_GET['email']); // Assigning the email address to the $email variable

		    $check_pass = mysqli_query($db_connect, "Select Email from users where Email='$email' and Password ='$old_pass'" ); // Check if old pass matches that of the users account.

		    // check if the the password matches by checking if the query returns a row or not
			if(mysqli_num_rows($check_pass) == 1)
	        {	
				$update_password = mysqli_query($db_connect, "Update users SET Password='$pass' where Email='$email'"); // Update database withnew password
				$message="Your password has been reset!";
				header('Location: loginreg.php?page=login&message=' . $message); // return to login page with success message
				exit;
	        }
	        else
	        {
	        	// display error message
	        	echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Email does not exist</b></div>";
	        }
		}
		else
		{ 
			// display error message
			echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Please fill in all spaces</b></div>";
		}
	}
?>	

<html>
<head>
    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
</head>
<body>
<div class="alert-message">
<div class ="container"> <!-- Twitter bootstrap container for common fixed-width layout -->
	<div class="jumbotron"> <!-- Twitter bootstrap jumbotron for the grey background of body -->
	<br/>
		<!-- Reset form. Twitter bootstrap form elements -->
		<form class="form-horizontal" method="post" id="reset-form" role="form">
			 	<div class="form-group">
			   	  <label for="inputPass" class="col-sm-3 control-label">Current Password</label>
			   		<div class="col-sm-6">
			     		<input type="password" name="password" class="form-control" id="inputPass" placeholder="Current Password" minLength="4" maxLength="11" required>
			   		</div>
			    </div>
			    <div class="form-group">
			   	  <label for="inputNewPassword" class="col-sm-3 control-label">New Password</label>
			   		<div class="col-sm-6">
			     		<input type="password" name="newPassword" class="form-control" id="inputNewPassword" maxLength="11" minLength="4" placeholder="New Password" required>
			   		</div>
			    </div>

			    <button type="submit" name="submit" class="btn btn-primary button-right"> Submit </button>
		</form>
	</div>
</div>
	<!-- JQuery validation plugin. validate() - Validates the form. highlight() - specifies how to highlight the invalid fields and unhighlight() specifies how to respond to valid fields. See: http://jqueryvalidation.org/validate/ --> 
	<script>
		     $(document).ready(function(){
              $("#reset-form").validate({
                  highlight: function (element) {
                      $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                  },
                  unhighlight: function (element) {
                      $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                  }
              });
          });  
	</script>
</body>
</html>