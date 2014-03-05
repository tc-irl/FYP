<?php
if(isset($_POST['submit']))
{
	if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['firstname']) && isset($_POST['lastname']))
	{
        $user = mysqli_real_escape_string($db_connect,$_POST['email']);
        $pass = hash('sha256', mysqli_real_escape_string($db_connect,$_POST['password']));
        $fname = mysqli_real_escape_string($db_connect,$_POST['firstname']);
        $lname = mysqli_real_escape_string($db_connect,$_POST['lastname']);
        $query = mysqli_query($db_connect, "Select * from users where Email='$user'");

        if(mysqli_num_rows($query) == 1)
        {	
            $message = "User already exists!";
            header('Location: loginreg.php?page=login&message=' . $message);
            exit;
        }
        else
        {
        	$message = "You have successfully registered!";
        	$insert_query = mysqli_query($db_connect, "Insert into users VALUES('$fname', '$lname', '$user', '$pass')");
        	header('Location: loginreg.php?page=login&message=' . $message);
        	exit;
        }
    }
	else
	{
	    $message = "Please fill in all spaces.";
        header('Location: loginreg.php?page=login&message=' . $message);
        exit;
	}  
}
?>

<html>
<head>
    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
</head>
<body>
		<br/>
		<form action="loginreg.php?page=register" method="post" class="form-horizontal" role="form" id="register-form">
			 	<div class="form-group">
			   	  <label for="inputFirstName" class="col-sm-3 control-label">First Name</label>
			   		<div class="col-sm-6">
			     		<input type="text" name="firstname" class="form-control" id="inputFirstName" maxLength="20" minLength="1" placeholder="First Name" required>
			   		</div>
			    </div>

			  <div class="form-group">
			  	<label for="inputLastName" class="col-sm-3 control-label">Last Name</label>
			    	<div class="col-sm-6">
			     		 <input type="text" name="lastname" class="form-control" id="inputLastName" maxlength="20" minLength="1" placeholder="Last Name" required>
			   	    </div>
			  </div>

			  <div class="form-group">
			  	<label for="inputEmail" class="col-sm-3 control-label">Email</label>
			    	<div class="col-sm-6">
			     		 <input type="email" name="email" class="form-control" id="inputEmail" placeholder="Email Address" maxLength="30" required>
			   	    </div>
			  </div>

			  <div class="form-group">
			  	<label for="inputPassword" class="col-sm-3 control-label">Password</label>
			    	<div class="col-sm-6">
			     		 <input type="password" name="password" class="form-control" id="inputPassword" minlength="4" maxLength="11" placeholder="Password" required>
			   	    </div>
			  </div>


			  <button type="submit" name="submit" class="btn btn-primary button-right"> Register </button>
	</form>

	 		<script>
 				$("#register-form").validate();
 			</script>
</body>
</html>