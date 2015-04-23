<?php

	session_start();

	function __autoload($class) {
		require_once $class . '.php';
   	}

   	set_include_path('/Applications/XAMPP/xamppfiles/PHPMailer/');
	require 'PHPMailerAutoload.php';

	#The user successfully logged in
	if (isset($_SESSION['blankField']))
		unset($_SESSION['blankField']);

	#Check for form submission
	if ((isset($_POST['fname']) && isset($_POST['lname']) && isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['problem']))){

		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$email = $_POST['email'];
		$subject = $_POST['subject'];
		$problem = $_POST['problem'];

		#Check for blank fields in form submission
		if (strcmp($fname, "") == 0 || strcmp($lname, "") == 0 || strcmp($email, "") == 0 || strcmp($subject, "") == 0 || strcmp($problem, "") == 0) {

			#Return to index
			$_SESSION['blankField'] = true;
     		header("Location: index.php");

     	}

     	else {
     		unset($_SESSION['blankField']);

     		
     		#Send Email confirmation
     		$info = [$email, $subject, $problem, false];
    		send_Mail($info);

    		$db	= new mysqli("localhost","Allison","charlie","Allison");
			if ($db->connect_error):
    			die ("Could not connect to db " . $db->connect_error);
    		endif;

    		# Get all admin email addresses and send to admin
    		$info[3] = true;
    		$getAdminEmail = $db->query("SELECT Admin.Email from Admin");
    		$numEmails = $getAdminEmail->num_rows;
    		echo "$numEmails";
    		for($i = 0; $i < $numEmails; $i++) {
    			$adminEmail = $getAdminEmail->fetch_array();
				$info[0] = $adminEmail[0];
				send_Mail($info);
			}
    		

			#Check to see if sender exists in database already###
			$getSender = $db->query("SELECT * from Sender WHERE Sender.Sender_name = '$fname $lname' and Sender.Sender_email = '$email'");
			$matches = $getSender->num_rows;

			if ($matches == 0) {
				$senderTable = $db->query("SELECT * from Sender");
				$senders = $senderTable->num_rows;
				$senderID = ($senders+1);
			}

			else {
				$senderRow = $getSender->fetch_array();
				$senderID = $senderRow['Sender_id'];
			}
			######################################################
			
			//$ticketsTable = $db->query("SELECT * from Tickets ORDER BY Ticket_num");
			//$ticketRows = $ticketsTable->num_rows;

			$ticketResult = $db->query("SELECT MAX(Ticket_num) AS max_id FROM Tickets");
			$row = $ticketResult->fetch_array();
			$ticketNum = $row["max_id"] + 1;

			//$date = CURRENT_TIMESTAMP();
			$created_date = date("Y-m-d H:i:s");

			#Create a Ticket object
    		//$newTicket = new Ticket($ticketNum, $created_date, "$fname $lname", $email, $subject, $problem);


			//$empty = "";
			//$time = date('M d Y h:iA');

			# --- Update Database ----------------- #
			$ticketQuery = "INSERT into Tickets VALUES ($ticketNum, '$created_date', $senderID, '$subject', NULL, 'open', '$problem')";

			$db->query($ticketQuery);

			$senderQuery = "INSERT into Sender VALUES ('$senderID', '$fname $lname', '$email')";

			$db->query($senderQuery);
			# ------------------------------------ #


     	}
	}

?>

<html>
<head>
	<title>Ticket Request Confirmation</title>
	<link type="text/css" rel="stylesheet" href="style.css" media="screen" />

</head>
<body>
<section class="logincontainer">
<div class="login" style="text-align:left;">
	<h1>Ticket Request Confirmation</h1>
	<p>Thanks for submitting the request! <a href="index.php">Click to submit another</a></p>
	<p><a href="admin.php">Go to admin</a></p>
</div>
</section>
</body>
</html>

<?php

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
	$mail->Username = "uva.cs4501@gmail.com"; // email username
	$mail->Password = "UVACSROCKS"; // email password

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