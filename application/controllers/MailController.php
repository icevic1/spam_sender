<?php

class MailController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->view->pageTitle = 'Sender:';
        $this->view->fullUrl = $this->view->serverUrl() . $this->view->baseUrl();
        if (!Zend_Auth::getInstance()->hasIdentity()) {
        	$this->_redirect($this->view->fullUrl.'/user/login');
        }
    }

    public function indexAction()
    {
//         throw new Exception('suntem aici');
        
        $Maillistdb = new Application_Model_Maillistdb();
        
        $params = array();
        $params['controller'] = 'mail';
        $params['action'] = 'index';
        
        if ($this->_getParam('is_sent')) $params['is_sent'] = $this->_getParam('is_sent', '0');
        if ($this->_getParam('unsubscribe') ) $params['unsubscribe'] = $this->_getParam('unsubscribe', '0');
        
        if ($this->_getParam('email')) {
        	$params['email'] = trim($this->_getParam('email'));
        }
        
        $params['page'] = $this->_getParam('page', 1);

//         var_dump($params);die;
        
        $mailList = $Maillistdb->searchPaging($params);
        $this->view->listResult = $mailList;
        $this->view->params = $params;
        
        

    }

    public function composeAction()
    {
    	$mailListObj = new Application_Model_Maillistdb();

    	
    	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    	$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    	$protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    	echo $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
    	
//     	var_dump($_SERVER);die;
    	
    	set_time_limit(60*60*24*7); // set to 7 day
    	ini_set("memory_limit","512M");
    
    	$mailSubject = $this->_getParam('mailSubject', '');
    	$mailMessage = $this->_getParam('mailMessage', '');
    	$statId = $this->_getParam('statId', null);
    
    	$newSend = $this->_getParam('newSend', 0);
    	$withOutHtml = $this->_getParam('withOutHtml', 0);
    	$withSampleHtml = $this->_getParam('withSampleHtml', 0);
    
    	$testSend = $this->_getParam('testSend', 0);
    	$testMail = $this->_getParam('testMail', '');
    	
    	if ($statId && $testSend) $testSend = 0; 
    
    	$spamDb = $this->_getParam('spamDb', '300');
    
    	$mailForm = 'office@gamaavia.md';
    	$mailsList = array();
    
    	$mailsList = $mailListObj->getList();
    	//var_dump($mailsList);die;
    	$this->view->statInfo = $statInfo = $mailListObj->getStatInfo($statId);
    	
    	
    	if ($this->getRequest()->isPost()) {
    		$attaces = array (array ('path' => APPLICATION_PATH . '/../public/static/img/gama-avia-logo.jpg', 'cid' => 'img1' ));
    
    		if ($mailsList && $mailSubject && $mailMessage && $testSend == 0) {
    			shuffle($mailsList);
    			$counter = 0;
    			
    			if ($newSend || !$statId || ($statId && !$statInfo)) {
    				$statId = $mailListObj->addStats($mailSubject, $mailMessage, $withOutHtml, $withSampleHtml);
    				$mailListObj->updateRecord(array('is_sent'=>'0'));
    			}

    			foreach ($mailsList as $item) {
    
    				if ($withOutHtml) {
    					$body = strip_tags($mailMessage). PHP_EOL . "\r\n ------------------". PHP_EOL . 'Daca nu doriti sa mai primiti oferte de la GamaAvia te poti dezabona accesind adresa '.$this->view->fullUrl.'/user/unsubscribe/email/'.base64_encode($item['email']). PHP_EOL ;
    					$this->_helper->SendMail->send($mailForm, $item['email'], $mailSubject, $body);
    						
    				} elseif ($withSampleHtml) {
    					$body = $this->view->partial('/mail/mail-spam-template-samplehtml.phtml', array('bodyMessage'=>$mailMessage, 'encodedMail'=> base64_encode($item['email']), 'fullUrl'=>$this->view->fullUrl) );
    					$this->_helper->SendMail->send($mailForm, $item['email'], $mailSubject, $body);
    				} else {
    					$body = $this->view->partial('/mail/mail-spam-template.phtml', array('bodyMessage'=>$mailMessage, 'encodedMail'=> base64_encode($item['email']), 'fullUrl'=>$this->view->fullUrl) );
    					$this->_helper->SendMail->send($mailForm, $item['email'], $mailSubject, $body, $attaces);
    				}

    				$mailListObj->updateRecord(array('id'=>$item['id'], 'is_sent'=>'1'));
    				$mailListObj->updateStats(array('id'=>$statInfo['id'], 'sent_count'=>new Zend_Db_Expr('sent_count + 1')));
    				
    				$counter++;
    			}
//     			$this->view->setMessenger('Message successfully sent!-'.$counter);
    			$this->_redirect($this->view->fullUrl.'/mail/compose');
    
    		} elseif ($testSend && $testMail) {
    			if ($withOutHtml) {
    				$body = $mailMessage.' <br> ------------------ <br><p><em><small>Daca nu doriti sa mai primiti oferte de la GamaAvia te poti dezabona accesind adresa '.$this->view->fullUrl.'/user/unsubscribe/email/'.base64_encode($item['email']).'</small></em></p>';
    				$this->_helper->SendMail->send($mailForm, $testMail, $mailSubject, $body);
    			} elseif ($withSampleHtml) {
    				$body = $this->view->partial('/mail/mail-spam-template-samplehtml.phtml', array('bodyMessage'=>$mailMessage, 'encodedMail'=> base64_encode($testMail), 'fullUrl'=>$this->view->fullUrl) );
    				$this->_helper->SendMail->send($mailForm, $testMail, $mailSubject, $body);
    			} else {
    				$body = $this->view->partial('/mail/mail-spam-template.phtml', array('bodyMessage'=>$mailMessage, 'encodedMail'=> base64_encode($testMail), 'fullUrl'=>$this->view->fullUrl) );
    				$this->_helper->SendMail->send($mailForm, $testMail, $mailSubject, $body, $attaces);
    			}
    			
    			$this->_redirect($this->view->fullUrl.'/mail/compose');
    		} else {
    			
    			$this->_redirect($this->view->fullUrl.'/mail/compose');
    		}
    
    	}
    	$this->view->mailsList = $mailsList;
    	$this->view->mailFrom = $mailForm;
    	$this->view->mailTeme = $mailSubject;
    	$this->view->mailBody = $mailMessage;
    
    	$this->view->headScript ()->appendFile ($this->view->fullUrl. '/static/ckeditor/ckeditor.js', 'text/javascript');
    	$this->view->headScript ()->appendFile ($this->view->fullUrl. '/static/ckfinder/ckfinder.js', 'text/javascript');
    }
    
    public function addAction()
    {
        $mailObj = new Application_Model_Maillistdb();
        
    	if($this->getRequest()->isPost()) {
    	    $validator = new Zend_Validate_EmailAddress ();
    	    $email = $this->_getParam('email', null);
    	    
    	    if($validator->isValid($email )) {
    	        $mailObj->add($email);
    	        $this->_redirect($this->view->fullUrl.'/mail/');
    	    } else {
    	        $this->_redirect($this->view->fullUrl.'/mail/add');
    	    }
    	}
    
    	$this->view->pageTitle = 'Add email';
    }
    
    public function deleteAction()
    {
    	$id = $this->_getParam('id', null);
    	$mailObj = new Application_Model_Maillistdb();
    
    	$mailObj->delete($id);
    	    
    	$this->_redirect($this->view->fullUrl.'/mail/');
    }
    
	public function unsubscribeAction()
	{
		$validator = new Zend_Validate_EmailAddress ();
		$mailListObj = new Application_Model_Maillistdb();

		$email = $this->_getParam('email', null);
		
		if ($this->getRequest()->isPost() == false) {
		    $email = base64_decode($email);
		}

		if ($validator->isValid($email )) {
			if ($mailListObj->unsubscribe($email)) {
				$this->view->replayMessage = "Your email address was successfully removed from newsletter subscribers list!";
			} else {
				$this->view->replayMessage = "Your email address has already been removed!";
			}
		} 
	}
    
    
    public function importAction()
    {
    	set_time_limit(60*60*24); // set to 24h
    	ini_set("memory_limit","512M");
    
    	$db = Zend_Db_Table::getDefaultAdapter();
    	$validator = new Zend_Validate_EmailAddress ();
    	$mailListObj = new Application_Model_Maillistdb();
    	$this->view->outMesage = '';
    	
    	if ($this->getRequest()->isPost()) {
    		try {
	    	    $upload = new Zend_File_Transfer_Adapter_Http();
	    	    
// 	    	    $upload->addValidator('Extension', false, 'csv');
	    		//$adapter->addFilter('Rename',array('target' => WWW_ROOT . '/photos/' . $this->memberId . '.jpg'));
	    		
    	
    			if ($upload->isValid()) {
    			    $upload->setDestination(APPLICATION_PATH . '/../public/static/upload/');
    			    if ($upload->receive ()) {
    			    
	    			    $uploadFileName = $upload->getFileName ( 'sourceFile', false );
	    			    
	    			    $filepath = $upload->getDestination() .'/'.$uploadFileName;
	    			    
	    			    $arrFoud = $invalidFound = array();
	    			    
	    			    $lines = file($filepath);
	    			    $i = 0;
	    			    $out = array();
	    			    
	    			    foreach($lines as $line_num => $row) {
	    			    	// preg match all in the string
	    			    	preg_match_all("/([A-Za-z0-9\.\-\_\!\#\$\%\&\'\*\+\/\=\?\^\`\{\|\}]+)\@([A-Za-z0-9.-_]+)(\.[A-Za-z]{2,5})/", $row, $emails);
	    			    	
	    			    	// remove duplicate emails
	    			    	$uniq_emails = array_unique($emails[0]);
	    			    	
	    			    	// echo out the emails
	    			    	foreach ($uniq_emails as $key => $email) {
	    			    	    
	    			    	    if($validator->isValid($email )) {
	    			    	    	$emExpl = explode('@', $email);
	    			    	    
	    			    	    	$db->query('INSERT OR IGNORE INTO `mail_list` (`email`, `domain`) VALUES (?, ?)', array($email, $emExpl[1]));

	    			    	    	$i++;
	    			    	    }
	    			    	}
	    			    }
    			    }
    			} else {
    			    $this->view->outMesage = 'Errors:'.json_encode($upload->getMessages());
    			}	
    				
    		} catch ( Zend_File_Transfer_Exception $e ) {
    			echo $e->getMessage ();
    		}
    		
    		$this->view->outMesage = 'Added new emails: '.$i;
    	}
    	
    }
    
    public function statListAction()
    {
    	$Maillistdb = new Application_Model_Maillistdb();
    	//var_dump($params);die;
    	$statList = $Maillistdb->getStats();
    	$this->view->listResult = $statList;
    }
    
}