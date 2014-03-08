<?php
	include("header.php");
	require_once("db.class.php");
	require_once('lib/swift_required.php');

	if(isset($_POST['submit']))
	{
		if(isset($_SESSION['username']))
		{
			if(isset($_POST['acronym']) && isset($_POST['definition']) && isset($_POST['category']) && isset($_POST['source']))
			{

				$acronym = mysqli_real_escape_string($db_connect,$_POST['acronym']);
				$definition = mysqli_real_escape_string($db_connect,$db_connect,$_POST['definition']);
				$category = mysqli_real_escape_string($db_connect,$_POST['category']);
				$source = mysqli_real_escape_string($db_connect,$_POST['source']);
				$email = $_SESSION['username'];

			    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
				->setUsername('acronymid@gmail.com')
				->setPassword('testmail'); 

   				$mailer = Swift_Mailer::newInstance($transport);
	            send_mail_from($email,$acronym,$definition,$category,$source,$mailer);
	            send_mail_to($email,$acronym,$mailer);

				echo "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b>Thank you for your suggestion </b></div>";
			}
			else
			{
				echo "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b> Please fill in all spaces </b></div>";
			}
		}
		else
		{
			echo "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><b> Please login to suggest an acronym </b></div>"; 
		}
	}

	function send_mail_from($from, $acronym, $definition, $category, $source,$mailer)
	{

		$subject = "Acronym Suggestion";
		$body = "The user " . $from . " has suggested the following acronym:" . PHP_EOL . 
		"Acronym: " . $acronym . PHP_EOL . 
		"Definition: " . $definition . PHP_EOL . 
		"Category: " . $category . PHP_EOL . 
		"Source: " . $source . PHP_EOL;

		$message = Swift_Message::newInstance($subject)
		-> setFrom("$from")
		-> setTo('acronymid@gmail.com')
		-> setBody($body);

		$mailer->send($message);
	}
	function send_mail_to($to,$acronym,$mailer)
	{
		$subject = "Acronym Suggestion";
		$body = "Thanks for providing a suggestion for the acronym "  . $acronym . ".If the acronym is accepted it will be added to our database";

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
<div class ="container">
	<div class="jumbotron">
		<p> To suggest an acronym, please login and then submit the form below: </p>
		<br/>
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