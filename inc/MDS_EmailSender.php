<?php

/**
 * MDS_EmailSender
 * MDS_EmailSender_Exception
 * MDS_EmailSender_Message
 *
 * An open source Email solution for PHP.
 *
 * @author		Mark P Haskins
 * @version     2.6
 * @copyright	Copyright (c) 2005 - 2011, MarksDevServer.com
 * @link		http://www.marksdevserver.com/mds_emailsender
 */

/**
 *   Copyright (c) 2005 - 2011, MarksDevServer.com
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *       * Redistributions of source code must retain the above copyright notice,
 *         this list of conditions and the following disclaimer.
 *
 *       * Redistributions in binary form must reproduce the above copyright notice,
 *         this list of conditions and the following disclaimer in the documentation
 *         and/or other materials provided with the distribution.
 *
 *       * Neither the name of MarksDevServer nor the names of its
 *         contributors may be used to endorse or promote products derived from this
 *         software without specific prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *   DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 *   ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *   (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *   LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *   ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *   (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *   SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */



/**
 * Helper class with the sole purpose in life to create and send an email.
 *
 * To send an email you would create an instance of the MDS_EmailSender_Message
 * and populate it with the required information, and pass that object by calling
 * send on the class.
 *
 * require_once 'MDS_EmailSender.php';
 *
 * $emailMesage = new MDS_EmailSender_Message();
 *
 * try
 * {
 *   $emailMesage->setFrom("Test <someone@domain_a.com>");
 *   $emailMesage->addToRecipient("Test2 <someone@domain_b.com>");
 *
 *   $emailMesage->setSubject("Test of the new Email Code");
 *   $emailMesage->setHtmlBody("<p>This is some HTML</p>");
 *   $emailMesage->setPlainBody("This is some plain text");
 *
 *   $data = file_get_contents("/path/to/file/1.pdf");
 *   $emailMesage->addAttachment($data, "application/pdf", "1.pdf");
 *
 *   $emailSender = new MDS_EmailSender();
 *   $emailSender->send($emailMesage);
 * }
 * catch (MDS_EmailSender_Exception $e)
 * {
 *    // handle exception
 * }
 * 
 * 
 * 
 * Note if you have problems sending emails and it turns out that the Mail Server
 * that you are trying to communicate with is Windows then you'll need to set a
 * flag so the emails can be sent.
 * 
 * $emailSender = new MDS_EmailSender();
 * $emailSender->setWinMailServer();
 * 
 */
class MDS_EmailSender
{
    private $debug;
    private $winMailServer = false;

    private $boundary;

    /**
     * By calling the contructor passing TRUE as a parameter it will enable 
     * debug mode which echos out the mail headers that will be used.
     * 
     * @param type $debug defaults to false. Set to true to get debug info
     */
    public function  __construct($debug = false)
    {
        $this->debug = $debug;
    }

    public function setWinMailServer() {
        
        $this->winMailServer = true;
    }
    
    /**
     *
     * @param MDS_EmailSender_Message $message a populated instance of
     * MDS_EmailSender_Message.
     */
    public function send(MDS_EmailSender_Message $message)
    {
        $this->boundary = "----Muli_Part_Boundary---" . md5(time());

        $fullFrom = $message->getFrom();
        if (!isset ($fullFrom))
        {
            throw new MDS_EmailSender_Exception("No 'FROM' addresses defined.",
                                                 MDS_EmailSender_Exception::MISSING_FROM_ADDRESS);
        }

        $from = $this->getEmailAddress($fullFrom);
        ini_set("sendmail_from", $from);

        $to = $this->createRecipientList($message->getToRecipients());
        if (!isset ($to))
        {
            throw new MDS_EmailSender_Exception("No 'TO' addresses defined.",
                                                 MDS_EmailSender_Exception::MISSING_TO_ADDRESS);
        }

        $subject = $message->getSubject();
        $messageBody = $this->createMessageBody($message);
        $headers = $this->createHeaders($message);
        
        $additional_parameters = "-f" . $from;

        if ($this->debug)
        {
            $this->debug($to, $subject, $messageBody, $headers, $additional_parameters);
        }
        else
        {
            if (!mail($to, $subject, $messageBody, $headers, $additional_parameters))
            {
                throw new MDS_EmailSender_Exception("Mail message was not accepted.",
                                                     MDS_EmailSender_Exception::MAIL_NOT_ACCEPTED);
            }
        }
        
        return true;
    }

    private function createHeaders($message)
    {
        $attachments = $message->getAttachments();
        $htmlBody = $message->getHtmlBody();
        $plainBody = $message->getPlainBody();

        $fullFrom = $message->getFrom();
        $from = $this->getEmailAddress($fullFrom);
        $domain = $this->domain($from);

        $headers = "";
        $headers .= "Return-Path: $fullFrom" . PHP_EOL;
        $headers .= "Message-ID: <" . md5(uniqid(time())) . "@$domain>" . PHP_EOL;
        $headers .= "Date: " . date('r') . PHP_EOL;
        $headers .= "From: $fullFrom" . PHP_EOL;
        $headers .= "Reply-To: $fullFrom" . PHP_EOL;
        
        $ccList = $this->createRecipientList($message->getCcRecipients());
        if (isset ($ccList))
        {
            $headers .= "Cc: $ccList" . PHP_EOL;
        }

        $bccList = $this->createRecipientList($message->getBccRecipients());
        if (isset ($ccList))
        {
            $headers .= "Bcc: $bccList" . PHP_EOL;
        }
    
        if (isset ($htmlBody) || isset ($attachments))
        {
            $headers .= "MIME-Version: 1.0" . PHP_EOL;
        }

        if (isset ($attachments))
        {
            $headers .= "Content-type: multipart/mixed; boundary=\"$this->boundary\"" . PHP_EOL;
        }
        else if (isset ($htmlBody) && !isset ($plainBody))
        {
            $headers .= "Content-type: text/html; charset=iso-8859-1" . PHP_EOL;
        }
        else if (isset ($htmlBody) && isset ($plainBody))
        {
            $headers .= "Content-Type: multipart/alternative; boundary=\"$this->boundary\"" . PHP_EOL;
        }
        
        $headers .= "X-Mailer: PHP" . phpversion()  . PHP_EOL;

        return $headers;
    }

    private function createMessageBody($message)
    {
        $attachments = $message->getAttachments();
        $htmlBody = $message->getHtmlBody();
        $plainBody = $message->getPlainBody();

        $messageBody = "";
        
        if (isset ($htmlBody) && isset ($plainBody))
        {
            $messageBody .= $this->createPlainPart($plainBody);
            $messageBody .= $this->createHTMLPart($htmlBody);

            if (isset ($attachments))
            {
                $messageBody .= $this->createAttachmentPart($attachments);
            }

            $messageBody .= "--$this->boundary" . "--";
        }
        else if (isset ($htmlBody) && !isset ($plainBody))
        {
            if (isset ($attachments))
            {
                $messageBody .= $this->createHTMLPart($htmlBody);
                $messageBody .= $this->createAttachmentPart($attachments);
                $messageBody .= "--$this->boundary" . "--";
            }
            else
            {
                $messageBody .= $htmlBody;
            }
        }
        else if (!isset ($htmlBody) && isset ($plainBody))
        {
            if (isset ($attachments))
            {
                $messageBody .= $this->createPlainPart($plainBody);
                $messageBody .= $this->createAttachmentPart($attachments);
                $messageBody .= "--$this->boundary" . "--";
            }
            else
            {
                $messageBody .= $plainBody;
            }
        }

        return $messageBody;
    }

    private function createPlainPart($plainBody)
    {
        $part = "";

        $part .= "--$this->boundary\n";
        $part .= "Content-type: text/plain; charset=UTF-8" . PHP_EOL;
        $part .= "Content-Transfer-Encoding: 8bit". PHP_EOL . PHP_EOL;
        $part .= $plainBody . PHP_EOL;

        return $part;
    }

    private function createHTMLPart($htmlBody)
    {
        $part = "";

        $part .= "--$this->boundary\n";
        $part .= "Content-type: text/html; charset=UTF-8" . PHP_EOL;
        $part .= "Content-Transfer-Encoding: 8bit". PHP_EOL . PHP_EOL;
        $part .= $htmlBody . PHP_EOL;

        return $part;
    }

    private function createAttachmentPart($attachments)
    {
        $part = "";

        foreach($attachments as $attachment)
        {
            $a = $attachment['data'];
            $t = $attachment['type'];
            $n = $attachment['name'];

            $data = chunk_split(base64_encode($a));

            $part .= "--$this->boundary\n";
            $part .= "Content-type: $t name=$n;" . PHP_EOL;
            $part .= "Content-Disposition: attachment; filename=$n" . PHP_EOL;
            $part .= "Content-Transfer-Encoding: base64". PHP_EOL . PHP_EOL;
            $part .= $data . PHP_EOL;
        }

        return $part;
    }

    private function createRecipientList($recipients) {

        if ($this->winMailServer) {
            
            return $this->createWindowsMailServerRecipientList($recipients);
            
        } else {
            
            return $this->createLinuxMailServerRecipientList($recipients);
        }
        
    }
    
    private function createLinuxMailServerRecipientList($recipients) {
        
        $list = null;

        $numRecipients = count($recipients);
        if ($numRecipients > 0)
        {
            $list = "";

            $count = 1;
            foreach($recipients as $email)
            {
                $list .= $email;

                if ($count < $numRecipients)
                {
                    $list .= ", ";
                }

                $count++;
            }
        }

        return $list;
    }
    
    /**
     * Taken from the PHP mail function notes about windows mail servers
     * 
     * As such, the to parameter should not be an address in the form of 
     * "Something <someone@example.com>". The mail command may not parse 
     * this properly while talking with the MTA.
     * 
     */
    public function createWindowsMailServerRecipientList($recipients) {
        
        $list = null;

        $numRecipients = count($recipients);
        if ($numRecipients > 0)
        {
            $list = "";

            $count = 1;
            foreach($recipients as $email)
            {
                // strip everything except for email address
                $to = $this->getEmailAddress($email);
                
                $list .= $to;

                if ($count < $numRecipients)
                {
                    $list .= ", ";
                }

                $count++;
            }
        }

        return $list;
        
    }

    private function getEmailAddress($fullEmail)
    {
        $emailAdress = trim($fullEmail);

        $first = stripos($emailAdress, "<");
        $length = strlen($emailAdress) - $first;

        return substr($emailAdress, $first + 1, $length - 2);
    }

    private function domain($emailAddress)
    {
        $at = strpos($emailAddress, "@");
        $length = strlen($emailAddress) - $at;

        return substr($emailAddress, $at + 1, $length - 1);
    }

    private function debug($to, $subject, $messageBody, $headers, $additional_parameters)
    {
        echo "<pre>";
        echo "To : " . $to . PHP_EOL . PHP_EOL;
        echo "----- Headers Start -----" . PHP_EOL;
        echo $headers;
        echo "----- Headers End -----". PHP_EOL . PHP_EOL;
        echo "Subject : " . $subject . PHP_EOL . PHP_EOL;
        echo "----- Body Start -----" . PHP_EOL;
        echo $messageBody;
        echo "----- Body End -----" . PHP_EOL . PHP_EOL;
        echo "Additional = " . $additional_parameters . PHP_EOL;
        echo "</pre>";
    }
}






/**
 * Model Utility class which contains all information that are to make up a single
 * email message that will be send using the MDS_EmailSender.
 *
 * When passing in email addresses for any of the fields the email address must
 * be in the following format
 * <br/>
 * Test <someone@domain_a.com>
 * <br/>
 * Which is Friendly name followed by a space followed by an email address enclosed
 * in <>, failure to pass the emai address in this format will result in an exception.
 */
class MDS_EmailSender_Message
{
    private $toAddresses;
    private $ccAddresses;
    private $bccAddresses;
    private $from;
    private $subject = "";
    private $plainBody;
    private $htmlBody;

    private $attachments;

    /**
     * Adds a <em>to</em> recipient to the message.
     * @param string $emailAddress Needs to be formated as like this :- Friendly Name <user@domain.com>
     */
    public function addToRecipient($emailAddress)
    {        
        $emailAddress = strtolower($emailAddress);
        
        $this->isValidEmailAddress($emailAddress,
                                   MDS_EmailSender_Exception::INVALID_TO_ADDRESS);
        
        if (!isset($this->toAddresses))
        {
            $this->toAddresses = array();
        }

        $this->toAddresses[] = $emailAddress;
    }

    /**
     * Returns an array of email address associated with TO address.
     * @return array an array of email address associated with TO address.
     */
    public function getToRecipients()
    {
        return $this->toAddresses;
    }

    /**
     * Adds a <em>cc</em> recipient to the message.
     * @param string $emailAddress Needs to be formated as like this :- Friendly Name <user@domain.com>
     */
    public function addCcRecipient($emailAddress)
    {
        $emailAddress = strtolower($emailAddress);
        
        $this->isValidEmailAddress($emailAddress,
                                   MDS_EmailSender_Exception::INVALID_CC_ADDRESS);
        
        if (!isset($this->ccAddresses))
        {
            $this->ccAddresses = array();
        }

        $this->ccAddresses[] = $emailAddress;
    }

    /**
     * Returns an array of email address associated with CC address.
     * @return array an array of email address associated with CC address.
     */
    public function getCcRecipients()
    {
        return $this->ccAddresses;
    }

    /**
     * Adds a <em>bcc</em> recipient to the message.
     * @param string $emailAddress Needs to be formated as like this :- Friendly Name <user@domain.com>
     */
    public function addBccRecipient($emailAddress)
    {
        $emailAddress = strtolower($emailAddress);
        
        $this->isValidEmailAddress($emailAddress,
                                   MDS_EmailSender_Exception::INVALID_BCC_ADDRESS);
        
        if (!isset($this->bccAddresses))
        {
            $this->bccAddresses = array();
        }

        $this->bccAddresses[] = $emailAddress;
    }

    /**
     * Returns an array of email address associated with BCC address.
     * @return array an array of email address associated with BCC address.
     */
    public function getBccRecipients()
    {
        return $this->bccAddresses;
    }

    /**
     * Adds a <em>from</em> email address to the message.
     * @param string $emailAddress Needs to be formated as like this :- Friendly Name <user@domain.com>
     */
    public function setFrom($emailAddress)
    {        
        $emailAddress = strtolower($emailAddress);
        
        $this->isValidEmailAddress($emailAddress,
                                   MDS_EmailSender_Exception::INVALID_FROM_ADDRESS);

        $this->from = $emailAddress;
    }

    /**
     * @param string an email address
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets the subject line of the email
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     *
     * @return string the subject of the message as a string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the plain text email body content
     * @param string $body
     */
    public function setPlainBody($body)
    {
        $this->plainBody = $body;
    }

    /**
     *
     * @return string the email body
     */
    public function getPlainBody()
    {
        return $this->plainBody;
    }

    /**
     * Sets the HTML email body content
     * @param string $body
     */
    public function setHtmlBody($body)
    {
        $this->htmlBody = $body;
    }

    /**
     *
     * @return string the email body
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * Adds an attachment to the email
     * @param mixed  $data The actual file data
     * @param string $type What type of file it is e.g. application/pdf
     * @param string $filename the name of the file e.g. some.pdf
     */
    public function addAttachment($data, $type, $filename)
    {
        if (!isset($this->attachments))
        {
            $this->attachments = array();
        }
        
        $this->attachments[] = array (
            "data" => $data,
            "type" => $type,
            "name" => $filename
        );
    }

    /**
     *
     * @return array all attachements
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    private function isStrictEmailFormat($email, $code = 0)
    {
        // Pattern Matches 'Mark <mark@domain.com>'
        $pattern = "[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";

        if(!eregi($pattern, $email))
        {
           throw new MDS_EmailSender_Exception("$email is not formatted correctly", $code);
        } 
    }
    
    private function isValidEmailAddress($email, $code = 0)
    {
        // Pattern Matches 'mark@domain.com'
        //$pattern = "[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";
        $pattern = "[_a-z0-9-]+ <[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})>$";

        if(!eregi($pattern, $email))
        {
           throw new MDS_EmailSender_Exception("$email is not formatted correctly", $code);
        }
    }
}





/**
 * Class that defines an exception that is thrown when appropriate by the
 * MDS_EmailSender class.
 */
class MDS_EmailSender_Exception extends Exception
{
    const UNKNOWN_EXCEPTION    = 0;
    const INVALID_FROM_ADDRESS = 1;
    const INVALID_TO_ADDRESS   = 2;
    const INVALID_CC_ADDRESS   = 3;
    const INVALID_BCC_ADDRESS  = 4;
    const MISSING_FROM_ADDRESS = 5;
    const MISSING_TO_ADDRESS   = 6;
    const MAIL_NOT_ACCEPTED    = 7;


    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);

        error_log($message, 0);
    }

    public function errorMessage()
    {
        $errorMsg = '<b>'.$this->getMessage() . '</b><br/>';
        
        return $errorMsg;
    }

    public function errorCode()
    {
        return $code;
    }
}

?>
