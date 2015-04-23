<?php

	session_start();

	$db	= new mysqli("localhost","Allison","charlie","Allison");
	if ($db->connect_error):
		die ("Could not connect to db " . $db->connect_error);
	endif;

	$query = "SELECT word FROM Words ORDER BY RAND() LIMIT 1";
	$result = $db->query($query);
	$array = $result->fetch_array();
	
	header('Content-type: text/xml');
	echo "<?xml version='1.0' encoding='utf-8'?>";
	echo "<value>$array[0]</value>";


?>