<?php

class Zend_Controller_Action_Helper_SendMail extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * 
	 * A helper that executes the send() function
	 *  
	 * @param string $username
	 * @param string $password
	 * @param string $serverName
	 * @param string $from
	 * @param string $to or array
	 * @param string $subject
	 * @param string $body
	 */
	public function send($from, $to, $subject, $body, $attachs = array())
    {
     	try {
  			$config = array('auth' => 'login',
  					//'ssl' => 'tls',
					'username'  => 'mail_sender@moldovenii.md',
					'password'  => '#hf^Sj10(2834',
					'port' => 25);
  	
		  	$server = '192.168.13.66';
		  	$tr = new Zend_Mail_Transport_Smtp($server ,$config);
		    $mail = new Zend_Mail('utf-8');

		    if ($attachs) {
	     		foreach ( $attachs as $attach ) {
					$idata = file_get_contents ( $attach['path'] );
					$itype = 'image/png';
					$img = $mail->createAttachment ( $idata, $itype, Zend_Mime::DISPOSITION_INLINE, Zend_Mime::ENCODING_BASE64 );
					if (isset ( $attach['cid'] )) {
						$img->id = $attach['cid'];
					}
				}
		    }
		    
		    $mail->setType(Zend_Mime::MULTIPART_RELATED);
			$mail->setBodyHtml($body);
			$mail->setFrom($from);
			$mail->addTo($to);
			$mail->setSubject($subject);
			$mail->send($tr);
		} catch (Exception $e){
			echo $e;
		}   
    }
    
	public function asciiEncode($e)
    {
    	for ($i = 0; $i < strlen($e); $i++) { 
    		$output .= '&#'.ord($e[$i]).';'; 
    	}
    	return $output;
    }
}