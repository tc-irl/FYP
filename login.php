<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Login page -> Allows the user to login to the site.

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap

This page also uses a JQuery Validation Plugin to help with client side validation. See: http://jqueryvalidation.org/validate/
-->

<?php
 // checking if the users submitted the login form
 if(isset($_POST['submit']))
 {
    // server-side validation, ensuring that each field of the form was successfully filled in. 
	  if(isset($_POST['email']) && isset($_POST['password']))
	  {
         // here we assign the filled in email to the $user variable. Note, mysqli_real_escape_string helps prevent against sql injection 
         // by escaping special characters in the string. See: http://www.w3schools.com/php/func_mysqli_real_escape_string.asp for more information. 
        $user = mysqli_real_escape_string($db_connect,$_POST['email']); 

        // Assigning the entered password to the $pass variable. Here was hash the password for security. 
        // Hashing is similar to encryption except you cannot decrypt the password, thus improving the security of the passwords provided. 
        // The sha256 is the hashing algorithm used.
        // For more information, see: http://us2.php.net/manual/en/function.hash.php. 

        $pass = hash('sha256',mysqli_real_escape_string($db_connect,$_POST['password']));

        echo "User: " . $user;
        echo "Pass: " . $pass;
        $query = mysqli_query($db_connect, "Select * from users where Email='$user' AND Password='$pass' limit 1"); // Query which checks if the username and password match a user.

         // check if the user exists, by checking if the query returns a row or not
        if(mysqli_num_rows($query) == 1)
        {	
        	  session_start(); // start session
            $_SESSION['username'] = $user; // set the username of the session to be the username filled into the form
            header('Location: index.php'); // return to index page
            exit; // terminates the script
        }
        else
        {
        	  $message = "Username/Password Invalid";
            header('Location: loginreg.php?page=login&message=' . $message); // return to login page with error message 
            exit; // terminates the script
        }
    }
	else
	{
	      $message = "Username/Password Invalid";
        header('Location: loginreg.php?page=login&message=' . $message); // return to login page with error message 
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
  <!-- Login form. Twitter bootstrap form elements -->
    <form class="form-horizontal" method="post" role="form" id="login-form">
		 	<div class="form-group">
		   	  <label for="inputEmail" class="col-sm-3 control-label">Email</label>
		   		<div class="col-sm-6">
		     		<input type="email" name="email" class="form-control" id="inputEmail" placeholder="Email Address" maxLength="30" required>
		   		</div>
		    </div>

			 <div class="form-group">
			  	<label for="inputPassword" class="col-sm-3 control-label">Password</label>
			    	<div class="col-sm-6">
			     		 <input type="password" name="password" class="form-control" minLength="4" maxLength="11" id="inputPassword" placeholder="Password" required>
			   	    </div>
			 </div>

			  <button type="submit" name="submit" class="btn btn-primary button-right"> Login </button>
	</form>
	 	<!-- JQuery validation plugin. validate() - Validates the form. highlight() - specifies how to highlight the invalid fields and unhighlight() specifies how to respond to valid fields. See: http://jqueryvalidation.org/validate/ --> 
	 <script>
          $(document).ready(function(){
              $("#login-form").validate({
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