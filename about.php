<!--

Author: Tony Cullen (C10385847)
College: Dublin Institute of Technology (DIT)
Module: Final Year Project
Project: Acronym Identification System
Page: About page -> Provides basic information on the site.

This page contains some elements from Twitter Bootstrap, such as icons, buttons or layouts. They will marked with "Twitter bootstrap" in the comments. 
See http://getbootstrap.com/2.3.2/ for a complete list of elements within Twitter Bootstrap
-->

<?php
	include("header.php"); // including the header page (for the title, navigation bar, and other functions)
?>

<html>
<head>
</head>
<body>
<div class="container"> <!-- Twitter bootstrap container for common fixed-width layout -->
<div class="jumbotron"> <!-- Twitter bootstrap jumbotron for the grey background of body -->
	<h2> About Page </h2>
	<div>
		<p> The Acronym Identification System allows you to find out the meaning of various acronyms across multiple categories.</p>
			<p> It also allows you to either search for a single acronym or attach a document in order determine the meaning of all the acronyms found in the text. 
			If the category is not specified, the system will also attempt to automatically classify the document. </p>
		<br/>
		<h4>For further information, or help, please contact me by email: <b>acronymid@gmail.com</b>
		I will attempt to answer your questions or provide as much assistance as I possibly can.  
		</h4>
	</div>
</div> <!-- Close jumbotron -->
</div> <!-- Close container-->
</body>
</html>
