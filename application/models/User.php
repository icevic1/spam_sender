<?php
class Application_Model_User
{
	
	public $id = null;
	public $username = null;
	public $real_name = null;
	public $isEnabled = null;
	public $created = null;
	public $contentId = null;
	public $displayMenu = null;
	
	protected $_db = null;
	
	public function __construct($id = null) {
		parent::__construct($id);
		$this->id = $id;
	}
  
	public function load() 
	{
		if ($this->id && ($userInfo = $this->get($this->id, $convertToObject = true))) {
			return true;
		} else {
			$this->id = null;
			return false;
		}
	}
	
	/**
  	 * Get primary user info
  	 * @param int $userId
  	 * @return array 
  	 */
	public function get($userId = null, $convertToObject = false) 
  	{
  		if (!$userId) return false;
		$select = $this->db->select()
				->from('users', array('*'))
				->where('id = ?', $userId);
				
		return $this->fetchRow($select, $convertToObject);
  	}
  	
	/**
     * Add new cms user
     * @param array $values
     */
	public function addUser($values) 
    {
    	$this->db->insert('users', $values);
    	return $this->db->lastInsertId();
    }
    
	/**
     * Edit loaded user
     * @param array $values
     */
	public function edit($values) 
    {
    	if(!$this->id) { return false; }
    	
    	return $this->db->update('users', $values, array('id = ?' => $this->id));
    }
  	
	public function getUserAclRoles() 
  	{
  		if (!$this->id) return false;
		$select = $this->db->select()
				->from(array('r' => 'roles'), array('id as roleId', 'name as roleName'))
				->join(array('ur'=>'user_role'), 'r.id = ur.role_id', array('user_id as id'))
				->where('user_id = ?', $this->id);
				
		return $this->db->fetchAll($select);
  	}
  	
	public function getAclRole($roleId) 
  	{
  		if (!$roleId) return false;
		$select = $this->db->select()
				->from('roles', array('id as roleId', 'name as roleName'))
				->where('id = ?', $roleId);
				
		return $this->fetchRow($select);
  	}
  	
	public function getAclRoles() 
  	{
		$select = $this->db->select()
				->from('roles', array('id as roleId', 'name as roleName'));
				
		return $this->db->fetchAll($select);
  	}
  	
	public function getAclRolePrivileges($roleId = null) 
  	{
  		if (!$roleId) return false;
		$select = $this->db->select()
				->from('privileges', array('id', 'role_id as roleId', 'controller', 'action'))
				->where('role_id = ?', $roleId);
		return $this->db->fetchAll($select);
  	}
  	
	public function checkAcllRolePrivilege($roleId = null, $controller = null, $action = null) 
  	{
  		if (!$roleId || !$controller || !$action) return false;
  		
		$select = $this->db->select()
				->from('privileges', array('id', 'role_id as roleId', 'controller', 'action'))
				->where('role_id = ?', $roleId)
				->where('controller = ?', $controller)
				->where('action = ?', $action);
		return $this->db->fetchRow($select);
  	}
  	
	/**
  	 * Add a new comment to news
  	 * @param array $values
  	 * @return int lastInsertId()
  	 */
	public function addPrivilege($values) 
	{
    	if(!$values) { return false; }
    	$this->db->insert('privileges', $values);
    	return $this->db->lastInsertId();
  	}
  	
	public function addUserRole($roleId = null) 
	{
    	if(!$this->id || !$roleId) { return false; }
    	
    	$this->db->insert('user_role', array('user_id' => $this->id, 'role_id' => $roleId));
    	return $this->db->lastInsertId();
  	}
  	
	/**
  	 * Add a new comment to news
  	 * @param array $values
  	 * @return int lastInsertId()
  	 */
	public function deletePrivilege($id = null, $roleId = null, $controller = null, $action = null) 
	{
		$where = array();
    	if($id) { $where['id = ?'] = $id; }
		if($roleId) { $where['role_id = ?'] = $roleId; }
		if($controller) { $where['controller = ?'] = $controller; }
		if($action) { $where['action = ?'] = $action; }
    	
		if ($where) {
			return $this->db->delete('privileges', $where);
		}
    	return false;
  	}
  	
	public function deleteUserAclRoles($roleId = null) 
	{
    	if(!$this->id) return false;
    	$where = array();
    	
    	$where['user_id = ?'] = $this->id;
		if($roleId ) { $where['role_id = ?'] = $roleId; }
    	
		return $this->db->delete('user_role', $where);
  	}
  
	/**
  	 * Get last registred user
  	 * @param int $limit
  	 * @return array 
  	 */
	public function getUsersList() 
  	{
		$select = $this->db->select()->from('users', array('*'));
				
		return $this->fetchAll($select, 'User');
  	}
  	
	/*
     * Add or Edit user role
     * @param int $roleId
     * @param string $roleName
     * @return int lastinsertid
     */
	public function saveRole($roleId = null, $roleName = null) 
    {
    	if(!$roleName) return false;
    	
    	$sql = "INSERT INTO `roles` SET `id` = ?, `name` = ? 
				ON DUPLICATE KEY UPDATE	`name` = ? ";
						
		$this->db->query($sql, array($roleId, $roleName, $roleName));

		return $this->db->lastInsertId();
    }
	
    /**
     * Delete atached content by user, contentId or contentType
     * @param int $contentId
     * @param int $contentType
     * @return int The number of rows deleted
     */
	public function deleteUserContents($contentId = null, $contentType = null) 
	{
    	if(!$this->id) return false;
    	
    	$where = array();
    	
    	$where['userId = ?'] = $this->id;
    	
    	if($contentId) $where['contentId = ?'] = $contentId;
		if($contentType) $where['contentType = ?'] = $contentType;
    	
		return $this->db->delete('users_contents', $where);
  	}
  	
	/**
     * Ataches content to user by contentId and contentType
     * @param int $contentId
     * @param int $contentType
     * @return int lastinsertid
     */
	public function addUserContent($contentId, $contentType) 
	{
    	if(!$this->id || !$contentId || !$contentType) return false;
    	
    	$this->db->insert('users_contents', array('userId' => $this->id, 'contentId' => $contentId, 'contentType'=>$contentType));
    	
    	return $this->db->lastInsertId();
  	}
  	
	/**
  	 * Get last registred user
  	 * @param int $limit
  	 * @return array 
  	 */
	public function getUserContents($userId = null, $contentId = null, $contentType = null, $fetchCol = false) 
  	{
  		$cols = array('*');
  		if ($fetchCol) {
  			$cols = array('contentId');
  		}
  		
		$sql = $this->db->select()
			->from('users_contents', $cols)
			->order('contentType ASC');

		if (!is_null($userId)) $sql->where('userId = ?', $userId);
		elseif ($this->id && is_null($userId)) $sql->where('userId = ?', $this->id);
		
		if (!is_null($contentId)) $sql->where('contentId = ?', $contentId);
		if (!is_null($contentType)) $sql->where('contentType = ?', $contentType);
		
		if ($fetchCol) return $this->db->fetchCol($sql, 'contentId');
		
		return $this->db->fetchAll($sql);
  	}
}