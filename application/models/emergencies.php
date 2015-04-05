<?php
class Emergencies extends CI_Model{
	public $id;
	public $uid;
	public $aid;
	public $state;
	public $time;
	public function __construct()
	{
		parent::__construct();
		$this->load->database();		
		$this->load->library('paramchecker');
	}
	
	public function newEntry($params)
	{
		if(!isset($params['id']))
			$params['id'] = $this->getLastId()+1;
		if($this->isParamsSet($params))
		{
			foreach($params as $key=>$val)
			{
				switch($key)
				{
					case 'id':
					case 'uid':
					case 'aid':
					case 'state':
					case 'time':
					$this->$key=$val;
				}
			}
			$this->db->insert('emergencies',get_object_vars($this));
			return (get_object_vars($this));
		}		
		else{
			throw new Exception('Failed to pass or generate one of: '.implode(',', $this->getRequiredParams()));
		}
	}
	/**
	 * params has uid (userid) of victim, 'aids' array (userid) of saviours assigned, state of currently assigned saviours
	 */
	public function newEmergency($params)
	{
		//if($this->existsByUid($params))
		//{
		//	throw new Exception('User already registered as victim of emergency');
		//}
		$req = array('uid','aids','state','time');
		if($this->paramchecker->isParamsSet($req,$params))
		{
			$uid = $params['uid'];
			$aids = $params['aids'];
			$state=$params['state'];
			$time=$params['time'];
			
			foreach($aids as $aid)
			{
				$this->newEntry(array('uid'=>$uid,'aid'=>$aid,'state'=>$state,'time'=>time()));
			}
		}
		else{
			throw new Exception("One of these not set : ".implode(',', $req));
		}
	}
	/**
	 * params conatins 'uid' (userid) of victim
	 */
	public function existsByUid($params)
	{
		if(count($this->db->get_where('emergencies',array('uid'=>$params['uid']))->result_array())>0)
		{
			return true;
		}
		return false;
	}
	/**
	 * params has id (userid) of victim, 'aid' of new person assigned, 'oaid' of person to be removed
	 */
	public function updateEmergency($params)//TODO, effectiveness in doubt
	{
		
	}
	/**
	 * params has 'aid','uid' and 'state'. Or a 'where' and 'state' combination
	 * returns null
	 */
	public function updateEmergencyState($params)
	{
		if(isset($params['where']) && isset($params['state'])){
			$this->db->update('emergencies',array('state'=>$params['state']),$params['where']);
			return $this->db->affected_rows();
		}
		//in case of aid, uid and state combination Eg:- to change the state of only a particular user
		$req=array('aid','uid','state');
		if($this->paramchecker->isParamsSet($req,$params)){
			$where = array();
			foreach($req as $key)
			{
				switch($key)
				{
					case 'aid':
					case 'uid':
					$where[$key]=$params[$key];
				}
			}
			$this->db->update('emergencies',array('state'=>$params['state'],'time'=>time()),$where);
			return $this->db->affected_rows();
		}
		else{
			throw new Exception('One of following not set : '.implode(',', $req));
		}
	}
	/**
	 * params may contain any combination of id (emergency id), uid, aid, time or state. Must NOT contain other params.
	 * may directly contain a 'where' array map parameter which causes it to ignore everything else
	 * returns number of rows deleted
	 */
	public function deleteEmergency($params)
	{
		if(isset($params['where']))
		{
			$this->db->delete('emergencies',$params['where']);
			return "";
		}
		$pos = array('id','uid','aid','state','time');
		$where = array();
		foreach($params as $key=>$val)
		{
			if(in_array($key, $pos))
			{
				$where[$key]=$val;
			}
		}
		$this->db->delete('emergencies',$where);
		return $this->db->affected_rows();
	}
	public function getLastId()
	{
		$response = $this->db->select_max('id')->get('emergencies')->result_array();
		return $response[0]['id'];
	}
	public function isParamsSet($params)
	{
		$req = $this->getRequiredParams();
		return $this->paramchecker->isParamsSet($req,$params);
	}
	public function getRequiredParams()
	{
		$arr = get_object_vars($this);
		$result= array();
		foreach($arr as $key=>$value)
		{
			$result[] = $key;
		}
		return $result;
	}
}
