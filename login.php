<?php
 
 if(isset($_POST['submit']))
 {
	if(isset($_POST['email']) && isset($_POST['password']))
	{
        $user = mysqli_real_escape_string($db_connect,$_POST['email']);
        $pass = hash('sha256',mysqli_real_escape_string($db_connect,$_POST['password']));
        $query = mysqli_query($db_connect, "Select * from users where Email='$user' AND Password='$pass' limit 1");

        if(mysqli_num_rows($query) == 1)
        {	
        	session_start();
            $_SESSION['username'] = $user;
            header('Location: index.php');
            exit;
        }
        else
        {
        	$message = "Username/Password Invalid";
            header('Location: loginreg.php?page=login&message=' . $message);
            exit;
        }
    }
	else
	{
	    $message = "Username/Password Invalid";
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
	 		
	 <script>
 		$("#login-form").validate();
 	</script>

</body>
</html>