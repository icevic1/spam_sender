<?php

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
//     	echo $this->view->serverUrl() . $this->view->baseUrl();die;
//  echo $this->view->url(array('controller'=>'mail', 'action' => 'add'), 'default', true);die;
        if (Zend_Auth::getInstance()->hasIdentity() == false) {
            $this->_redirect($this->view->serverUrl() . $this->view->baseUrl().'/user/login');
        } else {
            $this->_redirect($this->view->serverUrl() . $this->view->baseUrl().'/mail/');
        }
    }

}