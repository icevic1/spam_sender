<?php

class UserController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->view->pageTitle = 'Sender:';
        $this->view->fullUrl = $this->view->serverUrl() . $this->view->baseUrl();
    }
    
    public function loginAction()
    {
    	if (Zend_Auth::getInstance()->hasIdentity()) {
    		$this->_redirect($this->view->fullUrl.'/mail/');
    	}
//     echo md5('terminator');die;
    	$form = new Application_Form_Login();
    	$form->setAction($this->view->fullUrl.'/user/auth');
    	$return_url = $this->_getParam('return_url', null);

    	$form->getElement ( 'return_url' )->setValue ( $return_url );
    	$this->view->form = $form;
    }
    
    public function logoutAction()
    {
    	if (!Zend_Auth::getInstance()->hasIdentity()) {
    		$this->_redirect($this->view->fullUrl.'/user/login');
    	}
    	Zend_Auth::getInstance()->clearIdentity();
    	$this->_redirect($this->view->fullUrl.'/user/login'); // back to login page
    }
    
    public function authAction()
    {
    	$form = new Application_Form_Login();
    	$return_url = $this->_getParam('return_url', null);
    	
    	if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
    
    		$username = $this->_getParam('username', null);
    		$password = $this->_getParam('password', null);
    
    		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
//     		var_dump(md5('terminator'));die;

    		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
    
    		$authAdapter->setTableName('users')
	    		->setIdentityColumn('email')
	    		->setCredentialColumn('password');
    
    		$authAdapter->setIdentity($username)
    			->setCredential(md5($password));
    
    		$auth = Zend_Auth::getInstance();
    		$result = $auth->authenticate($authAdapter);
    
    		if ( $result->isValid() && $authAdapter->getResultRowObject()->id > 0) {
    			$userInfo = $authAdapter->getResultRowObject(null, 'password');  // se reseteaza parola
    			$authStorage = $auth->getStorage();
    			$authStorage->write($userInfo); // se inscrie in session

    			$seconds = 60 * 60 * 24 * 7; // 7 days
//     			$saveHandler = Zend_Session::getSaveHandler ();
//     			$saveHandler->setLifetime ( $seconds )->setOverrideLifetime ( true );

    			Zend_Session::rememberMe ( $seconds );
    
    			if ($return_url) {
    				$this->_redirect($return_url);
    			} else {
    				$this->_redirect($this->view->fullUrl.'/mail/'); //logare cu succes
    			}
    
    		} else {
    			$this->_redirect($this->view->fullUrl.'/user/login'.(($return_url)? '?return_url='.$return_url:''));
    		}
    	}
    	$this->_redirect($this->view->fullUrl.'/user/login'.(($return_url)? '?return_url='.$return_url:''));
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
    
}