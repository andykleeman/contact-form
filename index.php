<?php 
	$thisPage='contact';	
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" class="<?php echo($thisPage); ?>">
<head>
<title>Contact form</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<link rel="shortcut icon" href="/favicon.ico"/> 
	

	<link rel="stylesheet" href="css/normalize.css" media="screen" />
	<link rel="stylesheet" href="css/form.css" media="screen" />

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	
</head>
<body class="<?php echo($thisPage); ?>">

<div class="page-wrap cf">
	
	
	<div class="form-wrap">		
		<h1>MDS Email Sender contact form</h1>
		
		<!-- 
			Fields are validated using the js in functions.js
			required fields should have the data-required="required" and data-field="name" attributes
		-->			
					
		<form id="contact-form">
			<label for="name">Name:</label>
			<input type="text" name="name" id="name" data-required="required" data-field="name" />
			
			<label>Email:</label>
			<input type="email" name="email" id="email" data-required="required" data-field="email" />
				
			<label for="name">Telephone:</label>
			<input type="text" name="telephone" id="telephone" />				
						
			<label>Enquiry:</label>
			<textarea name="enquiry" data-required="required" data-field="enquiry"></textarea>
						
			<input type="submit" value="Send" />
						
		</form>
		
		<div id="msg"></div>
		
		<h2>How this works</h2>
		
		<ol>
			<li>Functions.js contains frond-end validation for the form, if this passes it posts an ajax call to contact_form.php</li>
			<li>contact_form.php includes contact_data.php which passes the back end validation if this is successful an email will be sent and a success message returned to #msg.</li>
		</ol>
		
		<h3>Notes / Tips</h3>
		
		<ul>
			<li>Fields are validated using the js in functions.js - required fields should have the data-required="required" and data-field="name" attributes</li>
			<li>Make sure you update the email to and from addresses lines 34-38 of contact_form.php</li>
		</ul>
		
		
	</div>				
					


</body>
</html>