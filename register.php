<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Register page -> Allowing users to register for the site. 

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

This page also uses a JQuery Validation Plugin to help with client side validation. See: http://jqueryvalidation.org/validate/
-->

<?php
// checking if the users submitted the registration form
if(isset($_POST['submit']))
{
	// server-side validation, ensuring that each field of the form was successfully filled in. 
	if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['firstname']) && isset($_POST['lastname']))
	{
		// here we assign the filled in email to the $user variable. Note, mysqli_real_escape_string helps prevent against sql injection 
		// by escaping special characters in the string. See: http://www.w3schools.com/php/func_mysqli_real_escape_string.asp for more information. 
        $user = mysqli_real_escape_string($db_connect,$_POST['email']); 

        // Assigning the entered password to the $pass variable. Here was hash the password for security. 
        // Hashing is similar to encryption except you cannot decrypt the password, thus improving the security of the passwords provided. 
        // The sha256 is the hashing algorithm used.
        // For more information, see: http://us2.php.net/manual/en/function.hash.php. 
        $pass = hash('sha256', mysqli_real_escape_string($db_connect,$_POST['password'])); 
        $fname = mysqli_real_escape_string($db_connect,$_POST['firstname']); //here we assign the filled in first name to the $fname variable.
        $lname = mysqli_real_escape_string($db_connect,$_POST['lastname']); //here we assign the filled in last name to the $lname variable.
        $query = mysqli_query($db_connect, "Select * from users where Email='$user'"); // Query which checks if the email already exists or not. 

        // check if the email already exists, by checking if the query returns a row or not
        if(mysqli_num_rows($query) == 1)
        {	
            $message = "User already exists!";
            header('Location: loginreg.php?page=login&message=' . $message); // return to login page with error message 
            exit; // terminates the script
        }
        else
        {
        	$message = "You have successfully registered!";
        	$insert_query = mysqli_query($db_connect, "Insert into users VALUES('$fname', '$lname', '$user', '$pass')"); // insert the user into the database. 
        	header('Location: loginreg.php?page=login&message=' . $message); //return the login page with success message
        	exit; // terminates the script
        }
    }
	else
	{
		// server-side validation error message is one or more of the spaces was not filled in. 
	    $message = "Please fill in all spaces.";
        header('Location: loginreg.php?page=login&message=' . $message); //return the login page with error message
        exit; // terminates the script
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

		<!-- Registration form. Twitter bootstrap form elements -->
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
			<!-- JQuery validation plugin. validate() - Validates the form. highlight() - specifies how to highlight the invalid fields and unhighlight() specifies how to respond to valid fields. See: http://jqueryvalidation.org/validate/ --> 
	 		<script>
 			$(document).ready(function(){
              $("#register-form").validate({
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