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
    include('contact_data.php');

    if($count > 0)
    {
        $response = array(
              'ok' => false,
              'msg' => '<p>'.$errors.'</p>');
        return $response;
    }  
   
    
    $emailMesage = new MDS_EmailSender_Message();
    
    try
    {
	    	// to address
		$emailMesage->addToRecipient("Andy <andykleeman@gmail.com>");
    	
    		// from address
        $emailMesage->setFrom("Dave <dave@dave.com>");

        	// email subject and content
        $emailMesage->setSubject("Contact form example");
        $emailMesage->setPlainBody("");
        $emailMesage->setHTMLBody('From: <strong>'.$name.'</strong><br />
        Email: <strong>'.$email.'</strong><br />
        Telephone: <strong>'.$telephone.'</strong><br />
        Enquiry: <strong>'.$enquiry.'</strong><br />');

        $emailSender = new MDS_EmailSender();
        $emailSender->setWinMailServer();
        $emailSender->send($emailMesage);
    }
    catch (MDS_EmailSender_Exception $e)
    {
        $success = false;
    }
    
    
    // send Json Response - this is the returned thanks message and also fades out the form
    $response = array(
          'ok' => true,
          'msg' => "<div class='success'>Thank you for your enquiry we will endever to get back<br /> to you soon as possible.<br /><br /></div><script type='text/javascript'>jQuery('#contact-form').fadeOut(300); jQuery('#msg').css('float', 'none');</script>");
   
    
    return $response;
}


?>
