<?php

	session_start();

	function __autoload($class) {
		require_once $class . '.php';
   	}

   	global $ticket;

   	require_once 'Ticket.php';

	$db	= new mysqli("localhost","Allison","charlie","Allison");
	if ($db->connect_error):
		die ("Could not connect to db " . $db->connect_error);
	endif;

	$type = $_POST['type'];

	$actualKeys = array("Ticket_num", "Received", "Sender_name", "Sender_email", "Subject", "Name", "Status");
    $keys = array("Ticket #", "Received", "Sender Name", "Sender Email", "Subject", "Tech", "Status");

    $globalQuery = returnQuery();

    $num = $_POST['ticket'];
    $query = "SELECT * from Tickets WHERE Tickets.Ticket_num = $num";
    $result = $db->query($query);
    $ticketRow = $result->fetch_array();

    $sender = $ticketRow[2];
    $query = "SELECT * from Sender WHERE Sender.Sender_id = '$sender'";
    $result = $db->query($query);
    $senderRow = $result->fetch_array();

    $ticket = createTicket($ticketRow, $senderRow);
    $adminID = $_SESSION['adminID'];

    // Close/Reopen Ticket
	if ($type == 1) {
		$response = "";
		$ticket->toggleStatus($db);

      	$tech = $ticket->getTechName($db);
        if ($adminID == $ticketRow[4]) {
        	$currentTech = "true";
        }
        else {
        	$currentTech = "false";
        }

      	echo "$ticket->number" . "#" . "$ticket->received" . "#". "$ticket->senderName" . "#" . "$tech" . "#" . "$ticket->status" . "#"
        . "$ticket->subject" . "#" . "$ticket->problem" . "#" . $currentTech;

        if ($ticket->status == 'closed') {
        	$info[0] = $ticket->senderEmail;
        	sendMail($info, true);
      	}

	}

	    // Assign self to ticket
	if ($type == 2) {
		$response = "";
		$ticket->assignTech($adminID, $db);

		$tech = $ticket->getTechName($db);
      	$currentTech = "true";

      	echo "$ticket->number" . "#" . "$ticket->received" . "#". "$ticket->senderName" . "#" . "$tech" . "#" . "$ticket->status" . "#"
        . "$ticket->subject" . "#" . "$ticket->problem" . "#" . $currentTech;
	}

	// Remove self from ticket
	if ($type == 3) {
		$response = "";
		$ticket->removeTech($db);

		$tech = $ticket->getTechName($db);
      	$currentTech = "false";

      	echo "$ticket->number" . "#" . "$ticket->received" . "#". "$ticket->senderName" . "#" . "$tech" . "#" . "$ticket->status" . "#"
        . "$ticket->subject" . "#" . "$ticket->problem" . "#" . $currentTech;
	}

	 // Email Sender
	 if ($type == 4) {
	 	$response = "";
		$subject = $_POST['subject'];
		$message = $_POST['message'];

		$result = $db->query("SELECT * from Admin where Admin.Admin_id = '$adminID'");
	    $admin = $result->fetch_array();

	    $info[0] = $ticket->senderEmail;
	    $info[1] = $admin[2];
	    $info[2] = $subject;
	    $info[3] = $message;

		$tech = $ticket->getTechName($db);
      	if ($adminID == $ticketRow[4]) {
        	$currentTech = "true";
        }
        else {
        	$currentTech = "false";
        }

      	echo "$ticket->number" . "#" . "$ticket->received" . "#". "$ticket->senderName" . "#" . "$tech" . "#" . "$ticket->status" . "#"
        . "$ticket->subject" . "#" . "$ticket->problem" . "#" . $currentTech . "#Mail";

	    sendMail($info, false);

	 }

	// Delete ticket
	if ($type == 5) {
		$ticket->delete($db);
	}

	// Find all tickets from sender
	if ($type == 6) {
      
      $tech = $ticket->getTechName($db);
  	  if ($adminID == $ticketRow[4]) {
    	$currentTech = "true";
      }
      else {
    	$currentTech = "false";
      }

  	  echo "$ticket->number" . "#" . "$ticket->received" . "#". "$ticket->senderName" . "#" . "$tech" . "#" . "$ticket->status" . "#"
        . "$ticket->subject" . "#" . "$ticket->problem" . "#" . $currentTech . "#TicketTable";


      $senderID = $ticket->senderID;
      $query = returnQuery() . " WHERE Tickets.Sender_id = '$senderID'";
      $_SESSION['query'] = $query;
      $result = $db->query($query);

      $rows = $result->num_rows;
	  $tableRows = "#";
	  for ($i = 0; $i < $rows; $i++):
	  	$row = $result->fetch_array();
	    foreach ($keys as $next_key){
	        $tableRows .= $row["$next_key"] . "|";
	    }
        if ($i < $rows-1):
         	$tableRows .= "^";
        endif;
      endfor;

	  echo "$tableRows";

	}

	// Find similar tickets
	if ($type == 7) {

	  $tech = $ticket->getTechName($db);
  	  if ($adminID == $ticketRow[4]) {
    	$currentTech = "true";
      }
      else {
    	$currentTech = "false";
      }

  	  echo "$ticket->number" . "#" . "$ticket->received" . "#". "$ticket->senderName" . "#" . "$tech" . "#" . "$ticket->status" . "#"
        . "$ticket->subject" . "#" . "$ticket->problem" . "#" . $currentTech;

	  $subject = $ticket->subject;
      $ticketNum = $ticket->number;
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
      	echo "#TicketTable";
        $rows = $result->num_rows;
	  	$tableRows = "#";
	  	for ($i = 0; $i < $rows; $i++):
	  		$row = $result->fetch_array();
	    	foreach ($keys as $next_key){
	        	$tableRows .= $row["$next_key"] . "|";
	    	}
        	if ($i < $rows-1):
         		$tableRows .= "^";
        	endif;
      	endfor;

	  echo "$tableRows";
      }
      else {
        echo "#NoTickets";
      }
	}




function createTicket($ticketRow, $senderRow) {
	$ticket = new Ticket($ticketRow[0], $ticketRow[1], $ticketRow[2], $senderRow[1], $senderRow[2], $ticketRow[3], $ticketRow[4], $ticketRow[5], $ticketRow[6]);
	return $ticket;
}



function sendMail($info, $closed) {

  $receiverEmail = $info[0];
  $senderEmail = "";
 
  if (!$closed) {
    $senderEmail = $info[1];
  }

  $ticket = $GLOBALS['ticket'];

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
    $subj = $info[2];
    $msg = $info[3];
  } else {
    $subj = "Ticket Closed";
    $msg = "The following ticket you sent in to the Ticket Administration has been closed.
          
          Subject: $ticket->subject
          Problem: $ticket->problem 

          Thank you.";

  }

  // Put information into the message
  $mail->addAddress($receiver);
  $mail->SetFrom($sender, "Ticket Administration");
  $mail->Subject = "$subj";
  $mail->Body = "$msg";

  // echo 'Everything ok so far' . var_dump($mail);
  if(!$mail->send()) {
  echo '#Message could not be sent.';
  //echo 'Mailer Error: ' . $mail->ErrorInfo;
  } 
  else { echo '#Message has been sent'; }
}

function returnQuery() {
    $query = "SELECT Tickets.Ticket_num AS 'Ticket #', Tickets.Received, Sender.Sender_name AS 'Sender Name', Sender.Sender_email AS 'Sender Email', Tickets.Subject, Admin.Name AS 'Tech', Tickets.Status
      FROM Tickets
      INNER JOIN Sender
      ON Tickets.Sender_id = Sender.Sender_id
      LEFT JOIN Admin
      ON Tickets.Admin_id = Admin.Admin_id";

      return $query;
  }


?>