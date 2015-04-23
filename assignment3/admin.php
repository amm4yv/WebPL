<?php

  if($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  }

	session_start();

?>

<html>
<head>
	<title>Ticket System Admin Panel</title>
  <link type="text/css" rel="stylesheet" href="style.css" media="screen" />
  <script type="text/javascript">

  var ticketDetailNumber = 0;

  function processData() {

    var httpRequest;
    var type = arguments[0];
    var data = 'type=' + type;

    if (window.XMLHttpRequest) {
        httpRequest=new XMLHttpRequest();
    }

    if (type == 0) {
      var logincontainer = document.getElementById("logincontainer");
      var login = document.getElementById("login");
      var div = document.getElementById("buttons");
      login.innerHTML = "";
      div.innerHTML = "";
      logincontainer.removeAttribute("class");
      login.removeAttribute("class");
    }

    if (type == 4) {
      var index = arguments[1];
      //alert("index " + index);
      var ticketNum = document.getElementById("ticketTable").rows[index].cells[0].innerHTML;
      data += '&ticket=' + ticketNum;

    }

    if (type == 8) {
      data += '&sort=' + arguments[1]; 
      
    }

    // Change Password
    if (type == 10){
        var password = arguments[1];
        data = 'type=' + type + '&pass=' + password;
      }


    //alert(data);

    httpRequest.onreadystatechange=function() {
      if (httpRequest.readyState==4 && httpRequest.status==200) {
        var response = httpRequest.responseText;
        
        if (type == 0) {      
          //alert(response);
          if (response == "Error")
            window.location="index.php";
          else
            showTicketTable(response); 
        }

        else if (type == 4) {
          showTicketDetail(response);
        }

        else if (type == 9) {
          window.location="index.php";
        } 

        //Change Password
        else if (type == 10) {
          if (response == "Error") {
              alert("User does not exist");
              showChangePassword();
            }
          else if (response == "Admin") {
              alert("Successfully set new password!");
              processData(0);
            } else {
              alert("Error");
              showChangePassword();
            }
        }

        else {
          showTicketTable(response);
        }

        // All other types show the ticket table
        if (type != 4 && type != 10) {
          processButtonArray();
          theTickets = new Array();
          var tickets = response.split("^");
          for (var i = 0; i < tickets.length; i++) {
            var ticket = new Ticket(tickets[i].split("|"));
            theTickets[i] = ticket;
          }
        }
      }
    }

    httpRequest.open("POST","processAdmin.php",true);
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    httpRequest.send(data);


  }

  function Ticket(ticketData) {
    //var ticketRow = ticketData.split("|");

    this.id = ticketData[0];
    this.received = ticketData[1];
    this.senderName = ticketData[2];
    this.senderEmail = ticketData[3];
    this.subject = ticketData[4];
    this.tech = ticketData[5];
    this.status = ticketData[6];


  }

  function processDetail() {
    var httpRequest;
    var type = arguments[0];
    var data = 'type=' + type + '&ticket=' + ticketDetailNumber;

    if (window.XMLHttpRequest) {
        httpRequest=new XMLHttpRequest();
    }

    if (type == 1 || type == 4) {
      document.body.style.cursor = "progress";
    }

    // Send Mail
    if (type == 4) {
      var subject = document.mailForm.subject.value;
      var message = document.mailForm.msg.value;
      data += '&subject=' + subject + '&message=' + message;
    }


    //alert(data);
    //alert(ticketDetailNumber);

    httpRequest.onreadystatechange=function() {
      if (httpRequest.readyState==4 && httpRequest.status==200) {
        document.body.style.cursor = "default";
        var response = httpRequest.responseText;
        //alert(response);
        if (type == 6 || type == 7) {
          var details = response.split("#");
          theTickets = new Array();
          var tickets = details[9].split("^");
          for (var i = 0; i < tickets.length; i++) {
            var ticket = new Ticket(tickets[i].split("|"));
            theTickets[i] = ticket;
          }
        }
        if (type != 5)
          showTicketDetail(response);
        else
          processData(0);
      }
    }

    httpRequest.open("POST","processTicketDetail.php",true);
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    httpRequest.send(data);

  }

  function processButtonArray() {
    var httpRequest;
    if (window.XMLHttpRequest) {
        httpRequest=new XMLHttpRequest();
    }

    httpRequest.onreadystatechange=function() {
      if (httpRequest.readyState==4 && httpRequest.status==200) {
        var response = httpRequest.responseText;
        showButtonTable(response);
      }
    }

    httpRequest.open("POST","processAdmin.php",true);
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    httpRequest.send('type=button');

  }

  function processButton() {
    var button = arguments[0];
    //alert(button);
    if (button == 0) {
      processData(1, arguments[1]);
    }
    else {
      if (button == "View Open Tickets") {
        processData(1);
      }

      if (button == "View All Tickets") {
        processData(2);
      }

      if (button == "View My Tickets") {
        processData(5);
      }

      if (button == "View Unassigned Tickets") {
        processData(7);
      }
    }

  }

  function processNewPassword() {
    var password = document.changeForm.pass.value;
    var password2 = document.changeForm.pass2.value;

    //alert(id + password);

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

    processData(10, password);

  }

  function showTicketDetail(response) {
    //alert(response);
    var details = response.split("#");
    var titles = ["Received", "Sender", "Tech", "Status", "Subject", "Problem"];

    var ticketTable = document.getElementById("ticketTable");
    ticketTable.innerHTML = "";

    var buttonTable = document.getElementById("buttonTable");
    buttonTable.innerHTML = "";

    var logincontainer = document.getElementById("logincontainer");
    var login = document.getElementById("login");
    logincontainer.innerHTML = "";
    login.innerHTML = "";
    logincontainer.setAttribute("class", "logincontainer");
    login.setAttribute("class", "login");

    var head = document.createElement("h1");
    head.innerHTML = "Ticket #" + details[0];
    login.appendChild(head);

    for (var i = 0; i < titles.length; i++) {
      var txt = document.createElement("p");
      txt.innerHTML = "<b>" + titles[i] + ":</b> " + details[i+1];
      login.appendChild(txt);
    }

    logincontainer.appendChild(login);

    var div = document.getElementById("buttons");
    buttons.innerHTML = "";
    //div.innerHTML = "<br/>";

    ticketDetailNumber = details[0];

    var button = document.createElement("input");
    button.setAttribute("type", "button");  
    button.setAttribute("onclick", "processDetail(1)");
    //button.onclick = function() { processDetail(0, num); };
    button.setAttribute("value", "Close/Reopen Ticket");
    //button.onclick = function() { processButton(1); };
    div.appendChild(button);

    if (details[3] == "Unassigned") {
      var button = document.createElement("input");
      button.setAttribute("type", "button");
      button.setAttribute("onclick", "processDetail(2)");
      button.setAttribute("value", "Assign Self to Ticket");
      div.appendChild(button);
    }

    if (details[7] == "true") {
      var button = document.createElement("input");
      button.setAttribute("type", "button");
      button.setAttribute("onclick", "processDetail(3)");
      button.setAttribute("value", "Remove Self from Ticket");
      div.appendChild(button);
    }

    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("onclick", "showMailForm()");
    button.setAttribute("value", "Email Sender");
    div.appendChild(button);

    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("onclick", "processDetail(5)");
    button.setAttribute("value", "Delete Ticket");
    div.appendChild(button);

    div.innerHTML += "<br/>";

    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("onclick", "processDetail(6)");
    button.setAttribute("value", "Find all Tickets from Sender");
    div.appendChild(button);

    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("onclick", "processDetail(7)");
    button.setAttribute("value", "Find Similar Tickets");
    div.appendChild(button);

    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("value", "Go Back to Admin");
    button.setAttribute("onclick", "processData(0)");
    div.appendChild(button);

    if (details[8] == "Mail") {
      div.innerHTML += "<p>" + details[9] + "</p>";
    }

    if (details[8] == "TicketTable") {
      showTicketTable(details[9]);
      details[8] = "";
    }

    if (details[8] == "NoTickets") {
      div.innerHTML += "<p>No similar tickets exist.</p>";
    }

  }

  function showMailForm() {
    var ticketTable = document.getElementById("ticketTable");
    ticketTable.innerHTML = "";

    //alert("hello");
    var div = document.getElementById("buttons");

    var form = document.createElement("form");
    form.setAttribute("name", "mailForm");
    form.innerHTML = "<br/><br/><br/><b>Subject:</b><br/><br/>";

    var field = document.createElement("input");
    field.setAttribute("type", "text");
    field.setAttribute("name", "subject");
    field.setAttribute("size", "60");
    form.appendChild(field);

    form.innerHTML += "<br/><br/><b>Message:</b><br/><br/>";

    var field = document.createElement("textarea");
    field.setAttribute("type", "text");
    field.setAttribute("name", "msg");
    field.setAttribute("rows", "5");
    field.setAttribute("cols", "60");
    form.appendChild(field);

    form.innerHTML += "<br/><br/>";

    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("onclick", "processDetail(4)");
    button.setAttribute("value", "Send");    
    form.appendChild(button);

    form.innerHTML += "<br/><br/><br/><br/>";

    div.appendChild(form);

  }

  function showTicketTable(response) {
 
    var T = document.getElementById("ticketTable");
    T.innerHTML = "";

    
    var headers = ["Ticket #", "Received", "Sender Name", "Sender Email", "Subject", "Tech", "Status"];
    var keys = ["id", "received", "senderName", "senderEmail", "subject", "tech", "status"];

    var R = T.insertRow(0); 
    R.setAttribute('align', 'center');

    // Create table header
    for (var i = 0; i < headers.length; i++) {
      var cell = document.createElement("th");
      cell.innerHTML = headers[i];
      if (i == 0) { cell.onclick = function() { processSort('id'); }; }
      if (i == 1) { cell.onclick = function() { processSort('received'); }; }
      if (i == 2) { cell.onclick = function() { processSort('senderName'); }; }
      if (i == 3) { cell.onclick = function() { processSort('senderEmail'); }; }
      if (i == 4) { cell.onclick = function() { processSort('subject'); }; }
      if (i == 5) { cell.onclick = function() { processSort('tech'); }; }
      if (i == 6) { cell.onclick = function() { processSort('status'); }; }
      cell.setAttribute("style", "cursor:pointer");
      R.appendChild(cell);
    }

    // Create Select column
    // var cell = document.createElement("th");
    // cell.innerHTML = "Select";
    // R.appendChild(cell);

    var ticketRows = response.split("^");
    var len = T.rows.length;

    for (var i = 0; i < ticketRows.length; i++){
      var R = T.insertRow(i+1); 
      R.setAttribute('align', 'center');
      addSelection(R);
      var theRow = ticketRows[i].split("|");

      for (var j = 0; j < keys.length; j++) {
        var C = R.insertCell(j);  
        var txt = document.createTextNode(theRow[j]);
        C.appendChild(txt);
      } 

      // Create selection radios
      //addSelectionRadio(R, keys.length);
      
     }

    // var actualKeys = ["Ticket_num", "Received", "Sender_name", "Sender_email", "Subject", "Name", "Status"];

    //  // Create sort radios
    // var R = T.insertRow(ticketRows.length+1);
    // for (var i = 0; i < actualKeys.length; i++) {
    //   var cell = document.createElement("th");
    //   cell.innerHTML = "Sort By ";
    //   var selection = document.createElement("input");
    //   selection.setAttribute("type", "radio");
    //   selection.setAttribute("name", "sort");
    //   //selection.setAttribute("value", key);
    //   if (i == 0) { selection.onclick = function() { processSort('id'); }; }
    //   if (i == 1) { selection.onclick = function() { processSort('received'); }; }
    //   if (i == 2) { selection.onclick = function() { processSort('senderName'); }; }
    //   if (i == 3) { selection.onclick = function() { processSort('senderEmail'); }; }
    //   if (i == 4) { selection.onclick = function() { processSort('subject'); }; }
    //   if (i == 5) { selection.onclick = function() { processSort('tech'); }; }
    //   if (i == 6) { selection.onclick = function() { processSort('status'); }; }
    //   cell.appendChild(selection);
    //   R.appendChild(cell);
    // }

  }

  function addSelectionRadio(R, keys) {
    var T = document.getElementById("ticketTable");
    var len = T.rows.length-1;

    var C = R.insertCell(keys);  
    var selection = document.createElement("input");
    selection.setAttribute("type", "radio");
    selection.setAttribute("name", "ticket");
    selection.setAttribute("value", len);
    selection.onclick = function() { processData(4, len); };
    C.appendChild(selection);
  }

  function addSelection(R){
    var T = document.getElementById("ticketTable");
    var len = T.rows.length-1;
    R.onclick = function () { processData(4, len); }
  }

  function processSort(key){
    //alert("Processing sort " + key);
    theTickets.sort(compare(key));
    var response = "";
    for (var i = 0; i < theTickets.length; i++){
      response += getTicketResponse(theTickets[i]);
      if (i < theTickets.length-1)
        response += "^";
    }

    showTicketTable(response);

  }

  function getTicketResponse(ticket) {
    return ticket.id + "|" + ticket.received + "|" + ticket.senderName + "|" + ticket.senderEmail + "|" + ticket.subject + "|" + ticket.tech + "|" + ticket.status;
  }

  function compare(key) {
    var sortOrder = 1;
    if(key[0] === "-") {
        sortOrder = -1;
        key = key.substr(1);
    }
    return function (a,b) {
        var result = (a[key] < b[key]) ? -1 : (a[key] > b[key]) ? 1 : 0;
        return result * sortOrder;
    }
  }

  function showButtonTable(response) {

    var form = document.getElementById("theForm");
    var T = document.getElementById("buttonTable");
    T.innerHTML = "";

    if (response == "No Tickets") {
      var txt = document.createElement("p");
      txt.innerHTML = "No tickets match that query.";
      form.innerHTML += "<br/>"
      form.appendChild(txt);
      return;
    }     

    var buttons = response.split("^")

    var row1 = document.createElement("tr");

    var newCell = document.createElement("td");
    newCell.setAttribute("style", "padding:2px;");
    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("value", buttons[0]);
    button.onclick = function() { processButton(buttons[0]); };
    newCell.appendChild(button);
    row1.appendChild(newCell);

    var newCell = document.createElement("td");
    newCell.setAttribute("style", "padding:2px;");
    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("value", buttons[1]);
    button.onclick = function() { processButton(buttons[1]); };
    newCell.appendChild(button);
    row1.appendChild(newCell);

    var newCell = document.createElement("td");
    newCell.setAttribute("style", "padding:2px;");
    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("value", buttons[2]);
    button.onclick = function() { processButton(buttons[2]); };
    newCell.appendChild(button);
    row1.appendChild(newCell);

    //T.appendChild(row1);

    //var row2 = document.createElement("tr");

    var newCell = document.createElement("td");
    newCell.setAttribute("style", "padding:2px;");
    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("value", "Change Password");
    button.setAttribute("onclick", "showChangePassword()");
    newCell.appendChild(button);
    row1.appendChild(newCell);

    // var newCell = document.createElement("td");
    // var button = document.createElement("input");
    // button.setAttribute("type", "button");
    // button.setAttribute("value", "");
    // button.setAttribute("onclick", "processData(4)");
    // newCell.appendChild(button);
    // row2.appendChild(newCell);

    var newCell = document.createElement("td");
    newCell.setAttribute("style", "padding:2px;");
    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("value", "Logout");
    button.setAttribute("onclick", "processData(9)");
    newCell.appendChild(button);
    row1.appendChild(newCell);



    T.appendChild(row1);

  }

  function showChangePassword() {
    
    var ticketTable = document.getElementById("ticketTable");
    ticketTable.innerHTML = "";

    var buttonTable = document.getElementById("buttonTable");
    buttonTable.innerHTML = "";

    var logincontainer = document.getElementById("logincontainer");
    var login = document.getElementById("login");
    logincontainer.innerHTML = "";
    login.innerHTML = "";
    logincontainer.setAttribute("class", "logincontainer");
    login.setAttribute("class", "login");

    var head = document.createElement("h1");
    head.innerHTML = "Change Password";
    login.appendChild(head);

    var form = document.createElement("form");
    form.setAttribute("style", "text-align:center;")
    form.setAttribute("name", "changeForm");

    var div = document.createElement("div");
    div.setAttribute("style", "text-align: left;");
    var back = document.createElement("a");
    back.setAttribute("href", "#");
    back.setAttribute("onclick", "processData(0)");
    back.setAttribute("style", "text-decoration: underline;");
    back.innerHTML = "Go Back";
    div.appendChild(back);
    form.appendChild(div);

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
    submitButton.onclick = function () { processNewPassword(); };
    //submitButton.setAttribute('onclick', 'processNewPassword()');
    form.appendChild(submitButton);

    login.appendChild(form);
    logincontainer.appendChild(login);

  }


  </script>
</head>
<body onload = "processData(0)">

  <section class="container" id="body">

    <header>
    <br/>
    <h1 style="font-size: 24;">Ticket Administration</h1>
    </header>

        <section id="logincontainer">
    <div style="text-align:left;" id="login">

    </div>
  </section>

  <div class = "buttons" id="buttons">

  </div>

    <form name="theForm">
    <table class='center' style='border:0;' id='buttonTable'>

    </table>

    <table class='center' id='ticketTable'>
    
    </table>


    </form>

  </section>
  <script type="text/javascript">
    var theTickets = new Array();
  </script>
</body>
</html>
    
