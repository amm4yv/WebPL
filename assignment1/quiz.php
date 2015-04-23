<?php
  session_start();
  date_default_timezone_set('America/New_York');

  if (!isset($_COOKIE['user']) && !isset($_SESSION['user'])){
    header("Location: index.php");
  }
  else{
    if (isset($_COOKIE['user']))
      $user = $_COOKIE['user'];
    if (isset($_SESSION['user']))
      $user = $_SESSION['user'];

    if (isset($_COOKIE['quiz'])){
      $quizzes = unserialize($_COOKIE['quiz']);
      if (isset($quizzes["$user"]) && !isset($_SESSION['questions'])){
          header("Location: index.php");
      }

      $takenQuiz = $quizzes;
      $clearInfo = false;

    } else {
      $clearInfo = true;
    }
    

    $takenQuiz["$user"] = true;
    $quiz = serialize($takenQuiz);

    //setcookie('cons', 1, strtotime('today 23:59'), '/')
    setcookie("quiz", "$quiz", strtotime('today 23:59:59'));

    $fileName = "";

    if (isset($_SESSION['questions'])){
      $questions = $_SESSION['questions'];
      if (!isset($_SESSION['numCorrect'])){
        $numCorrect = 0;
        $_SESSION['numCorrect'] = $numCorrect;
      }
      else
        $numCorrect = $_SESSION['numCorrect'];
    }
    else{
      $fileptr = fopen("quizzes.txt", "r");
      if (flock($fileptr, LOCK_SH)) { 
        $ctr=0;
        
        while ($line = fgetss($fileptr)){
          $data = explode("#", trim($line));
          $quizzes[$ctr] = $data;

          $ctr++;

        }
      }       

      //echo "$ctr<br/>";

      flock($fileptr, LOCK_UN);
      fclose($fileptr);

      $date = getdate();
      mt_srand ($date['yday']);
      $quizNumber = mt_rand(1, $ctr);

      $_SESSION['quizNumber'] = $quizNumber;

     //print_r($quizzes);

     $quizLine = $quizzes[$quizNumber - 1];
     $fileName = $quizLine[0];
     
     if ($clearInfo){
       $quizLine[2] = 0;
       $quizLine[3] = 0;
       $quizLine[4] = 0;
       $quizzes[$quizNumber - 1] = $quizLine;

       $fileptr = fopen("quizzes.txt", "w");
        if (flock($fileptr, LOCK_SH)) {
          for ($i=0; $i < $ctr; $i++) { 
            $array = $quizzes[$i];
            $line = $array[0] . "#" . $array[1] . "#" . $array[2] . "#" . $array[3] . "#" . $array[4];
            if ($i !== $ctr-1) $line = $line . "\n";
            fwrite($fileptr, $line);   
          }
        }
        flock($fileptr, LOCK_UN);
        fclose($fileptr);
    }


     $_SESSION['quizLine'] = $quizLine;

     $fileptr = fopen($fileName, "r");
      if (flock($fileptr, LOCK_SH)) { 
        $ctr=0;
        
        while ($line = fgetss($fileptr)){
          $data = explode("#", trim($line));
          $questions[$ctr] = $data;

          $ctr++;

        }
      }       

      flock($fileptr, LOCK_UN);
      fclose($fileptr);

      if (strcmp($fileName, 'quiz1.txt') == 0) $title = ": Harry Potter";
      elseif (strcmp($fileName, 'quiz2.txt') == 0) $title = ": Star Wars";
      elseif (strcmp($fileName, 'quiz3.txt') == 0) $title = ": Impossible Quiz";
      else $title = "";

      $questions['title'] = $title;

      $_SESSION['questions'] = $questions;

    }

    if (!isset($_SESSION['index']))
      $_SESSION['index'] = 0;
    
    $index = $_SESSION['index'];


    if (isset($_POST['ans'])){
      $val = $_POST['ans'];

      if ($index < (count($questions)-2) && $index !== -1){
          $index += 1;
          $_SESSION['index'] = $index;
        } 
        else 
          $index = -1;

      if ($index !== -1){
        $_SESSION['qLine'] = $questions[$index-1];
        $questionLine = $_SESSION['qLine'];
        $answers = $_SESSION['answers'];
        $correctAnswer = $answers[$questionLine[2]];

        if ($val == $questionLine[2]){

          $numCorrect += 1;
          $_SESSION['numCorrect'] = $numCorrect;
          $_SESSION['message'] = "<div class='end' style='margin-top:10px; text-align:center;'>
                Correct! Good job!<br/> 
                Stats so far: <br/>
                $numCorrect/" . $index . " questions answered correctly<br/><br/>
                </div>";
        }

        else
           $_SESSION['message'] = "<div class='end' style='margin-top:10px; text-align:center;'>
                Incorrect! The answer was $correctAnswer<br/> 
                Stats so far: <br/>
                $numCorrect/" . $index . " questions answered correctly<br/><br/>
                </div>";

      }
      else{
        $questionLine = $_SESSION['qLine'];
        if ($val == $questionLine[2]){
          $numCorrect += 1;
          $_SESSION['numCorrect'] = $numCorrect;
        }
      }

       
      
    }
  }

?>
<html>
<head>
   <title>Quiz of the Day</title>
<link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<body>

<?php

  if ($index == -1){

    $qNumber = $_SESSION['quizNumber'];

    $fileptr = fopen("quizzes.txt", "r");
      if (flock($fileptr, LOCK_SH)) { 
        $ctr=1;
        
        while ($line = fgetss($fileptr)){
          $data = explode("#", trim($line));

          if ($ctr == $qNumber){
            $data[2] = $data[2] + 1;
            $takers = $data[2];
            $data[3] = $data[3] + $numCorrect;
            $totalCorrect = $data[3];
            $data[4] = $data[4] + ((count($questions) - 1) - $numCorrect);
            $totalIncorrect = $data[4];
          }

          $quizzes[$ctr] = $data;

          $ctr++;

        }
      }     

      flock($fileptr, LOCK_UN);
      fclose($fileptr);

      $fileptr = fopen("quizzes.txt", "w");
      if (flock($fileptr, LOCK_SH)) {
        for ($i=1; $i < $ctr; $i++) { 
          $array = $quizzes[$i];
          $line = $array[0] . "#" . $array[1] . "#" . $array[2] . "#" . $array[3] . "#" . $array[4];
          if ($i !== $ctr-1) $line = $line . "\n";
          fwrite($fileptr, $line);   
        }
      }
      flock($fileptr, LOCK_UN);
      fclose($fileptr);

    $title = $questions['title'];

    echo "<header><h1 style='text-align:center'>Quiz of the Day$title</h1></header>";

    echo "<section class='container'>
                    <div class='login' style='width:450px; text-align:center;'>";
    echo "<h1>Results</h1>Thanks for taking the quiz! Here are you results:<br/><br/>
          $numCorrect/" . (count($questions) - 1) . " answered correctly <br/>";

    $percent = (int) (100 * ($numCorrect / (count($questions) - 1)));

    echo "$percent% Score<br/>";

    echo "<br/><a href=index.php><input type='submit' value='Go Home'></a><br/>";

    

    echo "<br/><br/><h1>Class Wide Results</h1>";


    echo "$takers people have taken this quiz. <br/>
          Total correctly answered questions: $totalCorrect <br/>";
    //echo " $totalCorrect/" . (count($questions) - 1) . " answered correctly <br/>";

    $percent = (int) (100 * ($totalCorrect / ($totalCorrect+$totalIncorrect)));

    echo "Average $percent% Score<br/>";

    echo "</div>
          </section>";
    session_destroy(); 

  }  

  else{

    $title = $questions['title'];

   echo "<header><h1 style='text-align:center'>Quiz of the Day$title</h1></header>";
   
   $questionLine = $questions[$index];
   $_SESSION['qLine'] = $questionLine;

    //print_r($questions);

    $answers = explode(":", trim($questionLine[1]));
    $_SESSION['answers'] = $answers;
    $number = $index + 1;
    echo "<section class='container'>
                    <div class='login' style='width:450px;'>";
    echo "<h1>$number) $questionLine[0]</h1><form action='quiz.php' method='POST'>";
    echo "<div style='padding-left:100px; padding-right:100px;'>";
    for ($i = 0; $i < count($answers); $i++):
      echo "<input type='radio' name='ans' value='$i'>";
      echo " $answers[$i]<br/>";
    endfor;
    echo "</div>";
    echo "<br/><div style='text-align:center';><input type='submit'></div>";
    if (isset($_SESSION['message'])){
      $output = $_SESSION['message'];
      echo "$output";
    }
    echo "</form>
          </div>
          </section>";

  }

?>
</div>
</body>
</html>  