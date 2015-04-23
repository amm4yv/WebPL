<?php

	session_start();

  function __autoload($class) {
    require_once $class . '.php';
  }

  # Use to toggle buttons
  if (!isset($_SESSION['buttons'])){
    $buttons[0] = 'View All Tickets';
    $buttons[1] = 'View My Tickets';
    $buttons[2] = 'View Unassigned Tickets';
    $_SESSION['buttons'] = $buttons;
  }

  # Use for the sort function to ensure sort of current view
  if (!isset($_SESSION['currentQuery'])) {
    $_SESSION['currentQuery'] = returnQuery() . " WHERE Tickets.Status = 'open'";
  }

  $db = new mysqli("localhost","Allison","charlie","Allison");
  if ($db->connect_error):
      die ("Could not connect to db " . $db->connect_error);
  endif;

  # The admin attempted login
  if (isset($_POST['id']) && isset($_POST['pass'])){

    $id = $_POST['id'];
    $password = hash('sha256', rtrim($_POST['pass']));

    $query = "SELECT * from Admin WHERE Admin.Admin_id = '$id' AND Admin.Password = '$password'";
    $result = $db->query($query);
    $rows = $result->num_rows;

    # Login successful
    if ($rows != 0) {
      $_SESSION['userID'] = $id;
      unset($_SESSION['error']);
    } 
    # Login unsuccessful
    else {
      $_SESSION['error'] = true;
      header("Location: admin.php");
    }

  }

  # The admin needs to login
  else if (!isset($_SESSION['userID'])) {
    login();
  }


  # The admin has requested an action
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $globalQuery = returnQuery();
    $currentQuery = $_SESSION['currentQuery'];

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
        $result = $db->query($globalQuery);
        showTable($result);
      }

    }

    if ($action == 'Sort') {
      
      if (isset($_POST['sort'])) {
        $key = $_POST['sort'];
        $query = $currentQuery . " ORDER BY $key";
        $result = $db->query($query);
      }

      # Admin did not select a "Sort By"; show table as normal
      else {
        $result = $db->query($currentQuery);
      }

      showTable($result);

    }

    if ($action == 'View Unassigned Tickets') {
      $buttons[0] = 'View All Tickets';
      $buttons[1] = 'View My Tickets';
      $buttons[2] = 'View Open Tickets';
      $_SESSION['buttons'] = $buttons;

      $query = $globalQuery . " WHERE (Tickets.Admin_id IS NULL OR Tickets.Admin_id = '')";
      $_SESSION['currentQuery'] = $query;
      $result = $db->query($query);
      showTable($result);

    }

    if ($action == 'View My Tickets') {
      $buttons[0] = 'View All Tickets';
      $buttons[1] = 'View Open Tickets';
      $buttons[2] = 'View Unassigned Tickets';
      $_SESSION['buttons'] = $buttons;

      $userID = $_SESSION['userID'];
      $query = $globalQuery . " WHERE Tickets.Admin_id = '$userID'";
      $_SESSION['currentQuery'] = $query;
      $result = $db->query($query);
      showTable($result);

    }

    if ($action == 'View All Tickets') {
      $buttons[0] = 'View Open Tickets';
      $buttons[1] = 'View My Tickets';
      $buttons[2] = 'View Unassigned Tickets';
      $_SESSION['buttons'] = $buttons;

      $result = $db->query($globalQuery);
      $_SESSION['currentQuery'] = $globalQuery;
      showTable($result);
    }

    if ($action == 'View Open Tickets') {
      $buttons[0] = 'View All Tickets';
      $buttons[1] = 'View My Tickets';
      $buttons[2] = 'View Unassigned Tickets';
      $_SESSION['buttons'] = $buttons;

      $query = $globalQuery . " WHERE Tickets.Status = 'open'";
      $_SESSION['currentQuery'] = $query;
      $result = $db->query($query);
      showTable($result);
    }

    if ($action == 'Logout') {
      session_destroy();
      header("Location: admin.php");
    }  

  }

    # The admin is already logged in but hasn't taken an action
  else if (isset($_SESSION['userID'])) {
    $query = returnQuery() . " WHERE Tickets.Status = 'open'";
    $_SESSION['currentQuery'] = $query;
    $result = $db->query($query);
    showTable($result);
  }


?>

<html>
<head>
	<title>Ticket System Admin Panel</title>
  <link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<body>
  <section class="container">
<?php
# Show data that has been inserted into the database
echo"<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
    <br/><br/>
    <br/>";
      $tables = array("Sender"=>array("Sender_id", "Sender_name", "Sender_email"),
                      "Admin"=>array("Admin_id", "Name", "Email", "Password"));
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


function login(){ ?>
<header>
    <br/>
    <h1>Ticket Administration</h1>
  </header>
<section class="logincontainer">
<div class="login">
  <h1>Admin Login</h1>
<form method="post" action="admin.php">
  <?php if (isset($_SESSION['error'])) echo "<p style='color:RED'>Error. Please try again.</p>"; ?>
  <p><input type="text" name="id" value="" placeholder="ID"></p>
  <p><input type="password" name="pass" value="" placeholder="Password"></p>
  <p class="submit"><input type="submit" name="submit" value="Login"></p>
</form>
</div>
<div class="login-help">
      <p>Forgot your password? <a href="passreset.php">Click here to reset it</a>.</p>
    </div>
</section>
<?php } ?>
</section>
</body>
</html>

<?php

  function showTable($data){

    ?>
    <header>
    <br/>
    <h1>Ticket Administration</h1>
    <p>You are logged in with administrator ID <?php $id = $_SESSION['userID']; echo "$id"; ?></p>
  </header>
    <?php

    //echo "$data";
    if ($data == NULL) {
      echo "No tickets match that query. <br/>
      <form action = 'admin.php' method = 'POST'>";
      $buttons = $_SESSION['buttons'];
      echo "<input type='submit' name='action' value='View All Tickets'>
            <input type='submit' name='action' value='View Open Tickets'>
            <input type='submit' name='action' value='Sort'> 
            <input type='submit' name='action' value='View Selected Ticket'><br/>
            <input type='submit' name='action' value='View My Tickets'>
            <input type='submit' name='action' value='Logout'>
            <input type='submit' name='action' value='View Unassigned Tickets'>


      </form>";
      return;
    }

    $actualKeys = array("Ticket_num", "Received", "Sender_name", "Sender_email", "Subject", "Name", "Status");
    $keys = array("Ticket #", "Received", "Sender Name", "Sender Email", "Subject", "Tech", "Status");
    
    $rows = $data->num_rows;

    echo "<form action = 'admin.php' method = 'POST'> 
          <table class='center'>
          <tr align = 'center'>";

         foreach ($keys as $next_key):
             echo "<th>$next_key</th>";
         endforeach;
         echo "<th>Select</th>";
         echo "</tr>"; 
         for ($i = 0; $i < $rows; $i++):
             echo "<tr align = 'center'>";
             $row = $data->fetch_array();
             foreach ($keys as $next_key)
             {
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

         $buttons = $_SESSION['buttons'];

         echo "
          <table class='center' style='border:0;'>
          <tr>
          <td><input type='submit' name='action' value='$buttons[0]'></td>
          <td><input type='submit' name='action' value='Sort'></td>
          <td><input type='submit' name='action' value='View Selected Ticket'></td>
          </tr>
          <tr>
          <td><input type='submit' name='action' value='$buttons[1]'></td>
          <td><input type='submit' name='action' value='Logout'></td>
          <td><input type='submit' name='action' value='$buttons[2]'></td>
          </tr>
          </table>

          

      </form>";

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
