<?php
	session_start();

  if($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  }
?>

<html>
<head>
	<title>Ticket Request</title>
	<link type="text/css" rel="stylesheet" href="style.css" media="screen" />
</head>
<script type="text/javascript">
	function validateForm() {
		var fields = ["fname", "lname", "email", "subject", "problem"];
		for (var i = 0; i < fields.length; i++) {
			var value = document.forms["login"][fields[i]].value;
		    if (value == null || value == "") {
		        alert("All fields must be filled out");
		        return false;
		    }
    	}
	}

	function processData() {
		var httpRequest;
		var type = arguments[0];

		document.body.style.cursor = "process";

		//alert(type);

		if (window.XMLHttpRequest) {
  			httpRequest=new XMLHttpRequest();
  		}

  		//Initialize Page
  		if (type == 0){
  			data = 'type=' + type;
  		}

  		//Log out
  		else if (type == 9){
  			data = 'type=' + type;
  		}

  		//Log in
  		else if (type == 1) {
  			var id = arguments[1];
	        var password = arguments[2];
	        data = 'type=' + type + '&id=' + id + '&pass=' + password; 
  			//alert(data);
  		}

  		//Submit ticket
  		else if (type == 2){
	        var subject = arguments[1];
	        var problem = arguments[2];
	        data = 'type=' + type + '&subject=' + subject + '&problem=' + problem; 
  			//alert(data);
  		}

  		//View tickets
  		else if (type == 3){
  			data = 'type=' + type;
  		}

  		//Change Password
  		else if (type == 4){
  			var id = arguments[1];
	        var password = arguments[2];
	        var reset = arguments[3];
	        data = 'type=' + type + '&id=' + id + '&pass=' + password + "&reset=" + reset;
  		}

  		//Create new account
  		else if (type == 5) {
  			var id = arguments[1];
	        var fname = arguments[2];
	        var lname = arguments[3];
	        var email = arguments[4];
	        var password = arguments[5];
  			data = 'type=' + type + '&id=' + id + '&fname=' + fname + '&lname=' + lname + '&email=' + email + '&pass=' + password;
  		}

  		//Reset password
  		else if (type == 6) {
  			var id = arguments[1];
  			var email = arguments[2];
  			data = 'type=' + type + '&id=' + id + '&email=' + email;
  		}
		
		
		httpRequest.onreadystatechange=function() {
	  		if (httpRequest.readyState==4 && httpRequest.status==200) {
	  			var response = httpRequest.responseText; 
	  			document.body.style.cursor = "default";
	  			if (type == 0) {
	    			//alert(response);
	    			if (response == "Error") {
	    				showLoginForm();
	    			} else if (response == "User") {
	    				showHomepage();
	    			} else if (response == "Admin") {
	    				window.location="admin.php";
	    			} else {
	    				alert("Error");
	    			}
	  			}
	  			if (type == 9) {
	  				showLoginForm();
	  			}
				if (type == 1) {    			
    				//alert(response);
	    			if (response == "User") {
	    				document.getElementById('userExistHelp').innerHTML = "";
						document.getElementById('forgotPassword').innerHTML = "";
	    				showHomepage();
	    			} else if (response == "Admin") {
	    				//alert("Admin");
	    				window.location="admin.php";
	    			} else {
	    				alert("Error");
	    			}
	    		}
	    		if (type == 2) {
					alert("Successfully processed data.");
					showHomepage();
	  			}

	  			if (type == 3) {
					//alert(response);
					showMyTickets(response);
	  			}
	  			//Change Password
	  			if (type == 4) {
	  				if (response == "Error") {
	    				alert("User does not exist");
	    				showChangePassword();
	    			} 
	    			else if (response == "Invalid Reset") {
	    				alert("Invalid Reset User");
	    				showChangePassword();
	    			}
	    			else if (response == "User") {
	    				alert("Successfully set new password!");
	    				showHomepage();
	    			} else if (response == "Admin") {
	    				alert("Successfully set new password!");
	    				window.location="admin.php";
	    			} else {
	    				alert("Error");
	    				showChangePassword();
	    			}
	  			}
	  			if (type == 5) {
	  				if (response == "User Exists"){
	  					alert("User already exists");
	  					showCreateAccount();
	  				}
	  				else {
	  					alert("Successfully created account!");
	  					showLoginForm();
	  				}
	  			}

	  			if (type == 6) {
	  				if (response == "No User Exists"){
	  					alert("User does not exist.");
	  					showPasswordReset();
	  				}
	  				else {
	  					alert(response);
	  					showLoginForm();
	  				}
	  			}

	  		}
	  	}

		httpRequest.open("POST","process.php",true);
		httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		httpRequest.send(data);
	}


	function processNewTicket() {
        var subject = document.theForm.subject.value;
        var problem = document.theForm.problem.value;

    	if (subject == ""){
            alert("Please enter a subject for your request.");
            document.theForm.subject.focus();
            return false;
    	}
    	if (problem == ""){
            alert("Please enter your request.");
            document.theForm.problem.focus();
            return false;
    	}

        document.theForm.subject.value = "";
        document.theForm.problem.value = "";

        processData(2, subject, problem);

        //showHomepage();
        
        
        
	}

	function processLogin() {
		var id = document.theForm.userId.value;
        var password = document.theForm.pass.value;

        //alert(id + password);

        if (id == ""){
            alert("Please enter your user ID.");
            document.theForm.userId.focus();
            return false;
    	}
        if (password == ""){
            alert("Please enter your password.");
            document.theForm.pass.focus();
            return false;
    	}

    	processData(1, id, password);

	}

	function processNewAccount() {
		var id = document.theForm.userId.value;
		var fname = document.theForm.fname.value;
		var lname = document.theForm.lname.value;
		var email = document.theForm.email.value;
        var password = document.theForm.pass.value;
        var password2 = document.theForm.pass2.value;

        //alert(id + password);

        if (id == ""){
            alert("Please enter a user ID.");
            document.theForm.userId.focus();
            return false;
    	}
        if (id !== parseInt(id, 10)) {
            alert("Your user ID must be an integer.");
            document.theForm.userId.focus();
            return false;
        }
    	if (fname == ""){
            alert("Please enter your first name.");
            document.theForm.fname.focus();
            return false;
    	}
    	if (lname == ""){
            alert("Please enter your last name.");
            document.theForm.lname.focus();
            return false;
    	}
    	if (email == ""){
            alert("Please enter your email.");
            document.theForm.email.focus();
            return false;
    	}
        if (password == ""){
            alert("Please enter a password.");
            document.theForm.pass.focus();
            return false;
    	}
    	if (password2 == ""){
            alert("Please confirm your password.");
            document.theForm.pass2.focus();
            return false;
    	}
    	if (password != password2){
    		alert("Passwords do not match.");
            document.theForm.pass2.focus();
            return false;
    	}

    	processData(5, id, fname, lname, email, password);

	}

	function processNewPassword() {
		var id = document.theForm.userId.value;
        var password = document.theForm.pass.value;
        var password2 = document.theForm.pass2.value;

        //alert(id + password);

        if (id == ""){
            alert("Please enter your user ID.");
            document.theForm.userId.focus();
            return false;
    	}
        if (password == ""){
            alert("Please enter a new password.");
            document.theForm.pass.focus();
            return false;
    	}
    	if (password2 == ""){
            alert("Please re-enter your new password.");
            document.theForm.pass2.focus();
            return false;
    	}
    	if (password != password2){
    		alert("Passwords do not match.");
            document.theForm.pass2.focus();
            return false;
    	}

    	var reset = "";
    	if (arguments[0] != null)
    		reset = arguments[0];

    	processData(4, id, password, reset);

	}

	function processPasswordReset() {
		var id = document.theForm.userId.value;

		if (id == ""){
            alert("Please enter your user ID.");
            document.theForm.userId.focus();
            return false;
    	}

    	processData(6, id);

	}

	function showMyTickets(response) {
		if (response == "No Tickets" || response == "") {
		    alert("You have not submitted any tickets.");
		    showHomepage();
        }
        else {
       	
        	document.getElementById("title").innerHTML = "Your Tickets";
        	var container = document.getElementById("login");
        	container.setAttribute("style", "width: 500px;");
        	var form = document.theForm;
        	form.innerHTML = "";

        	var div = document.createElement("div");
			div.setAttribute("style", "text-align: left;");
			var back = document.createElement("a");
			back.setAttribute("href", "#");
			back.setAttribute("onclick", "showHomepage()");
			back.setAttribute("style", "text-decoration: underline;");
	    	back.innerHTML = "Go Back";
	    	div.appendChild(back);
	    	form.appendChild(div);

	    	form.innerHTML += "<br/>";

        	var ticketRows = response.split("^");
        	var T = document.createElement("table");
        	T.setAttribute('class', 'center');

    		var keys = ["Ticket #", "Received", "Subject", "Status"];

        	var R = T.insertRow(0); 
    		R.setAttribute('align', 'center');

    		// Create table header
    		for (var i = 0; i < keys.length; i++) {
    			var cell = document.createElement("th");
    			cell.innerHTML = keys[i];
      			R.appendChild(cell);
    		}

        	for (var i = 0; i < ticketRows.length; i++){
        		var R = T.insertRow(i+1); 
        		R.setAttribute('align', 'center');
        		var theRow = ticketRows[i].split("|");

        		for (var j = 0; j < keys.length; j++) {
	    			var C = R.insertCell(j);  
	      			var txt = document.createTextNode(theRow[j]);
	      			C.appendChild(txt);
    			}	
        		
           }

           form.appendChild(T);
        }
      
    


	}

	function showHomepage() {
		var container = document.getElementById("login");
        	container.removeAttribute("style");
    	document.getElementById("title").innerHTML = "User Homepage";
    	var form = document.theForm;
    	form.innerHTML = '';


    	var text = document.createElement("p");
    	text.innerHTML = "Please choose an option";
    	form.appendChild(text);

    	var newButton = document.createElement("input");
    	newButton.setAttribute('type', 'button');
        newButton.setAttribute('name', 'submit');
        newButton.setAttribute('id', 'submitButton');
        newButton.setAttribute('value', "Submit New Ticket");
        newButton.setAttribute('onclick', 'showRequestForm()');
        form.appendChild(newButton);

    	var newButton = document.createElement("input");
    	newButton.setAttribute('type', 'button');
        newButton.setAttribute('name', 'view');
        newButton.setAttribute('value', "View My Tickets");
        newButton.setAttribute('onclick', 'processData(3)');
        form.appendChild(newButton);

        var newButton = document.createElement("input");
    	newButton.setAttribute('type', 'button');
        newButton.setAttribute('name', 'changePassword');
        newButton.setAttribute('onclick', 'showChangePassword()');
        newButton.setAttribute('value', "Change Password");
        form.appendChild(newButton);  

        form.innerHTML += "<br/>";

        var newButton = document.createElement("input");
    	newButton.setAttribute('type', 'button');
        newButton.setAttribute('name', 'logout');
        newButton.setAttribute('value', "Logout");
        newButton.setAttribute('onclick', 'processData(9)');
        form.appendChild(newButton);   	
	}

	function showChangePassword() {
		var container = document.getElementById("login");
        	container.removeAttribute("style");
		document.getElementById("title").innerHTML = "Change Password";
		var form = document.theForm;
		form.innerHTML = '';

		var id = arguments[0];
		if (id == null) {
			id = "";
		}

		var div = document.createElement("div");
		div.setAttribute("style", "text-align: left;");
		var back = document.createElement("a");
		back.setAttribute("href", "#");
		back.setAttribute("onclick", "showHomepage()");
		back.setAttribute("style", "text-decoration: underline;");
    	back.innerHTML = "Go Back";
    	div.appendChild(back);
    	form.appendChild(div);

    	form.innerHTML += "<br/>";

		var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'userId');
        field.setAttribute('value', '');
        field.setAttribute('placeholder', "Existing User ID");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var field = document.createElement("input");
		field.setAttribute('type', 'password');
        field.setAttribute('name', 'pass');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "New Password");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var field = document.createElement("input");
		field.setAttribute('type', 'password');
        field.setAttribute('name', 'pass2');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Confirm New Password");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var submitButton = document.createElement("input");
    	submitButton.setAttribute('type', 'button');
        submitButton.setAttribute('name', 'submit');
        submitButton.setAttribute('value', "Set New Password");
        submitButton.onclick = function () { processNewPassword(id); };
        //submitButton.setAttribute('onclick', 'processNewPassword()');
        form.appendChild(submitButton);

	}

	function showPasswordReset() {
		var container = document.getElementById("login");
        	container.removeAttribute("style");
		document.getElementById("title").innerHTML = "Password Reset";
		var form = document.theForm;
		form.innerHTML = "";

		if (arguments[0] == 'error'){
			form.innerHTML += "<p style='color:RED;'>The link used was invalid. Please try again</p>";
		}

		form.innerHTML += '<p>Please enter your user ID. The reset link will be sent to the password on file.</p>';

		var link = document.getElementById("forgotPassword");
        link.innerHTML = "<p>Know your password? <a onclick='showLoginForm()' style='cursor:pointer;'>Click here to log in</a>.</p>";
        link = document.getElementById("userExistHelp");
        link.innerHTML = "Not a user? <a onclick='showCreateAccount()' style='cursor:pointer;'>Click here to register</a>.";


		var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'userId');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Existing User ID");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var submitButton = document.createElement("input");
    	submitButton.setAttribute('type', 'button');
        submitButton.setAttribute('name', 'submit');
        submitButton.setAttribute('value', "Send Reset Email");
        submitButton.setAttribute('onclick', 'processPasswordReset()');
        form.appendChild(submitButton);
	}

	function showCreateAccount() {
		var container = document.getElementById("login");
        	container.removeAttribute("style");
		document.getElementById("title").innerHTML = "Create a New Account";
		var form = document.theForm;
		form.innerHTML = '';

		var user = document.getElementById("userExistHelp");
		user.innerHTML = "Already a user? <a onclick='showLoginForm()' style='cursor:pointer;'>Click here to log in</a>.";
		var link = document.getElementById("forgotPassword");
        link.innerHTML = "<p>Forgot your password? <a onclick='showPasswordReset()' style='cursor:pointer;'>Click here to reset it</a>.</p>";

		var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'userId');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "User ID");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'fname');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "First Name");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'lname');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Last Name");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

       	var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'email');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Email Address");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var field = document.createElement("input");
		field.setAttribute('type', 'password');
        field.setAttribute('name', 'pass');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Password");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var field = document.createElement("input");
		field.setAttribute('type', 'password');
        field.setAttribute('name', 'pass2');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Confirm Password");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var submitButton = document.createElement("input");
    	submitButton.setAttribute('type', 'button');
        submitButton.setAttribute('name', 'submit');
        submitButton.setAttribute('value', "Create Account");
        submitButton.setAttribute('onclick', 'processNewAccount()');
        form.appendChild(submitButton);


		/*
	<form method="post" action="index.php">
        <p><input type="text" name="userReset" value="" placeholder="Existing User"></p>
        <p><input type="password" name="newPass1" value="" placeholder="New Password"></p>
        <p><input type="password" name="newPass2" value="" placeholder="Confirm Password"></p>
        <p class="submit"><input type="submit" name="submit" value="Set New Password"></p>
      </form>
		*/

	}

	function showRequestForm() {
		var container = document.getElementById("login");
        	container.removeAttribute("style");
		document.getElementById("title").innerHTML = "Request Ticket";
		var form = document.theForm;
		form.innerHTML = '';

		var div = document.createElement("div");
		div.setAttribute("style", "text-align: left;")
		var back = document.createElement("a");
		back.setAttribute("href", "#");
		back.setAttribute("onclick", "showHomepage()");
		back.setAttribute("style", "text-decoration: underline;")
    	back.innerHTML = "Go Back";
    	div.appendChild(back);
    	form.appendChild(div);

    	form.innerHTML += "<br/><br/>";

		//form.setAttribute("style", "text-align: left;");

        var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'subject');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Subject");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var field = document.createElement("textarea");
        field.setAttribute('rows', '5');
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'problem');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Problem Message");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var button = document.createElement("input");
		button.setAttribute('type', 'button');
        button.setAttribute('name', 'submit');
        button.setAttribute('value', "Submit");
        button.setAttribute('onclick', 'processNewTicket()');
        //button.setAttribute('style', "align='center'");
        form.appendChild(button);

	}

	function showLoginForm() {
		var container = document.getElementById("login");
        	container.removeAttribute("style");
		document.getElementById("title").innerHTML = "Login";
		var form = document.theForm;
		form.innerHTML = '';
		
		var field = document.createElement("input");
		field.setAttribute('type', 'text');
        field.setAttribute('name', 'userId');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "ID");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        field = document.createElement("input");
		field.setAttribute('type', 'password');
        field.setAttribute('name', 'pass');
        field.setAttribute('value', "");
        field.setAttribute('placeholder', "Password");
        form.appendChild(field);

        form.innerHTML += "<br/><br/>";

        var submitButton = document.createElement("input");
    	submitButton.setAttribute('type', 'button');
        submitButton.setAttribute('name', 'submit');
        submitButton.setAttribute('value', "Login");
        submitButton.setAttribute('onclick', 'processLogin()');
        form.appendChild(submitButton);

        var div = document.getElementById("login-help");
        var link = document.getElementById("forgotPassword");
        link.innerHTML = "<p>Forgot your password? <a onclick='showPasswordReset()' style='cursor:pointer;'>Click here to reset it</a>.</p>";
        link = document.getElementById("userExistHelp");
        link.innerHTML = "Not a user? <a onclick='showCreateAccount()' style='cursor:pointer;'>Click here to register</a>.";
	}



</script>
<body >
  <header>
    <br/>
    <h1>Ticket Administration</h1>
  </header>
  <section class="logincontainer" id="logincontainer">
	<div class="login" id='login'>
		<h1 id="title"></h1>
		<form name="theForm">
		</form>
		<div id='login-help' class='login-help'>
			<p id='forgotPassword'></p>
		<p id='userExistHelp'></p>
		</div>
	</div>

</section>
<?php
	$key = $_SERVER['QUERY_STRING'];

	if ($key != null) {

		$db = new mysqli("localhost","Allison","charlie","Allison");
		if ($db->connect_error):
		    die ("Could not connect to db " . $db->connect_error);
		endif;

		$query = "SELECT * FROM Sender WHERE Reset = '$key'";
    	$result = $db->query($query);
    	$rows = $result->num_rows;

    	if ($rows != 0) {
    		echo"<script type='text/javascript'>
    				showChangePassword('$key');
    				</script>";
	
	    } 
    	# User unsuccessful
    	else {
      		$query = "SELECT * from Admin WHERE Reset = '$key'";
      		$result = $db->query($query);
      		$rows = $result->num_rows;
      		# Admin key exists
      		if ($rows != 0) {
      			echo"<script type='text/javascript'>
    				showChangePassword('$key');
    				</script>";

      		} 
      		else {
      			echo"<script type='text/javascript'>
    				showPasswordReset('error');
    				</script>";
      		}
    	}

  	}

  	else {
  		echo"<script type='text/javascript'>
    				processData(0);
    				</script>";
  	}


?>
</body>
</html>