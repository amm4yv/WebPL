<!DOCTYPE html>
<html>
<head>
	<title>Welcome</title>
      <meta http-equiv="Refresh" content="0; url=index.php">
</head>
<body>
	<?php

      session_start();
      unset($_COOKIE['user']);
      unset($_COOKIE['pass']);
      setcookie('user', '', time() - 3600);
      setcookie('pass', '', time() - 3600);
      session_destroy(); 

	?>

</body>
</html>