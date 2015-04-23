<?php

  session_start();
  date_default_timezone_set('America/New_York');

  if (isset($_SESSION['questions']))
    unset($_SESSION['questions']);
    

  if (isset($_POST['newUser'])){
    $newUser = $_POST['newUser'];
    $pass1 = $_POST['newPass1'];
    $pass2 = $_POST['newPass2'];

    $save = true;

    if (strcmp($pass1, $pass2) !== 0){
      $save = false;
      $_SESSION['error'] = "passwordMatch";
      header("Location: newuser.php");
    }

    $fileptr = fopen("users.txt", "r");
    if (flock($fileptr, LOCK_SH)) {        
        while ($curruser = fgetss($fileptr)){
          $data = explode("#", trim($curruser));
          if (strcasecmp($newUser, $data[0]) == 0 || strcmp($pass1, "") == 0 || strcmp($newUser, "") == 0){
            $save = false;
            $_SESSION['error'] = "userExists";
            header("Location: newuser.php");            
          }
        }
      }       
    flock($fileptr, LOCK_UN);
    fclose($fileptr);

    if ($save){
      $fileptr = fopen("users.txt", "a");
      if (flock($fileptr, LOCK_SH)) {
        $userLine = "\n" . $newUser . "#" . $pass1;
        fwrite($fileptr, $userLine);
      }
      flock($fileptr, LOCK_UN);
      fclose($fileptr);
    }

  }


  if (isset($_POST['userReset'])) {
    $user = $_POST['userReset'];
    $newPass1 = $_POST['newPass1'];
    $newPass2 = $_POST['newPass2'];

    $save = false;
    $_SESSION['resetError'] = "userExists";

    if (strcmp($newPass1, $newPass2) !== 0){
      $save = false;
      $_SESSION['resetError'] = "passwordMatch";
      header("Location: passreset.php");
    }
    
    else {
      $fileptr = fopen("users.txt", "r");
        if (flock($fileptr, LOCK_SH)) {  
          $ctr = 0;      
          while ($curruser = fgetss($fileptr)){
            $data = explode("#", trim($curruser));
            if (strcasecmp($user, $data[0]) == 0){
              $save = true;
              $data[0] = $user;
              $data[1] = $newPass1;
            }
            $users[$ctr] = $data;
            $ctr++;
          }
        }  

      flock($fileptr, LOCK_UN);
      fclose($fileptr);

      if ($save){
        $fileptr = fopen("users.txt", "w");
        if (flock($fileptr, LOCK_SH)) {
          for ($i=0; $i < $ctr; $i++) { 
            $array = $users[$i];
            $line = $array[0] . "#" . $array[1];
            if ($i !== $ctr-1) $line = $line . "\n";
            fwrite($fileptr, $line);   
          }
        }
        flock($fileptr, LOCK_UN);
        fclose($fileptr);
      }
      else
        header("Location: passreset.php");
    }

  }

  if (isset($_COOKIE['user'])){
    $user = $_COOKIE['user'];
    $pass = $_COOKIE['pass'];
    if (!isset($_COOKIE["quiz"]))
      $takenQuiz = false;
    else{
      $quiz = unserialize($_COOKIE["quiz"]);
      if (isset($quiz["$user"]))
        $takenQuiz = $quiz["$user"];
      else
        $takenQuiz = false;
    }

    $_SESSION['user'] = $user;
    $_SESSION['pass'] = $pass;
    $_SESSION['quiz'] = $takenQuiz;

    show_header();
    welcome();
    show_end();


    // show_header();
    // //echo "<div class='center'>";
    // echo "Welcome Back $user! <a href='changeuser.php'>Logout</a><br/><br/>";

    // if (!$takenQuiz) 
    //   echo "<a href='quiz.php'>Click here to take the Quiz of the Day!</a>";
    // else 
    //   echo "Sorry, you can only take the Quiz of the Day once per day. Come back tomorrow!";

    // //echo "</div>";
    // show_end();

  }
  elseif (isset($_POST['user'])){

    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if(isset($_POST['cookie']))
      $cookie = true;
    else
      $cookie = false;

    if (!isset($_COOKIE["quiz"]))
      $takenQuiz = false;
    else{
      $quiz = unserialize($_COOKIE["quiz"]);
      if (isset($quiz["$user"]))
        $takenQuiz = $quiz["$user"];
      else
        $takenQuiz = false;
    }

    if ($cookie){
      setcookie("user", "$user", time()+600);
      setcookie("pass", "$pass", time()+600);
    //setcookie("quiz", "$takenQuiz", time()+600);
  }

    $_SESSION['user'] = $user;
    $_SESSION['pass'] = $pass;
    $_SESSION['quiz'] = $takenQuiz;

    show_header();
    welcome();
    show_end();

  }
  else{
    show_header();
    login(false);
    show_end();
  }
    


function show_end(){
  echo "</body>";
  echo "</html>";
}

function show_header(){
?>
<!DOCTYPE html>
<html>
<head>
<title>Quiz of the Day</title>

<link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<body>
  <div id="main">
  <header>
    <h1>Quiz of the Day</h1>
  </header>
<?php
}
   
function welcome(){

    //$info = $_SESSION['info'];
    $username = $_SESSION['user'];
    $password = $_SESSION['pass'];
    $takenQuiz = $_SESSION['quiz'];


    $fileptr = fopen("users.txt", "r");
    if (flock($fileptr, LOCK_SH)) { 
        $ctr=0;
        
        while ($curruser = fgetss($fileptr)){
          $data = explode("#", trim($curruser));
          if (strcasecmp($username, $data[0]) == 0){
            $ctr++;
            if (strcmp($password, $data[1]) == 0){
              echo "<section class='container'>
                    <div class='login' style='text-align: center; width: 450px;'>
                    <h1>Welcome $username! (<a href='changeuser.php'>not $username?</a>)</h1>";
              if (!$takenQuiz) 
                echo "<a href='quiz.php'>Click here to take the Quiz of the Day!</a>";
              else 
                echo "Sorry, you can only take the Quiz of the Day once per day. Come back tomorrow!";
              echo "</div>
                    </section>";
              break;
            } else {
              login(true);
              //echo "Incorrect password. <a href='index.php'>Please try again.</a>";
              unset($_COOKIE['user']);
              unset($_COOKIE['pass']);
              setcookie('user', '', time() - 3600);
              setcookie('pass', '', time() - 3600);
              //setcookie('takenQuiz', '', time() - 3600);
              session_destroy();              
              break;
            }
          }
        }

        if ($ctr === 0){
          login(true);
          //echo "No user exists. <a href='index.php'>Please try again.</a>";
          unset($_COOKIE['user']);
          unset($_COOKIE['pass']);
          setcookie('user', '', time() - 3600);
          setcookie('pass', '', time() - 3600);
          //setcookie('takenQuiz', '', time() - 3600);
          session_destroy();  
          
        }
      }       

      flock($fileptr, LOCK_UN);
      fclose($fileptr);
}



function login($tryAgain){
?>
  <!-- <div id="wrapper">
    <form action="index.php" method="POST">
    <h2>Login</h2>

    <div id="username_input">
          
      <div id="username_inputmiddle">
        <input type="text" name="user" id="url" value="Username">
      </div>
    </div>    
    <div id="password_input">
      <div id="password_inputmiddle">
        <input type="password" name="pass" id="url" value="Password" >
      </div>
        <b>Keep me logged in</b> <input type="checkbox" name="cookie"><br/>
    </div>
    <br/>
    <input type="image" src="submit.png" id="submit" value="Sign In"><br/>
    
    <div id="links_left"><a href="#">Forgot your Password?</a></div>
    <div id="links_right"><a href='newuser.php'>Not a Member Yet?</a></div>

    </form>
  </div>

  </div>
</div> -->

<section class="container">
    <div class="login">
      <h1>Login to Quiz of the Day</h1>
      <?php
      if ($tryAgain) echo "<p style='color:RED;'>Invalid username or password. Please try again.</p>";
    ?>
      <form method="post" action="index.php">
        <p><input type="text" name="user" value="" placeholder="Username"></p>
        <p><input type="password" name="pass" value="" placeholder="Password"></p>
        <p class="remember_me">
          <label>
            <input type="checkbox" name="cookie" id="remember_me">
            Remember me on this computer
          </label>
        </p>
        <p class="submit"><input type="submit" name="submit" value="Login"></p>
      </form>
    </div>

    <div class="login-help">
      <p>Forgot your password? <a href="passreset.php">Click here to reset it</a>.</p>
      <p>Not a user? <a href="newuser.php">Click here to create a username</a>.</p>
    </div>
  </section>


<?php
}
