<?php

require_once('lib/swift_required.php');

if(isset($_POST['submit']))
{
	if(isset($_POST['email']))
	{
		$email = mysqli_real_escape_string($db_connect,$_POST['email']);

		$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
				->setUsername('acronymid@gmail.com')
				->setPassword('testmail'); 

   			$mailer = Swift_Mailer::newInstance($transport);

			$check_email = mysqli_query($db_connect, "Select Email from users where Email='$email'");

		if(mysqli_num_rows($check_email) == 1)
        {	
        	$newpass = substr(md5(rand()),0,7);
            send_mail_to($email,$mailer,$newpass);
            $newpass = hash('sha256', $newpass);
            $update_pass = mysqli_query($db_connect, "Update users set Password='$newpass' where Email='$email'");
            $message = "Email has been sent";
            header('Location: loginreg.php?page=forgot&message=' . $message);
			exit;
        }
        else
        {
        	$message = "Email does not exist";
            header('Location: loginreg.php?page=forgot&message=' . $message);
            exit;
        }
	}
	else
	{
		$message = "No email was provided";
		header('Location: loginreg.php?page=forgot&message=' . $message);
		exit;
	}
}

function send_mail_to($to,$mailer,$newpass)
{
	$subject = "Reset Password";
	$body = "Your new password is: $newpass." .PHP_EOL . 
	"If you wish to reset your password, follow the reset link for the acronym identification site: http://acronym-id.servehttp.com/reset.php?email=$to";

	$message = Swift_Message::newInstance($subject)
	-> setFrom('acronymid@gmail.com')
	-> setTo("$to")
	-> setBody($body);

	$mailer->send($message);
}


?>

<html>
<head>
    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
</head>
<body>
		<br/>
		<form class="form-horizontal" method="post" id="forgot-form" role="form">
			 	<div class="form-group">
			   	  <label for="inputEmail" class="col-sm-3 control-label">Email</label>
			   		<div class="col-sm-6">
			     		<input type="email" name="email" class="form-control" id="inputEmail" placeholder="Email Address" maxLength="30" required>
			   		</div>
			    </div>

			    <button type="submit" name="submit" class="btn btn-primary button-right"> Submit </button>
		</form>

 	<script>
		$("#forgot-form").validate();
	</script>
</body>
</html>