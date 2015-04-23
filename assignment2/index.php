<?php

	session_start();


?>

<html>
<head>
	<title>Ticket Request</title>
	<link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<script type="text/javascript">
	function validateForm() {
		var fields = ["fname", "lname", "email", "subject", "problem"];
		for (var i = 0; i < fields.length; i++) {
			var value = document.forms["login"][fields[i]].value;
		    if (value == null || value == "") {
		        alert("All fields must be filled out");
		        return false;
		    }
    	}
	}
</script>
<body>
<section class="logincontainer">
<div class="login" style="text-align:left;">
	<h1>Request Ticket</h1>
	<?php
      if (isset($_SESSION['blankField']))
      	echo "<p style='color:RED;'>You cannot leave any fields blank. Please try again.</p>";
    ?>

	<form name="login" method="post" action="process.php" onsubmit="return validateForm()">
		<p>First Name: <input type="text" name="fname" value="" style="margin-left:30px;"></p>
		<p>Last Name: <input type="text" name="lname" value="" style="margin-left:30px;"></p>
		<p>Email Address: <input type="email" name="email" value="" style="margin-left:12px;"></p>
		<p>Subject: <input type="text" name="subject" value="" style="margin-left:49px;"></p>
		<p>Problem Description: <br/> <textarea style="margin-left:95px;" name="problem" rows="5" value=""></textarea></p>
		<input type="submit" value="Submit">
	</form>
</div>
</section>

</body>
</html>