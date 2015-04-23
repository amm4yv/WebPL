<?php

	// Open database
	$db	= new mysqli("localhost","Allison","charlie","Allison");
	if ($db->connect_error):
    	die ("Could not connect to db " . $db->connect_error);
    endif;

    // Load admin data
    $fileptr = fopen("words.txt", "r");
    if (flock($fileptr, LOCK_SH)) {         
      while ($line = fgetss($fileptr)){
        $word = trim($line);

        $insertQuery = "INSERT into Words VALUES ('$word')";
        $db->query($insertQuery);
      }
    }       

    flock($fileptr, LOCK_UN);
    fclose($fileptr);

    // Redirect to index
    header("Location: game.html");

?>