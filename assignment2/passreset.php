<?php 

  session_start();

  $query = $_SERVER['QUERY_STRING'];

  $setNew = false;
  $reset = false;
  $resetError = false;

  $db = new mysqli("localhost","Allison","charlie","Allison");
  if ($db->connect_error):
    die ("Could not connect to db " . $db->connect_error);
  endif;

  //echo "$query";

  # The admin got here via reset link
  if ($query != null && isset($_COOKIE['userID'])) {
    echo "$query";
    $id = $_COOKIE['userID'];
    $hashed = hash('sha256', rtrim($id));

    if ($hashed == $query) {
      $setNew = true;
      $reset = true;
    }
  }

  if (isset($_POST['pass1'])){
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    if (strcmp($pass1, $pass2) !== 0){
      $resetError = true;
    }
    else {

      $password = hash('sha256', rtrim($pass1));

      $query = "UPDATE Admin SET Password='$password' WHERE Admin_id = '$id'";
      $db->query($query);

      header("Location: admin.php");

    }
  }

  # The admin attempted to send email
  if (isset($_POST['id']) && isset($_POST['email'])){

    $id = $_POST['id'];
    $email = $_POST['email'];

    $query = "SELECT * from Admin WHERE Admin.Admin_id = '$id' AND Admin.Email = '$email'";
    $result = $db->query($query);
    $rows = $result->num_rows;

    # Information correct
    if ($rows != 0) {
      $_SESSION['userID'] = $id;
      setcookie("userID", "$id", time()+60);
      $reset = true;

      $id = hash('sha256', rtrim($id));

      send_Mail($email, $id);
    } 

    else {
      $resetError = true;
    }

  }



?>
<!DOCTYPE html>
<html>
<head>
<title>Ticket System Admin Panel</title>
<link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<script type="text/javascript">
  function validateForm() {
    var pass1 = document.forms["newPass"]["pass1"].value;
    var pass2 = document.forms["newPass"]["pass2"].value;
    if (pass1 == null || pass1 == "" || pass2 == null || pass2 == "") {
      alert("All fields must be filled out");
      return false;
    }
    if (pass1 != pass2) {
      alert("Passwords do not match");
      return false;
    }
      
  }
</script>
<body>
<header>
    <br/>
    <h1>Ticket Administration</h1>
  </header>

<section class="logincontainer">
      <?php
    
    if(!$reset){
      echo "<div class='login'>
            <h1>Reset Your Password</h1>";
      if ($resetError) {
        echo "<p style='color:RED'>Error. Please try again.</p>";
      }
    ?>
      <form method="post" action="passreset.php">
        <p><input type="text" name="id" value="" placeholder="Admin ID"></p>
        <p><input type="email" name="email" value="" placeholder="Admin Email"></p>
        <p class="submit"><input type="submit" name="submit" value="Send Reset Email"></p>
      </form>
    </div>
    <div class="login-help">
      <p>Already have a username and password? <a href="admin.php">Click here to login</a>.</p>
    </div>
  <?php 
      }
      else if (!$setNew) {
        echo "<div class='login'>
              <h1>Email Sent</h1>
              <p>Please check your email for a link to reset your password.</p>
              </div>";

      }
      else {
      echo "<div class='login'>
            <h1>Reset Your Password</h1>";
      if ($resetError) {
        echo "<p style='color:RED'>Error. Please try again.</p>";
      }
    ?>
      <form method="post" action="passreset.php" name="newPass" onsubmit="return validateForm()">
        <p><input type="password" name="pass1" value="" placeholder="New Password"></p>
        <p><input type="password" name="pass2" value="" placeholder="Confirm New Password"></p>
        <p class="submit"><input type="submit" name="submit" value="Reset Password"></p>
      </form>
    </div>
  <?php 
      }
  ?>
  </section>
</body>
</html>

<?php

  function send_Mail($email, $id) {

    $receiverEmail = $email;
    $receiverID = $id;

    $subject = "Ticket Administration - Password Reset";
    $message = "You requested to reset your password on the Ticket Administration Website.<br/><br/>
            Link to reset: http://localhost/assignment2/passreset.php?".$id;

    set_include_path('/Applications/XAMPP/xamppfiles/PHPMailer/');
    require 'PHPMailerAutoload.php';
    $mail = new PHPMailer();

    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->SMTPAuth = true; // enable SMTP authentication
    $mail->SMTPSecure = "tls"; // sets tls authentication
    $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server; or your email service
    $mail->Port = 587; // set the SMTP port for GMAIL server; or your email server port
    $mail->Username = "uva.cs4501@gmail.com"; // email username
    $mail->Password = "UVACSROCKS"; // email password

    //$sender = strip_tags($senderEmail);
    $receiver = strip_tags($receiverEmail);
    $subj = strip_tags($subject);
    $msg = strip_tags($message);

    // Put information into the message
    $mail->addAddress($receiver);
    $mail->SetFrom("", "Ticket Administration");
    $mail->Subject = "$subj";
    $mail->Body = "$msg";

    // echo 'Everything ok so far' . var_dump($mail);
    if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
    } 
    else { echo 'Message has been sent'; }



  }



?>
