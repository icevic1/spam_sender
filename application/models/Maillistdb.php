<?php
class Application_Model_Maillistdb extends Zend_Db_Table
{
	public function __construct($id = null)
	{
		$this->_db = self::getDefaultAdapter();
	}

	public function searchByEmail($email = null)
	{
		if (!$email) return false;

		$select = $this->_db->select()
				->from('mail_list', array('*'))
				->where('email = ?', $email);

		return $this->_db->fetchRow($select);
	}

	/**
	 *
	 * @return array Fetches all SQL result rows as a sequential array.
	 */
	public function getList($excludeGmail = 0)
	{
		$select = $this->_db->select ()
			->from ( 'mail_list', array ('*' ) )
			->where ( 'is_sent = ?', '0' )
			->where ( 'unsubscribe = ?', '0' );
			//->where('email = ?', 'orletchi.victor@gmail.com')

		if ($excludeGmail)
			$select->where ( 'domain != ?', 'gmail.com' );

		$select->order('id desc')->limit(1);
		return $this->_db->fetchAll ( $select );
	}
	
	public function searchPaging($params = array())
	{
		$select = $this->_db->select ()->from ( 'mail_list', array ('*'))->order('id DESC');
		
		if (isset($params['email'])) $select->where('email LIKE ?', "%".$params['email']."%");
		if (isset($params['is_sent'])) $select->where ( 'is_sent = ?', $params['is_sent']);
		if (isset($params['unsubscribe'])) $select->where("unsubscribe = ?", $params['unsubscribe']);
		
// 		if (!is_null($limit)) $select->limit((int)$limit);

// 		print $select->__toString();die;
		
		$paginator = Zend_Paginator::factory($select);
		$paginator->setCurrentPageNumber($params['page']);
		$paginator->setItemCountPerPage(50);
		$paginator->setPageRange(9);
		 
		return $paginator;
	
// 		return $this->_db->fetchAll ( $select );
	}
	

	/**
  	 *
  	 * @return array Fetches all SQL result rows as a sequential array.
  	 */
  	public static function countUnsubscribe()
  	{

  		$sql = self::getDefaultAdapter()->select()
  				->from('mail_list', array('COUNT(*) as nr'))
  				->where('unsubscribe = ?', '1');

  		return self::getDefaultAdapter()->fetchOne($sql);
  	}

	/**
	 *
	 * @return array Fetches all SQL result rows as a sequential array.
	 */
	public static function countSent()
	{
		$sql = self::getDefaultAdapter ()->select ()
			->from ( 'mail_list', array ('COUNT(*) as nr' ) )
			->where ( 'is_sent = ?', '1' );

 		return self::getDefaultAdapter ()->fetchOne($sql);
	}

	/**
	 * Update the individual mail record
	 * @param array $values
	 * @return int The number of affected rows.
	 */
	public function updateRecord($values) {
		if (! $values)
			return false;
		$where = array ();
		if (! empty ( $values ['id'] ))
			$where = array ('id = ?' => $values ['id'] );

		return $this->_db->update ( 'mail_list', $values, $where );
	}

	/**
	 * Update unsubscribed flag
	 * @param array $email
	 * @return int The number of affected rows.
	 */
	public function unsubscribe($email) 
	{
		if (! $email)
			return false;

		return $this->_db->update ( 'mail_list', array ('unsubscribe' => '1' ), array ('email = ?' => $email ) );
	}
	
	public function add($email) {
		if (! $email) { return false;	}
		
		$expl = explode('@', $email);
		
		$this->_db->insert ( 'mail_list', array('email'=>$email, 'domain'=>$expl[1]));
		return $this->_db->lastInsertId ();
	}
	
	public function delete($id) {
	    if (!$id) return false;
		return $this->_db->delete ( 'mail_list', array ('id = ?' => $id) );
	}
	
	public function addStats($mailSubject, $mailMessage, $withOutHtml, $withSampleHtml) 
	{
		if (! $mailSubject || !$mailMessage) { return false;	}
	
		$this->_db->insert ( 'stats', array('mailSubject'=>$mailSubject, 'mailMessage'=>$mailMessage, 'withOutHtml'=>$withOutHtml, 'withSampleHtml'=>$withSampleHtml));
		return $this->_db->lastInsertId ();
	}
	
	public function updateStats($values)
	{
		if (!$values) return false;
	
		return $this->_db->update ( 'stats', $values, array ('id = ?' => $values['id'] ) );
	}
	
	public function getStatInfo($id = null)
	{
	    if (!$id) return false;
		$select = $this->_db->select ()
			->from ( 'stats', array ('*' ) )
			->where('id = ?', $id);
	
		return $this->_db->fetchRow( $select );
	}
	
	public function getStats()
	{
		$select = $this->_db->select ()
			->from ( 'stats', array ('*' ) )
			->order('created DESC');
			
	
		return $this->_db->fetchAll ( $select );
	}
	
}