<?php

	session_start();

  function __autoload($class) {
    require_once $class . '.php';
  }
  global $db, $ticketObject;

  $showData = false;

  $db = new mysqli("localhost","Allison","charlie","Allison");
  
  if ($db->connect_error):
      die ("Could not connect to db " . $db->connect_error);
  endif;

  # Check if the admin is logged in - if not redirect
  if (!isset($_SESSION['userID']) || !isset($_SESSION['ticket'])) {
    header('Location: admin.php');

  }

  $userID = $_SESSION['userID'];
  $ticketObject = unserialize($_SESSION['ticket']);

  $query = "SELECT * from Tickets where Tickets.Ticket_num = '$ticketObject->number'";
  $result = $db->query($query);
  $ticket = $result->fetch_array();


  # The admin has requested an action
  if (isset($_POST['action'])) {

    $action = $_POST['action'];

    # Things to do before showing ticket again
    if ($action == 'Delete Ticket') {
      $ticketObject->delete($db); 
      header('Location: admin.php');
    }

    if ($action == 'Go Back to Admin') {
      header('Location: admin.php');
    }

    if ($action == 'Close/Reopen Ticket') {
      $ticketObject->toggleStatus($db);
      $_SESSION['ticket'] = serialize($ticketObject);
      if ($ticketObject->status == 'closed') {
        $info[0] = $ticketObject->senderEmail;
        sendMail($info, true);
      }
    }

    if ($action == 'Assign Self to Ticket') {
      $ticketObject->assignTech($userID, $db);
      $_SESSION['ticket'] = serialize($ticketObject);
    }

    if ($action == 'Remove Self from Ticket') {
      $ticketObject->removeTech($db);   
    }

    # Show ticket
    showTicket();

    # Things to show after ticket
    if ($action == 'Email Sender') {
      showMailForm();
    }

    if ($action == 'Find all Tickets from Sender') {
      $senderID = $ticketObject->senderID;
      $query = returnQuery() . " WHERE Tickets.Sender_id = '$senderID'";
      $_SESSION['query'] = $query;
      $result = $db->query($query);
      showTable($result);
    }

    if ($action == 'Find Similar Tickets') {
      $subject = $ticketObject->subject;
      $ticketNum = $ticketObject->number;
      $words = explode(' ', $subject);
      //print_r($words);
      $match = "";
      for ($i = 0; $i < sizeof($words); $i++) {
        $word = $words[$i];
        if (strlen($word) > 3) {
          if ($i != 0)
            $match .= "OR";
          $match .= " Tickets.Subject LIKE '%$word%'";
        }
      }

      $query = returnQuery() . " WHERE (Tickets.Ticket_num != $ticketNum) AND ($match)";
      //$query = "SELECT * FROM Tickets WHERE Tickets.Subject LIKE '%computer%'";
      $_SESSION['query'] = $query;
      $result = $db->query($query);
      $numResults = $result->num_rows;
      if ($numResults != 0) {
        showTable($result);
      }
      else {
        echo "<br/>No similar tickets exist.";
      }

    }

    if ($action == 'Sort') {
        if (isset($_POST['sort'])) {
        $key = $_POST['sort'];
        $query = $_SESSION['query'] . " ORDER BY $key";
        $result = $db->query($query);
        showTable($result);
      }

      # Admin did not select a "Sort By"; show table as normal
      else {
        //$result = $db->query($globalQuery);
      }

      
    }

    if ($action == 'View Selected Ticket') {
        if (isset($_POST['ticket'])) {
        $num = $_POST['ticket'];

        $query = "SELECT * from Tickets WHERE Tickets.Ticket_num = '$num'";
        $result = $db->query($query);
        $ticketRow = $result->fetch_array();

        $sender = $ticketRow[2];
        $query = "SELECT * from Sender WHERE Sender.Sender_id = '$sender'";
        $result = $db->query($query);
        $senderRow = $result->fetch_array();

        $ticket = new Ticket($ticketRow[0], $ticketRow[1], $ticketRow[2], $senderRow[1], $senderRow[2], $ticketRow[3], $ticketRow[4], $ticketRow[5], $ticketRow[6]);

        $_SESSION['ticket'] = serialize($ticket);
        header('Location: detail.php');
      }

      # Admin did not select a "Ticket"; show table as normal
      else {
        // $result = $db->query($globalQuery);
        // showTable($result);
      }
    }

  }
  # No action has been requested
  else {
    showTicket();
  }

  # The admin tried to send an email
  if (isset($_POST['subject']) || isset($_POST['msg'])) {
    $result = $db->query("SELECT * from Admin where Admin.Admin_id = '$userID'");
    $admin = $result->fetch_array();

    $info[0] = $ticketObject->senderEmail;
    $info[1] = $admin[2];

    sendMail($info, false);
  }


?>

<html>
<head>
	<title>Ticket Detail</title>
  <link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<body>

  <?php

  function showTicket() {
  ?>
  <header>
    <br/>
    <h1>Ticket Administration</h1>
    <p>You are logged in with administrator ID <?php $id = $_SESSION['userID']; echo "$id"; ?></p>
  </header>
  <section class="logincontainer">
    <div class="login" style="text-align:left;">
  <?php
    $ticket = $GLOBALS['ticketObject'];
    $db = $GLOBALS['db'];
    $tech = $ticket->getTechName($db);
    echo "<h1>Ticket #$ticket->number</h1>
      <p><b>Received</b>: $ticket->received</p>
      <p><b>Sender name</b>: $ticket->senderName</p>
      <p><b>Tech</b>: $tech</p>
      <p><b>Status</b>: $ticket->status</p>
      <br/>
      <p><b>Subject</b>: $ticket->subject</p>
      <p><b>Problem</b>: $ticket->problem</p>

      </div>
      </section>";

    echo "<form action='detail.php' method='post'>
    <input type='submit' name='action' value='Close/Reopen Ticket'>";
    
    if ($ticket->tech == NULL) {
      echo "<input type='submit' name='action' value='Assign Self to Ticket'>";
    }

    if ($ticket->tech == $GLOBALS['userID']) {
      echo "<input type='submit' name='action' value='Remove Self from Ticket'>";
    }

    echo "<input type='submit' name='action' value='Email Sender'><br/>
          <input type='submit' name='action' value='Delete Ticket'>
          <input type='submit' name='action' value='Find all Tickets from Sender'>
          <input type='submit' name='action' value='Find Similar Tickets'>
          <input type='submit' name='action' value='Go Back to Admin'>

      </form>";

    }

?>


</body>
</html>

<?php function showMailForm() { ?>
  <br/>
    <br/>
  <form action = "detail.php"
          method = "POST">
          <br/>
    <b>Subject:</b><br/><br/>
    <input type = "text" name = "subject" size = "60" maxlength = "60">
    <br /><br />
    <b>Message:</b><br />
    <br />
    <textarea name="msg" rows="5" cols="60"></textarea>
    <br /><br />
    <input type = "submit" value = "Submit">
    </form>
    <br/>
    <br/>
    <br/>
    <br/>

<?php 

}

function showTable($data) {

  $actualKeys = array("Ticket_num", "Received", "Sender_name", "Sender_email", "Subject", "Name", "Status");
  $keys = array("Ticket #", "Received", "Sender Name", "Sender Email", "Subject", "Tech", "Status");  
  $rows = $data->num_rows;

  echo "<form action = 'detail.php' method = 'POST'> 
        <table class='center'>
        <caption>Requested Tickets</caption>
        <tr align = 'center'>";

       foreach ($keys as $next_key):
           echo "<th>$next_key</th>";
       endforeach;
       echo "<th>Select</th>";
       echo "</tr>"; 
       for ($i = 0; $i < $rows; $i++):
           echo "<tr align = 'center'>";
           $row = $data->fetch_array();
           foreach ($keys as $next_key){
              echo "<td> $row[$next_key] </td>";
           }
           $ticketNum = $row[$keys[0]];
           echo "<td><input type='radio' name='ticket' value='$ticketNum'></td>";
           echo "</tr>";
       endfor;
       echo "<tr>";
       foreach ($actualKeys as $next_key){
                echo "<th>Sort By <input type='radio' name='sort' value='$next_key'></th>";
       }
       echo "</tr></table><br />";

       echo "
          <input type='submit' name='action' value='Sort'> 
          <input type='submit' name='action' value='View Selected Ticket'><br/>

    </form>";

  }

function sendMail($info, $closed) {

  $receiverEmail = $info[0];
  $senderEmail = "";
 
  if (!$closed) {
    $senderEmail = $info[1];
  }

  $ticketObj = $GLOBALS['ticketObject'];

  //echo "$senderEmail";

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

  $sender = strip_tags($senderEmail);
  $receiver = strip_tags($receiverEmail);
  if (!$closed) {
    $subj = strip_tags($_POST["subject"]);
    $msg = strip_tags($_POST["msg"]);
  } else {
    $subj = "Ticket Closed";
    $msg = "The following ticket you sent in to the Ticket Administration has been closed.
          
          Subject: $ticketObj->subject
          Problem: $ticketObj->problem 

          Thank you.";

  }

  // Put information into the message
  $mail->addAddress($receiver);
  $mail->SetFrom($sender, "Ticket Administration");
  $mail->Subject = "$subj";
  $mail->Body = "$msg";

  // echo 'Everything ok so far' . var_dump($mail);
  if(!$mail->send()) {
  echo 'Message could not be sent.';
  echo 'Mailer Error: ' . $mail->ErrorInfo;
  } 
  else { echo 'Message has been sent'; }
}

function returnQuery() {
        $query = "SELECT Tickets.Ticket_num AS 'Ticket #', Tickets.Received, Sender.Sender_name AS 'Sender Name', Sender.Sender_email AS 'Sender Email', Tickets.Subject, Admin.Name AS 'Tech', Tickets.Status, Tickets.Admin_id
    FROM Tickets
      INNER JOIN Sender
        ON Tickets.Sender_id = Sender.Sender_id
      LEFT JOIN Admin
        ON Tickets.Admin_id = Admin.Admin_id";

      return $query;
  }

 ?>


