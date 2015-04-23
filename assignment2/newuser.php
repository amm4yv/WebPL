<?php session_start() ?>
<!DOCTYPE html>
<html>
<head>
<title>Quiz of the Day</title>
<link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<body>
  <header>
    <h1>Quiz of the Day</h1>
  </header>

<section class="container">
    <div class="login">
      <h1>Create an Account</h1>
      <?php
    
    if(isset($_SESSION['error'])){
      $error = $_SESSION['error'];
      if (strcmp($error, "passwordMatch") == 0){
        echo "<p style='color:RED;'>Passwords didn't match. Please try again.</p><br/>";
      }
      if (strcmp($error, "userExists") == 0){
        echo "<p style='color:RED;'>Invalid user or user already exists. Please try again.</p><br/>";
      }
      unset($_SESSION['error']);
    }

    ?>
      <form method="post" action="index.php">
        <p><input type="text" name="newUser" value="" placeholder="Username"></p>
        <p><input type="password" name="newPass1" value="" placeholder="Password"></p>
        <p><input type="password" name="newPass2" value="" placeholder="Confirm Password"></p>
        <p class="submit"><input type="submit" name="submit" value="Create Account"></p>
      </form>
    </div>
    <div class="login-help">
      <p>Already have a username? <a href="index.php">Click here to login</a>.</p>
    </div>
  </section>
</body>
</html>
