<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Suggest page -> Allows users to suggest an acronym to the site. 

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

This page  uses a JQuery Validation Plugin to help with client side validation. See: http://jqueryvalidation.org/validate/

This page also uses Swift Mailer to assist in sending emails to and from the site. See: http://swiftmailer.org/docs/sending.html
-->

<?php
	include("header.php");
	require_once("db.class.php");
	require_once('lib/swift_required.php');

	// checking if the users submitted the suggest form
	if(isset($_POST['submit']))
	{	
		// checking if the user is logged in.
		if(isset($_SESSION['username']))
		{
			// server-side validation, ensuring that each field of the form was successfully filled in. 
			if(isset($_POST['acronym']) && isset($_POST['definition']) && isset($_POST['category']) && isset($_POST['source']))
			{
				// here we assign the filled in fields to the appropriate variables. Note, mysqli_real_escape_string helps prevent against sql injection 
				// by escaping special characters in the string. See: http://www.w3schools.com/php/func_mysqli_real_escape_string.asp for more information. 
				$acronym = mysqli_real_escape_string($db_connect,$_POST['acronym']);
				$definition = mysqli_real_escape_string($db_connect,$_POST['definition']);
				$category = mysqli_real_escape_string($db_connect,$_POST['category']);
				$source = mysqli_real_escape_string($db_connect,$_POST['source']);
				$email = $_SESSION['username'];

			    // Create a transport, using gmail as the smtp. See: http://swiftmailer.org/docs/sending.html for more information. Part of swift mailer. 
			    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
				->setUsername('acronymid@gmail.com') // username of email
				->setPassword('testmail123');  // password of email

   				$mailer = Swift_Mailer::newInstance($transport); // creating a new instance of the Swift Mailer. Part of the swift mailer. 
	           
	            send_mail_from($email,$acronym,$definition,$category,$source,$mailer);
	            send_mail_to($email,$acronym,$mailer);

				echo "<div class='alert alert-success alert-dismissable' style='margin-right: 225px; margin-left: 195px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Thank you for your suggestion </b></div>";
			}
			else
			{
				echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b> Please fill in all spaces </b></div>";
			}
		}
		else
		{
			echo "<div class='alert alert-danger alert-dismissable' style='margin-right: 225px; margin-left: 195px;'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b> Please login to suggest an acronym </b></div>"; 
		}
	}

	// sending a mail from the user to the sites email
	function send_mail_from($from, $acronym, $definition, $category, $source,$mailer)
	{
		// setting the subject and body of the email
		$subject = "Acronym Suggestion";
		$body = "The user " . $from . " has suggested the following acronym:" . PHP_EOL . 
		"Acronym: " . $acronym . PHP_EOL . 
		"Definition: " . $definition . PHP_EOL . 
		"Category: " . $category . PHP_EOL . 
		"Source: " . $source . PHP_EOL;

		// setting the from, to and body of the email. 
		$message = Swift_Message::newInstance($subject)
		-> setFrom("$from")
		-> setTo('acronymid@gmail.com')
		-> setBody($body);

		$mailer->send($message); // send the email.
	}

	// sending a mail to the user, thanking for their suggestion
	function send_mail_to($to,$acronym,$mailer)
	{
		$subject = "Acronym Suggestion";
		$body = "Thanks for providing a suggestion for the acronym "  . $acronym . ".If the acronym is accepted it will be added to our database";

		// setting the from, to and body of the email.
		$message = Swift_Message::newInstance($subject)
		-> setFrom('tc.irl13@gmail.com')
		-> setTo("$to")
		-> setBody($body);

		$mailer->send($message); // send the email.
	}
?>

<html>
<head>
    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
</head>
<body>
<div class ="container"> <!-- Twitter bootstrap container for common fixed-width layout -->
	<div class="jumbotron"> <!-- Twitter bootstrap jumbotron for the grey background of body -->
		<p> To suggest an acronym, please login and then submit the form below: </p>
		<br/>
			<!-- Suggestion form -->
			<form class="form-horizontal" method="post" id="suggest-form" role="form">
			 	<div class="form-group">
			   	  <label for="inputAcronym" class="col-sm-3 control-label">Acronym</label>
			   		<div class="col-sm-6">
			     		<input type="text" class="form-control" name="acronym" minlength="2" id="inputAcronym" placeholder="Acronym" required/>
			   		</div>
			    </div>

			  <div class="form-group">
			  	<label for="inputDefinition" class="col-sm-3 control-label"> Definition</label>
			    	<div class="col-sm-6">
			     		 <input type="text" class="form-control" name="definition" id="inputDefinition" minlength="5" placeholder="Definition" required/>
			   	    </div>
			  </div>

			    <div class="form-group">
			   	 <label for="inputCategory" class="col-sm-3 control-label">Category</label>
			    	<div class="col-sm-6">
			    	 	 <input type="text" class="form-control" name="category" id="inputCategory" minlength="2" placeholder="Category" required/>
			    	</div>
			 	</div>

			    <div class="form-group">
			    	<label for="inputSource" class="col-sm-3 control-label">Source</label>
			    		<div class="col-sm-6">
			    		  <input type="text" class="form-control" name="source" id="inputSource" minlength="4" placeholder="Source" required/> 
			    		</div>
			  	</div>
			  	<button type="submit" name="submit" class="btn btn-primary button-right"> Suggest </button>

			</form>
			<!-- JQuery validation plugin. validate() - Validates the form. highlight() - specifies how to highlight the invalid fields and unhighlight() specifies how to respond to valid fields. See: http://jqueryvalidation.org/validate/ --> 
 			<script>
 			
 			$(document).ready(function(){
              $("#suggest-form").validate({
                  highlight: function (element) {
                      $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                  },
                  unhighlight: function (element) {
                      $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                  }
              });
          });  
 			</script>

  </div>
</div>
</body>
</html>