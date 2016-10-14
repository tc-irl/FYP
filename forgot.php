<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Forgot page -> Allows users to request a new password if they forgot theirs.

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

This page uses a JQuery Validation Plugin to help with client side validation. See: http://jqueryvalidation.org/validate/

This page also uses Swift Mailer to assist in sending emails to and from the site. See: http://swiftmailer.org/docs/sending.html
-->

<?php

require_once('lib/swift_required.php');

// checking if the users submitted the forgot password form
if(isset($_POST['submit']))
{
	// checking if the users email is set
	if(isset($_POST['email']))
	{
		$email = mysqli_real_escape_string($db_connect,$_POST['email']); // assign the users email to the variable $email, Note: mysqli_real_escape_string helps prevent against sql injection 


		// Create a transport, using gmail as the smtp. See: http://swiftmailer.org/docs/sending.html for more information. Part of swift mailer. 
		$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
				->setUsername('justforgithub4884fe9309@gmail.com') // username of email
				->setPassword('wdwfe3r453evd');  //password of email

   			$mailer = Swift_Mailer::newInstance($transport); // create a new instance of the swift mailer class with transport as the parameter. Part of the swift mailer

			$check_email = mysqli_query($db_connect, "Select Email from users where Email='$email'"); // Query which checks if the email already exists or not. 

		// check if the email exists, by checking if the query returns a row or not
		if(mysqli_num_rows($check_email) == 1)
        {	
        	$newpass = substr(md5(rand()),0,7); // creating a new random md5 password which is sent to the user. 
            send_mail_to($email,$mailer,$newpass); // sends a mail to the user
            $newpass = hash('sha256', $newpass); // hashing the new password assigned to the user
            $update_pass = mysqli_query($db_connect, "Update users set Password='$newpass' where Email='$email'"); // Updates the users password, assigning a new password
            $message = "Email has been sent";
            header('Location: loginreg.php?page=forgot&message=' . $message); // return to the page with the appropriate message
			exit;
        }
        else
        {
        	$message = "Email does not exist";
            header('Location: loginreg.php?page=forgot&message=' . $message); // return to the page with the appropriate message
            exit;
        }
	}
	else
	{
		$message = "No email was provided";
		header('Location: loginreg.php?page=forgot&message=' . $message); // return to the page with the appropriate message
		exit;
	}
}

// function for sending a mail to the user
function send_mail_to($to,$mailer,$newpass)
{
	$subject = "Reset Password"; // Subject of the email
	$body = "Your new password is: $newpass." .PHP_EOL . 
	"If you wish to reset your password, follow the reset link for the acronym identification site: http://localhost/reset.php?email=$to"; // Body of text of the email

	// setting the from, to and body of the email. Part of swift mailer
	$message = Swift_Message::newInstance($subject)
	-> setFrom('acronymid@gmail.com')
	-> setTo("$to")
	-> setBody($body);

	$mailer->send($message); // send the message. Part of swift mailer
}


?>

<html>
<head>
    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
</head>
<body>
		<br/>
		<!-- Forgot form. Twitter bootstrap form elements -->
		<form class="form-horizontal" method="post" id="forgot-form" role="form">
			 	<div class="form-group">
			   	  <label for="inputEmail" class="col-sm-3 control-label">Email</label>
			   		<div class="col-sm-6">
			     		<input type="email" name="email" class="form-control" id="inputEmail" placeholder="Email Address" maxLength="30" required>
			   		</div>
			    </div>

			    <button type="submit" name="submit" class="btn btn-primary button-right"> Submit </button>
		</form>
	<!-- JQuery validation plugin. validate() - Validates the form. highlight() - specifies how to highlight the invalid fields and unhighlight() specifies how to respond to valid fields. See: http://jqueryvalidation.org/validate/ --> 
 	<script>
		 	$(document).ready(function(){
              $("#forgot-form").validate({
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
