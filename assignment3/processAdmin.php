<?php

	session_start();

	function __autoload($class) {
		require_once $class . '.php';
   	}

   	require_once 'Ticket.php';

   	set_include_path('/Applications/XAMPP/xamppfiles/PHPMailer/');
	require 'PHPMailerAutoload.php';

	$db	= new mysqli("localhost","Allison","charlie","Allison");
	if ($db->connect_error):
		die ("Could not connect to db " . $db->connect_error);
	endif;

	 # Use to toggle buttons
	if (!isset($_SESSION['buttons'])){
	    $buttons[0] = 'View All Tickets';
	    $buttons[1] = 'View My Tickets';
	    $buttons[2] = 'View Unassigned Tickets';
	    $_SESSION['buttons'] = $buttons;
	}

	$type = $_POST['type'];

	$actualKeys = array("Ticket_num", "Received", "Sender_name", "Sender_email", "Subject", "Name", "Status");
    $keys = array("Ticket #", "Received", "Sender Name", "Sender Email", "Subject", "Tech", "Status");

    $globalQuery = returnQuery();

    if ($type == "button") {
    	$buttons = $_SESSION['buttons'];
    	$response = "$buttons[0]^$buttons[1]^$buttons[2]";
    	echo "$response";
    	return;
    }

    if (isset($_SESSION['currentQuery'])) {
    	$currentQuery = $_SESSION['currentQuery'];
    } else {
    	$currentQuery = $globalQuery . " WHERE Tickets.Status = 'open'";
    }

    // View Open Tickets
	if ($type == 0 || $type == 1) {
		$adminInSession = isset($_SESSION['adminID']);
		$buttons[0] = 'View All Tickets';
      	$buttons[1] = 'View My Tickets';
      	$buttons[2] = 'View Unassigned Tickets';
      	$_SESSION['buttons'] = $buttons;

		if (!$adminInSession) {
			echo "Error";
			return;
		} else {
			$query = $globalQuery . " WHERE Tickets.Status = 'open'";
    		$_SESSION['currentQuery'] = $query;
    		$result = $db->query($query);

    		$rows = $result->num_rows;
	    	$tableRows = "";
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
	}

	if ($type == 9) {
		session_destroy();
		return;
	}

	// View All Tickets
	if ($type == 2) {
	  $buttons[0] = 'View Open Tickets';
      $buttons[1] = 'View My Tickets';
      $buttons[2] = 'View Unassigned Tickets';
      $_SESSION['buttons'] = $buttons;

      $result = $db->query($globalQuery);
      $_SESSION['currentQuery'] = $globalQuery;

      $rows = $result->num_rows;
		$tableRows = "";
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

	 // View Selected Ticket
	 if ($type == 4) {

	 	$response = "";

	 	if (isset($_POST['ticket'])) {
	        $num = $_POST['ticket'];

	        //echo "$num";

	        $adminID = $_SESSION['adminID'];

	        $query = "SELECT * from Tickets WHERE Tickets.Ticket_num = '$num'";
	        $result = $db->query($query);
	        $ticketRow = $result->fetch_array();

	        $sender = $ticketRow[2];
	        $query = "SELECT * from Sender WHERE Sender.Sender_id = '$sender'";
	        $result = $db->query($query);
	        $senderRow = $result->fetch_array();

	        $ticket = new Ticket($ticketRow[0], $ticketRow[1], $ticketRow[2], $senderRow[1], $senderRow[2], $ticketRow[3], $ticketRow[4], $ticketRow[5], $ticketRow[6]);

	        $_SESSION['ticket'] = serialize($ticket);

	        $tech = $ticket->getTechName($db);
	        if ($adminID == $ticketRow[4]) {
	        	$currentTech = "true";
	        }
	        else {
	        	$currentTech = "false";
	        }

	        echo "$ticket->number" . "#" . "$ticket->received" . "#". "$ticket->senderName" . "#" . "$tech" . "#" . "$ticket->status" . "#"
        . "$ticket->subject" . "#" . "$ticket->problem" . "#" . $currentTech;
	      }

      # Admin did not select a "Ticket"; show table as normal
      else {
        $result = $db->query($globalQuery);
        //showTable($result);
      }


/*

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

*/


	 }


	 // View My Tickets
	if ($type == 5) {

	  $buttons[0] = 'View All Tickets';
      $buttons[1] = 'View Open Tickets';
      $buttons[2] = 'View Unassigned Tickets';
      $_SESSION['buttons'] = $buttons;

      $adminID = $_SESSION['adminID'];
      $query = $globalQuery . " WHERE Tickets.Admin_id = '$adminID'";
      $_SESSION['currentQuery'] = $query;
      $result = $db->query($query);

      $rows = $result->num_rows;
		$tableRows = "";
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

	 //View Unassigned Tickets
	 if ($type == 7) {
	  $buttons[0] = 'View All Tickets';
      $buttons[1] = 'View My Tickets';
      $buttons[2] = 'View Open Tickets';
      $_SESSION['buttons'] = $buttons;

      $query = $globalQuery . " WHERE (Tickets.Admin_id IS NULL OR Tickets.Admin_id = '')";
      $_SESSION['currentQuery'] = $query;
      $result = $db->query($query);

      $rows = $result->num_rows;
		$tableRows = "";
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

	 // Sort
	if ($type == 8) {
		if (isset($_POST['sort'])) {
        	$key = $_POST['sort'];
        	$query = $currentQuery . " ORDER BY $key";
        	$result = $db->query($query);
      	}

      # Admin did not select a "Sort By"; show table as normal
      else {
        $result = $db->query($currentQuery);
      }

      $rows = $result->num_rows;
		$tableRows = "";
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

	// Change Password
  if ($type == 10) {

  	$id = $_SESSION['adminID'];
    $password = hash('sha256', rtrim($_POST['pass']));

      $query = "SELECT * from Admin WHERE Admin.Admin_id = '$id'";
      $result = $db->query($query);
      $rows = $result->num_rows;
      $data = $result->fetch_array();
      # Admin Login successful
      if ($rows != 0) {
	      $query = "UPDATE Admin SET Password='$password' WHERE Admin_id = '$id'";
      	  $db->query($query);
	      echo "Admin";
      } 
      else {
      	echo "Error";
      }
    

  }

function send_Mail($info) {

	$email = $info[0];
	$subject = $info[1];
	$problem = $info[2];
	$isAdmin = $info[3];

	$mail = new PHPMailer();

	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->SMTPAuth = true; // enable SMTP authentication
	$mail->SMTPSecure = "tls"; // sets tls authentication
	$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server; or your email service
	$mail->Port = 587; // set the SMTP port for GMAIL server; or your email server port
	$mail->Username = "amm765@gmail.com"; // email username
    $mail->Password = "CharliE 7655"; // email password

	#$sender = strip_tags($_POST["sender"]);
	$receiver = strip_tags($email);
	if (!$isAdmin) {
		$subj = strip_tags("Ticket Request Confirmation");
		$msg = strip_tags("This is confirming your ticket request. Here is the request you sent:<br/><br/>
			Subject: $subject <br/>
			Problem: $problem ");
	}
	else {
		$subj = strip_tags("New Ticket Request Submitted");
		$msg = strip_tags("A new ticket request has been created. <br/>
							<a href='http://localhost/assignment2/admin.php'>Go to admin site</a>");
	}

	// Put information into the message
	$mail->addAddress($receiver);
	#$mail->SetFrom($sender);
	$mail->Subject = "$subj";
	$mail->Body = "$msg";

	// echo 'Everything ok so far' . var_dump($mail);
	if(!$mail->send()) {
	//echo 'Message could not be sent.';
	//echo 'Mailer Error: ' . $mail->ErrorInfo;
	} 
	else { 
		//echo 'Message has been sent'; 
	}
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