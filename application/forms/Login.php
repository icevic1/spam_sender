<?php

class Application_Form_Login extends Zend_Form 
{

  public function init() {
    $this->setMethod('post');

    $username = $this->CreateElement('text', 'username')
        ->setFilters(array('StringTrim'))
		->addFilters(array('StringTrim', 'StripTags'))
    	->addValidator('EmailAddress', true)
        ->addValidator('stringLength', false, array(6, 100))
        ->setLabel('Email:')
        ->setRequired(true);

    $username->setDecorators(array(
      'ViewHelper',
      'Description',
      'Errors',
      array(array('data' => 'HtmlTag'), array('tag' => 'td')),
      array('Label', array('tag' => 'td')),
      array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    ));

    $password = $this->CreateElement('password', 'password')
        ->setFilters(array('StringTrim'))
        ->addValidator('StringLength', false, array(3,20))
        ->setRequired(true)
        ->setLabel('Password');

    $password->setDecorators(array(
      'ViewHelper',
      'Description',
      'Errors',
      array(array('data' => 'HtmlTag'), array('tag' => 'td')),
      array('Label', array('tag' => 'td')),
      array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    ));

    $submit = $this->CreateElement('submit', 'submit')->setLabel('Login');

    $submit->setDecorators(array(
      'ViewHelper',
      'Description',
      'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td',
          'colspan' => '2', 'align' => 'center')),
      array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    ));

    $hash = $this->CreateElement('hash', 'csrf')
    	->setIgnore(true)
    	->removeDecorator('label')
        ->removeDecorator('HtmlTag');

    $return_url = $this->CreateElement('hidden', 'return_url')
    	->setRequired(false)
    	->removeDecorator('label')
        ->removeDecorator('HtmlTag');

    $this->addElements(array(
      $username,
      $password,
      $submit,
     // $hash,
      $return_url
    ));

    $this->setDecorators(array(
      'FormElements',
      array(array('data' => 'HtmlTag'), array('tag' => 'table')),
      'Form'
    ));
  }

}