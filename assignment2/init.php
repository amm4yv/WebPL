<?php

	// $servername = "localhost";
	// $username = "root";
	// $password = "";

	// // Create connection
	// $conn = new mysqli($servername, $username, $password);
	// // Check connection
	// if ($conn->connect_error) {
	//     die("Connection failed: " . $conn->connect_error);
	// } 

	// $conn->query("DROP DATABASE Allison");

	// // Create database
	// $sql = "CREATE DATABASE Allison";
	// if ($conn->query($sql) === TRUE) {
	//     echo "Database created successfully";
	// } else {
	//     echo "Error creating database: " . $conn->error;
	// }

	// $sql = "GRANT ALL on Allison.* to Allison@localhost IDENTIFIED by 'charlie'";
	// $conn->query($sql);
	// $sql = "USE Allison";
	// $conn->query($sql);

	// $conn->close();


	// Open database
	$db	= new mysqli("localhost","Allison","charlie","Allison");
	if ($db->connect_error):
    	die ("Could not connect to db " . $db->connect_error);
    endif;

    // Load admin data
    $fileptr = fopen("Admin.flat", "r");
    if (flock($fileptr, LOCK_SH)) {         
        while ($line = fgetss($fileptr)){
          $data = explode(";", trim($line));

          $password = hash('sha256', rtrim($data[3]));

          $insertQuery = "INSERT into Admin VALUES ('$data[0]', '$data[1]', '$data[2]', '$password')";
          $db->query($insertQuery);
        }
      }       

    flock($fileptr, LOCK_UN);
    fclose($fileptr);

    // Load tickets data
    $fileptr = fopen("Tickets.flat", "r");
    if (flock($fileptr, LOCK_SH)) {         
        while ($line = fgetss($fileptr)){
          $data = explode(";", trim($line));

          $senderQuery = "INSERT into Sender VALUES ($data[1], '$data[2]', '$data[3]')";
          $db->query($senderQuery);

          $created_date = date("Y-m-d H:i:s");

          $ticketQuery = "INSERT into Tickets VALUES ($data[0], '$created_date', $data[1], '$data[4]', $data[5], '$data[6]', '$data[7]')";
          $result = $db->query($ticketQuery);
        }
      }       

    flock($fileptr, LOCK_UN);
    fclose($fileptr);


    // Redirect to index
    header("Location: index.php");

?>