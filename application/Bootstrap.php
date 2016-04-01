<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoload()
	{

	    error_reporting(E_ALL | E_STRICT);
	    ini_set('display_errors', true );
	    
	    $moduleLoader = new Zend_Application_Module_Autoloader(array(
	    		'namespace' => 'Application', // Or default
	    		'basePath' => APPLICATION_PATH //if APPLICATION_PATH is defined - else use 'basePath' => 'F:\xampp\htdocs\quickstart\application'
	    ));
  	}

  	protected function _initLogger()
  	{
  	    if ($this->hasPluginResource('log')) {
  	        $r = $this->getPluginResource('log');
  	        $log = $r->getLog();
  	        Zend_Registry::set('log', $log);
  	    }
  	}
  	
  	protected function _initActionHelpers() 
  	{
  		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers', 'Action_Helper');
  	}
    
    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }
}