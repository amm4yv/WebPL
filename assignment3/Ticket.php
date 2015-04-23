<?php
class Ticket {

   public $number;
   public $received;
   public $senderID;
   public $senderName;
   public $senderEmail;
   public $subject;
   public $problem;
   public $tech;
   public $status;

   public function __construct($num, $date, $senderID, $senderName, $senderEmail, $subject, $tech, $status, $problem) {
    $this->number = $num;
    $this->received = $date;
    $this->senderID = $senderID;
    $this->senderName = $senderName;
    $this->senderEmail = $senderEmail;
    $this->subject = $subject;
    $this->problem = $problem;
    $this->tech = $tech;
    $this->status = $status;
   }

   public function getTechName($db) {
      $query = "SELECT Admin.Name FROM Admin WHERE Admin_id = '$this->tech'";
      $result = $db->query($query);
      if ($result->num_rows == 0)
        return "Unassigned";
      else {
        $row = $result->fetch_array();
        return $row[0];
      }

   }

   public function toggleStatus($db) {
     if ($this->status == "open") {
      $this->status = "closed";
      $query = "UPDATE Tickets SET Status='closed', Received=Received WHERE Ticket_num = '$this->number'";
      $db->query($query);
    } 

    else if ($this->status == "closed") {
      $this->status = "open";
      $query = "UPDATE Tickets SET Status='open', Received=Received WHERE Ticket_num = '$this->number'";
      $db->query($query);
    }
   }

   public function assignTech($tech, $db) {
    $this->tech = $tech;
    $query = "UPDATE Tickets SET Admin_id=$tech, Received=Received WHERE Ticket_num = '$this->number'";
    $db->query($query);    
   }

   public function removeTech($db) {
    $this->tech = NULL;
    $query = "UPDATE Tickets SET Admin_id=NULL, Received=Received WHERE Ticket_num = '$this->number'";
    $db->query($query);  
   }

   public function delete($db) {
    $query = "DELETE FROM Tickets WHERE Ticket_num = '$this->number'";
    $db->query($query);
   }

}
?>