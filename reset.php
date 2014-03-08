<?php
	include("header.php");
	include("db.class.php");

	if(isset($_POST['submit']))
	{
		if(isset($_POST['password']) && isset($_POST['newPassword']))
		{
			$old_pass = hash('sha256',mysqli_real_escape_string($db_connect, $_POST['password']));
			$pass = hash('sha256', mysqli_real_escape_string($db_connect, $_POST['newPassword']));
			$email = mysqli_real_escape_string($db_connect, $_GET['email']);

		    $check_pass = mysqli_query($db_connect, "Select Email from users where Email='$email' and Password ='$old_pass'" );

			if(mysqli_num_rows($check_pass) == 1)
	        {	
				$update_password = mysqli_query($db_connect, "Update users SET Password='$pass' where Email='$email'");
				$message="Your password has been reset!";
				header('Location: loginreg.php?page=login&message=' . $message);
				exit;
	        }
	        else
	        {
	        	echo "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Email does not exist</b></div>";
	        }
		}
		else
		{ 
			echo "<div class='alert alert-danger alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button> <b>Please fill in all spaces</b></div>";
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
<div class ="container">
	<div class="jumbotron">
	<br/>
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