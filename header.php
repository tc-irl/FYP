<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: Header page -> Used for the constant parts of the site, including the navigation bar (and it's updating), title etc...

This page contains some elements used from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap
-->

<?php

global $link;

session_start(); // Start a session, which is used for user logins. Sessions are similar to cookies. 

// Helps protect against header injection attacks. Prevents multiple headers to be sent at once. See http://www.w3schools.com/php/func_http_header.asp
header("Expires: Wed, 13 March 1993 10:00:00 GMT");
header("Pragma: no-cache");
header("Cache-Control: no-cache");

define("URI", $_SERVER['REQUEST_URI']); // Define a variable URI as the URI of the site. 

// If statement to redirect users to the home page
if (URI == "/" || URI == "/index.php" || URI == "/index.php?logout") 
	header('Location: index.php?category=None'); // Send header to the client, which redirects them to the index.php page with category none 

if (URI == "/acronyms.php")
  header('Location: acronyms.php?category=None'); // Send header to the client, which redirects them to the acronym.php page with category none

// Checking if user changes page or logs out, if so we can now delete the temporary folder we created to store the uploaded file. 
if((strpos(URI,'/index.php/category=') == false && (strpos(URI, 'filename=') == false)) || (isset($_GET['logout'])))
{
  // check if the user is logged
  if(isset($_SESSION['username']))
  {
    $link = "./uploads/" . @$_SESSION['username'] . "/"; //A link to where the users temporary folder will be located. Example: uploads/user1@hotmail.com

    // checking if the directory exists
    if(is_dir($link))
    {
      // returns all the file names in the directory
      $dir_files = glob($link . '*', GLOB_MARK);

      // loop through each file name and delete them 
      foreach ($dir_files as $df)
      {
        unlink($df);
      }

     rmdir($link); // Delete the directory. 
    }
  }
}
// check if the users session has ended, which is when the browser is closed (or specified otherwise in php.ini) and delete the temporary folder and file(s). 
else if(!isset($_SESSION['username']))
{
    $dir_files = glob($link . '*', GLOB_MARK);

    foreach ($dir_files as $df)
    {
      unlink($df);
    }

    rmdir($link);
}

// Updating the navigation bar, highlighting the current tab of the page you're on. 
function updateMenu($navLink)
{
    // checking if the URI matches the passed in navigation link and if so, 
	// return class = active which highlights the tab on the navigation bar. 
    if (URI == $navLink)
    		return 'class="active"';

    // checking if the navigation link is a substring of the URI and if so, return class = active which highlights 
	// the tab on the navigation bar. 
    // an example of this is index.php?category=none

    if (strpos(URI, $navLink) !== false)
    {
        return "class='active'";
    }
}

// A function which returns whether or not the user is logged in by checking if the session is set or not. 
function isLoggedIn()
{
  if(isset($_SESSION['username']))
  {
    return true;
  }
  else
  {
    return false;
  }
}

// Checking if the logout tab is clicked and if so call deleteSession() which deletes the session
if(isset($_GET['logout']))
{
  deleteSession();
}

// functions which checks if the user is currently logged in and if so, unset the session. 
function deleteSession()
{
  if(isset($_SESSION['username']))
  {
      unset($_SESSION['username']);
  }
}

?>

<!DOCTYPE html>
<html>
<head>

<title> Acronym Identification System </title>

<!-- Meta data -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Twitter bootstrap - Enables responsive features which allows your site to be used by multiple screen sizes (and resizing) and multiple devices. See: http://getbootstrap.com/2.3.2/scaffolding.html#responsive--> 
<meta name="description" content="Acronym Identification System">
<meta name="keywords" content="Acronym Identification, Id, Acronyms, 
Acronym Identification System, AIS, Initialisms, Acronym ID">

<!-- CSS -->
<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
<link href="/css/navigation.css" rel="stylesheet">
<link href="/css/jumbotron.css" rel="stylesheet">

</head>
<body>
<h1 style="text-align: center; font-size:400%;"> Acronym Identification System </h1> <!-- Title -->
<div class="container"> <!-- Twitter bootstrap container for common fixed-width layout -->
	<div class="navbar navbar-default" role="navigation"> <!-- Twitter bootstrap navigation bar -->
          <ul class="nav navbar-nav"> <!-- Tabs on the navigation bar, starting from the left -->
		  <!-- List of elements which go on the navigation bar. updateMenu() is used highlight the active class -->
            <li <?=updateMenu("/index.php")?>><a href="index.php"><span class="glyphicon glyphicon-home"> Home </span></a></li> 
            <li <?=updateMenu("/acronyms.php")?>><a href="acronyms.php"><span class="glyphicon glyphicon-list"> Acronyms </span></a></li>
            <li <?=updateMenu("/suggest.php")?>><a href="suggest.php"><span class="glyphicon glyphicon-tag"> Suggest </span></a></li>
            <li <?=updateMenu("/about.php")?>><a href="about.php"><span class="glyphicon glyphicon-info-sign"> About </span></a></li>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right"> <!-- Tabs on the right of the navbar -->
          <?php   
            // checking if the user is logged in and if they are, display logout tab and if they aren't display login/register tab. 
            if(isLoggedIn())
            {
                echo "<li><a href='index.php?logout'><span class='glyphicon glyphicon-log-in'> Logout </span></a></li>";
            }
            else
            {
                echo "<li " . updateMenu("/loginreg.php") . ">" . 
                "<a href='loginreg.php?page=login'><span class='glyphicon glyphicon-log-in'> Login/Register </span></a></li>";
            }
          ?>
          </ul>
        </div> 
      </div>
      <script type="text/javascript"></script>
      <script src="http://code.jquery.com/jquery.js"></script>
      <script src="http://getbootstrap.com/dist/js/bootstrap.min.js"></script>
</body>
</html>