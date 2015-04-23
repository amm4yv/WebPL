<?php

	session_start();

	function __autoload($class) {
		require_once $class . '.php';
   	}

   	set_include_path('/Applications/XAMPP/xamppfiles/PHPMailer/');
	require 'PHPMailerAutoload.php';

	$db	= new mysqli("localhost","Allison","charlie","Allison");
	if ($db->connect_error):
		die ("Could not connect to db " . $db->connect_error);
	endif;

	$type = $_POST['type'];

	if ($type == 0) {
		$userInSession = isset($_SESSION['userID']);
		$adminInSession = isset($_SESSION['adminID']);
		if ($userInSession)
			echo "User";
		else if ($adminInSession)
			echo "Admin";
		else
			echo "Error";
		return;
	}

	if ($type == 9) {
		session_destroy();
		return;
	}

	//Log in
	if ($type == 1) {
		$id = $_POST['id'];
	    $password = hash('sha256', rtrim($_POST['pass']));

	    $query = "SELECT * from Sender WHERE Sender.Sender_id = '$id' AND Sender.Password = '$password'";
	    $result = $db->query($query);
	    $rows = $result->num_rows;
	    $data = $result->fetch_array();

	    # User Login successful
	    if ($rows != 0) {
	      $_SESSION['userID'] = $id;
	      $_SESSION['userdata'] = $data;
	      echo "User";
	    } 
	    # User Login unsuccessful
	    else {
	      $query = "SELECT * from Admin WHERE Admin.Admin_id = '$id' AND Admin.Password = '$password'";
	      $result = $db->query($query);
	      $rows = $result->num_rows;
	      # Admin Login successful
	      if ($rows != 0) {
		      $_SESSION['adminID'] = $id;
		      echo "Admin";
		      //header("Location: admin.php");
	      } 
	      else {
	      	echo "Error";
	      }
	    }

	 }

	 //Submit ticket
	if ($type == 2) {

		$sender = $_SESSION['userdata'];

		$senderID = $sender['Sender_id'];
		$email = $sender['Sender_email'];
		$subject = strip_tags($_POST['subject']);
		$problem = strip_tags($_POST['problem']);

     		
 		#Send Email confirmation
 		$info = [$email, $subject, $problem, false];
		//send_Mail($info);

		# Get all admin email addresses and send to admin
		$info[3] = true;
		$getAdminEmail = $db->query("SELECT Admin.Email from Admin");
		$numEmails = $getAdminEmail->num_rows;

		for($i = 0; $i < $numEmails; $i++) {
			$adminEmail = $getAdminEmail->fetch_array();
			$info[0] = $adminEmail[0];
			//send_Mail($info);
		}		

		$ticketResult = $db->query("SELECT MAX(Ticket_num) AS max_id FROM Tickets");
		$row = $ticketResult->fetch_array();
		$ticketNum = $row["max_id"] + 1;

		//$date = CURRENT_TIMESTAMP();
		$created_date = date("Y-m-d H:i:s");

		# --- Update Database ----------------- #
		$ticketQuery = "INSERT into Tickets VALUES ($ticketNum, '$created_date', '$senderID', '$subject', NULL, 'open', '$problem')";
		$db->query($ticketQuery);
		# ------------------------------------ #

		//echo "$ticketQuery";

	}

	//View Ticket
	if ($type == 3) {

		$sender = $_SESSION['userdata'];
		$senderID = $sender['Sender_id'];


		$globalQuery = "SELECT Tickets.Ticket_num AS 'Ticket #', Tickets.Received, Sender.Sender_name AS 'Sender Name', Sender.Sender_email AS 'Sender Email', Tickets.Subject, Admin.Name AS 'Tech', Tickets.Status
     				FROM Tickets
       				INNER JOIN Sender
         			ON Tickets.Sender_id = Sender.Sender_id
       				LEFT JOIN Admin
         			ON Tickets.Admin_id = Admin.Admin_id";

      	$query = $globalQuery . " WHERE Tickets.Sender_id = '$senderID'";
      	$result = $db->query($query);

	 		
		//echo "$data";
		if ($result == NULL) {
		  echo "No Tickets";
		  return;
		} 
		else {
		    //$actualKeys = array("Ticket_num", "Received", "Subject", "Status");
		    $keys = array("Ticket #", "Received", "Subject", "Status");


	    
	    	$rows = $result->num_rows;
	    	$tableRows = "";
	        for ($i = 0; $i < $rows; $i++):
	        	$row = $result->fetch_array();
	        //print_r($row);
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

  // Change Password
  if ($type == 4) {

  	$id = $_POST['id'];
    $password = hash('sha256', rtrim($_POST['pass']));
    $reset = $_POST['reset'];

    $query = "SELECT * from Sender WHERE Sender.Sender_id = '$id'";
    $result = $db->query($query);
    $rows = $result->num_rows;
    $data = $result->fetch_array();

    # User Login successful
    if ($rows != 0) {
    	$senderReset = $data['Reset'];
    	if ($senderReset != $reset) {
    		echo "Invalid Reset";
    		return;
    	}
      $_SESSION['userID'] = $id;
      $_SESSION['userdata'] = $data;
      $query = "UPDATE Sender SET Password='$password' WHERE Sender_id = '$id'";
      $db->query($query);
      //Remove reset key because succeeded
	$query = "UPDATE Sender SET Reset='' WHERE Sender_id = '$id'";
	$result = $db->query($query);
      echo "User";
    } 
    # User Login unsuccessful
    else {
      $query = "SELECT * from Admin WHERE Admin.Admin_id = '$id'";
      $result = $db->query($query);
      $rows = $result->num_rows;
      $data = $result->fetch_array();
      # Admin Login successful
      if ($rows != 0) {
	      	$adminReset = $data['Reset'];
	    	if ($adminReset != $reset) {
	    		echo "Invalid Reset";
	    		return;
	    	}
	      $_SESSION['adminID'] = $id;
	      $query = "UPDATE Admin SET Password='$password' WHERE Admin_id = '$id'";
      	  $db->query($query);
      	  //Remove reset key because succeeded
			$query = "UPDATE Admin SET Reset='' WHERE Admin_id = '$id'";
			$result = $db->query($query);
	      echo "Admin";
	      //header("Location: admin.php");
      } 
      else {
      	echo "Error";
      }
    }

  }

  //Create new account
  if ($type == 5) {
  	$id = $_POST['id'];
  	$query = "SELECT * from Sender WHERE Sender.Sender_id = '$id'";
  	$result = $db->query($query);
    $rows = $result->num_rows;

    if ($rows != 0) {
    	echo "User Exists";
    	return;
    }
    else {
	  	$fname = $_POST['fname'];
	  	$lname = $_POST['lname'];
	  	$email = $_POST['email'];
	    $password = hash('sha256', rtrim($_POST['pass']));

	    $query = "INSERT into Sender VALUES ($id, '$fname $lname', '$email', '$password', NULL)";
  		$db->query($query);

	}

  }

  if ($type == 6) {

  	$id = $_POST['id'];
    //$email = $_POST['email'];

    $query = "SELECT * from Sender WHERE Sender.Sender_id = '$id'";
    $result = $db->query($query);
    $rows = $result->num_rows;

    # User exists
    if ($rows != 0) {
      $key = uniqid();
      $dataRow = $result->fetch_array();
      $email = $dataRow['Sender_email'];
      $query = "UPDATE Sender SET Reset='$key' WHERE Sender_id = '$id'";
      $result = $db->query($query);
      send_Reset($email, $id, $key);
    } 
    # User Login unsuccessful
    else {
      $query = "SELECT * from Admin WHERE Admin.Admin_id = '$id'";
      $result = $db->query($query);
      $rows = $result->num_rows;
      # Admin exists
      if ($rows != 0) {
	    $key = uniqid();
	    $dataRow = $result->fetch_array();
      	$email = $dataRow['Email'];
      	$query = "UPDATE Admin SET Reset='$key' WHERE Admin_id = '$id'";
        $result = $db->query($query);
      	send_Reset($email, $id, $key);
      } 
      else {
      	echo "No User Exists";
      }
    }
  }

function send_Reset($email, $id, $key) {

    $receiverEmail = $email;
    $receiverID = $id;

    $subject = "Ticket Administration - Password Reset";
    $message = "You requested to reset your password on the Ticket Administration Website.<br/><br/>
            Link to reset: http://localhost/assignment3/index.php?".$key;

    $mail = new PHPMailer();

    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->SMTPAuth = true; // enable SMTP authentication
    $mail->SMTPSecure = "tls"; // sets tls authentication
    $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server; or your email service
    $mail->Port = 587; // set the SMTP port for GMAIL server; or your email server port
    $mail->Username = "amm765@gmail.com"; // email username
    $mail->Password = "CharliE 7655"; // email password

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
	// $mail->Username = "uva.cs4501@gmail.com"; // email username
	// $mail->Password = "UVACSROCKS"; // email password
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


?>