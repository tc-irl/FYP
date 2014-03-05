<?php

session_start();
// Helps protect against header injection attacks. Prevents multiple headers to be sent at once. 
header("Expires: Wed, 13 March 1993 10:00:00 GMT");
header("Pragma: no-cache");
header("Cache-Control: no-cache");

define("URI", $_SERVER['REQUEST_URI']);

if (URI == "/" || URI == "/index.php" || URI == "/index.php?logout") 
	header('Location: index.php?category=None');

if (URI == "/acronyms.php")
  header('Location: acronyms.php?category=None');

if((strpos(URI,'/index.php/category=') == false && (strpos(URI, 'filename=') == false)) || (isset($_GET['logout'])))
{
  if(isset($_SESSION['username']))
  {
    $link = "C:/wamp/www/uploads/" . @$_SESSION['username'] . "/";

    if(is_dir($link))
    {
      
      $dir_files = glob($link . '*', GLOB_MARK);

      foreach ($dir_files as $df)
      {
        unlink($df);
      }

     rmdir($link);
    }
  }
}

function updateMenu($navLink)
{
    if (URI == $navLink)
    		return 'class="active"';

    if (strpos(URI, $navLink) !== false)
    {
        return "class='active'";
    }
}

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

if(isset($_GET['logout']))
{
  deleteSession();
}

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Acronym Identification System">
<meta name="keywords" content="Acronym Identification, Id, Acronyms, 
Acronym Identification System, AIS, Initialisms, Acronym ID">

<!-- CSS -->
<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
<link href="/css/navigation.css" rel="stylesheet">
<link href="/css/jumbotron.css" rel="stylesheet">

</head>
<body>
    <div class="container">
    	<h1 style="text-align: center; font-size:400%;"> Acronym Identification System </h1>
        <div class="navbar navbar-default" role="navigation">
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li <?=updateMenu("/index.php")?>><a href="index.php"><span class="glyphicon glyphicon-home"> Home </span></a></li>
            <li <?=updateMenu("/acronyms.php")?>><a href="acronyms.php"><span class="glyphicon glyphicon-list"> Acronyms </span></a></li>
            <li <?=updateMenu("/suggest.php")?>><a href="suggest.php"><span class="glyphicon glyphicon-tag"> Suggest </span></a></li>
            <li <?=updateMenu("/about.php")?>><a href="about.php"><span class="glyphicon glyphicon-info-sign"> About </span></a></li>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
          <?php 

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
        </div><!--/.nav-collapse -->
      </div>
      <script type="text/javascript"></script>
      <script src="http://code.jquery.com/jquery.js"></script>
      <script src="http://getbootstrap.com/dist/js/bootstrap.min.js"></script>
</body>
</html>