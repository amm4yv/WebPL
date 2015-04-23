<?php

	$db = new mysqli("localhost","Allison","charlie","Allison");
  if ($db->connect_error):
      die ("Could not connect to db " . $db->connect_error);
  endif;

?>

<html>
<head>
	<title></title>
</head>
<body>
	<?php
# Show data that has been inserted into the database
      $tables = array("Tickets"=>array("Ticket_num", "Received", "Sender_id", "Subject", "Admin_id", "Status", "Message"), 
      				  "Sender"=>array("Sender_id", "Sender_name", "Sender_email", "Password", "Reset"),
                      "Admin"=>array("Admin_id", "Name", "Email", "Password", "Reset"));
      foreach ($tables as $curr_table=>$curr_keys):
         $query = "select * from " . $curr_table;
         $result = $db->query($query);
         $rows = $result->num_rows;
         $keys = $curr_keys;
?>
      <table class="center">
      <caption><?php echo $curr_table;?></caption>
      <tr align = "center">
<?php
         foreach ($keys as $next_key):
             echo "<th>$next_key</th>";
         endforeach;
         echo "</tr>"; 
         for ($i = 0; $i < $rows; $i++):
             echo "<tr align = 'center'>";
             $row = $result->fetch_array();
             foreach ($keys as $next_key)
             {
                  echo "<td> $row[$next_key] </td>";
             }
             echo "</tr>";
         endfor;
         echo "<tr>";
         echo "</tr></table><br />";
      endforeach;
?>
</body>
</html>