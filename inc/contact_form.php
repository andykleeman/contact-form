<?php

include('MDS_EmailSender.php');

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
{
    echo json_encode(processForm());
    exit;
}
else
{
    echo json_encode(array('ok' => false, 'msg' => 'Not Ajax request'));
}

function processForm()
{
    include('contact_validation.php');

    if($count > 0)
    {
        $response = array(
              'ok' => false,
              'msg' => '<p>'.$errors.'</p>');
        return $response;
    }  
   
    
    $emailMesage = new MDS_EmailSender_Message();
    $success = true;
    
    try
    {
	    	// to address
		$emailMesage->addToRecipient("Your Name <you@emailaddress.com>");
    	
    		// from address
        $emailMesage->setFrom($name." <".$email.">");

        	// email subject and content
        $emailMesage->setSubject("Contact form example");
        $emailMesage->setPlainBody("");
        $emailMesage->setHTMLBody('From: <strong>'.$name.'</strong><br />
        Email: <strong>'.$email.'</strong><br />
        Telephone: <strong>'.$telephone.'</strong><br />
        Enquiry: <strong>'.$enquiry.'</strong><br />');

        $emailSender = new MDS_EmailSender();
    /* 	
    //	Use when sending via a Windows Mail Server
      	$emailSender->setWinMailServer();
    */
        $emailSender->send($emailMesage);
    }
    catch (MDS_EmailSender_Exception $e)
    {
        echo 'Error Code is : ' . $e->errorCode();
        echo 'Error Message is : ' . $e->errorMessage();
        // you can find what the codes mean in the MDS_EmailSender.php file
        $success = false;
    }
    
    
    if ($success === true) {
    	// SUCCESS RESPONSE
	    // send Json Response - this is the returned thanks message and also fades out the form
	    $response = array(
	          'ok' => true,
	          'msg' => "<div class='success'>Thank you for your enquiry we will endever to get back<br /> to you soon as possible.<br /><br /></div><script type='text/javascript'>jQuery('#contact-form').fadeOut(300); jQuery('#msg').css('float', 'none');</script>");
	} else {
		    // FAILED
	    $response = array(
	          'ok' => false,
	          'msg' => "Sorry there was an error and your message has not been sent.");	
	}
    
    return $response;
}


?>
