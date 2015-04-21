<?php
class Pulse extends CI_Model{
	public $emergency_time_limit = 90;//in s (1 and half minutes) TODO
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Save');
	}
	/**
	 * params needs 'imei'
	 * returns whether their help is required or not
	 */
	function pulse($params)
	{
		//$this->routine_activities(); //no need as we're running a php script to do this?
		$req = array('imei'=>$params['imei']);
		$idresponse = $this->db->select('id')->get_where('userlist',array('imei'=>$params['imei']))->result_array();
		$params['id']=$idresponse[0]['id'];
		//state=>0 makes sure user has not been informed of emergency yet
		$response = $this->db->get_where('emergencies',array('aid'=>$params['id'],'state'=>'0'))->result_array();
		
		if(count($response)!=0)
		{
			$userresponse = $this->db->get_where('userlist',array('id'=>$response[0]['uid']))->result_array();
			$this->db->update('emergencies',array('state'=>'4'),array('id'=>$response[0]['id']));
			return array('emergency'=>'true','user'=>$userresponse[0]);
		}
		else {
			return array('emergency'=>'false');
		}
	}
	/*
	private function locationPulse($params)
	{
		$data = array('lx'=>$params['lx'],'ly'=>$params['ly'],'id'=>$params['id']);
		$this->db->where('id',$params['id'])->update('userlist',$data);
		return true;
	}*/
	/**
	 * params need lx, ly, imei
	 */
	 function locationPulse($params){
	 	$data = array('lx'=>$params['lx'],'ly'=>$params['ly'],'imei'=>$params['imei']);
		$this->db->where('imei',$params['imei'])->update('userlist',$data);
		return true;
	 }
	public function routine_activities()
	{
		//$this->db->delete('emergencies',array('state'=>3));
		$this->cleanUpEmergencies();
		$this->Save->setUnresponding();
		$response = $this->db->get('emergencies')->result_array();
		foreach($response as $emergency)
		{
			$this->Save->setNewSaviours(array('uid'=>$emergency['uid']));
		}
	}
	public function cleanUpEmergencies(){
		//should ideally find min(time) for removal calculation. But here emergencies are ordered in terms of arrivall to the table and therfore implicitly the earliest (the correct) entry is chosen as internally query will be ordering on the basis id (I think)
		//$emergencies = $this->db->distinct()->group_by('uid')->select('uid')->get_where('emergencies',array('time < '=>time()-$this->emergency_time_limit))->result_array();
		$emergencies = $this->db->select('uid')->get_where('emergencies',array('time < '=>time()-$this->emergency_time_limit))->result_array();
		
		for($i=0;$i<count($emergencies);$i++){
			$this->db->delete('emergencies',array('uid'=>$emergencies[$i]['uid']));
		}		
	}
}
