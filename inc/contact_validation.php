<?php
$count = 0;

$errors = "";

$name = strip_tags($_POST["name"]);
$email = strip_tags($_POST["email"]);
$enquiry = strip_tags($_POST["enquiry"]);

if (!empty($_POST['name'])) {
        
    $errors = "Please complete all required fields and check your email address is valid.";
    
    if (!empty($_POST["email"])) {
        $email_check = preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $_POST["email"]);
   		if ($email_check !== 0) {
            $email = strip_tags($_POST["email"]);
    	} else {
            $count++;
            $errors .= "<!-- Put a specific message here if you want to add to the error string -->";
        }
    } else {
        $count++;
        $errors .= "<!-- Put a specific message here if you want to add to the error string -->";
    }
    
    if (!empty($_POST["enquiry"])) {
        $enquiry = strip_tags($_POST["enquiry"]);
    } else {
        $count++;
        $errors .= "";
    }    
    
    $name = strip_tags($_POST["name"]);
    $telephone = strip_tags($_POST["telephone"]);
    
} else {
    $count++;
    $errors .= "Please enter your name";
}
?>