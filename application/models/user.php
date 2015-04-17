<?php
class User extends CI_Model{
	public  $name='null';
	public  $email='none';
	public  $pass='';
	public  $age;
	public  $gender='';
	public  $fbid;
	public $ly;
	public $lx;
	public $ready;
	public $fit;
	public $vehicle;
	public $slot;
	function __construct()
	{
		parent::__construct();
		$this->load->library('paramchecker');
		$this->load->database();
	}
	/**
	 * params must contain (check isParamsSet for now. Also consider values automatically assigned in function)
	 */
	public function newEntry($params)//TODO enable user registration without facebook by 
	{
		//TODO these parameters are being automatically set until future releases include them
		$params['fbid']=-1;
		$params['email']=-1;
		$params['fit']=-1;
		$params['vehicle']=-1;
		$params['slot']=-1;
		$params['ready']=-1;
		$params['reqid']=-1;
		$params['pass']=$this->randomPassword();
		//id auto-incrementing handled by table
		if($this->isParamsSet($params))
		{
			$usresponse = $this->db->get_where('userlist',array('imei'=>$params['imei']))->result_array();
			if(count($usresponse)>0){
                            $data;
                            foreach($this->getRequiredParams() as $key){
                                    $data[$key]=$params[$key];
                            }
                            $this->db->where('imei',$params['imei'])->update('userlist',$data);
                            return array('user'=>$usresponse[0]);
			}
			$data;
			foreach($this->getRequiredParams() as $key){
				$data[$key]=$params[$key];
			}
			$this->load->database();
			$this->db->insert('userlist', $data);
			
			$response = $this->db->get_where('userlist',array('imei'=>$params['imei']))->result_array();
			return array('user'=>$response[0]);
		}
		else
		{
			throw new Exception('Insufficient parameters to create user');
		}
	}
	/**
	 * Used to create a new user from facebook. Can obtain email, city, age, gender, fbid, pass,fbtoken. Rest must be included in $params.
	 * Throws exception if user with same fbid already exists in database.
	 * permission for token: email, about_me, user_location, user_hometown
	 * @param $params should contain 'fbtoken' key with a valid token as its value
	 * @return true if successful
	 */
	public function newEntryFromFacebook($params)//TODO
	 {
	 	$this->load->model('Facebook_tools');
		
	 	$exist_params['fbid'] = $this->Facebook_tools->getUserId($params['fbtoken']);
	 	if($this->existsUsingFacebook($exist_params))
			throw new Exception("User already exists in database");
		
		$this->load->model('Facebook_tools');
		$details['fbtoken']=$params['fbtoken'];
		$details['fields']='email,id,gender,name,birthday';
		$req_params = explode(',',$details['fields']); 	
		$response = $this->Facebook_tools->getUserDetail($details);
						
		//check if email was returned
		foreach($req_params as $p)
		{
			if(!isset($response[$p]))
				throw new Exception("$p not present in response. Check facebook permissions.");
		}
			
		//at this stage, parameter presence is confirmed
		$params['fbid']=$response['id'];
		//$city = explode(",",$response['hometown']['name']);//splits the string with ',' delimiter so that value at first index in split string which ist he city can be used
		//$params['city']= $city[0];
		$params['name']=$response['name'];
		$params['gender']=$response['gender'];
		$params['email']=$response['email'];
		$this->load->library('tools');
		$params['age']=	$this->tools->getAgeFromDob($response['birthday']);
		$params['pass']=$this->randomPassword();
		
		//affid and predpos and preddone is taken care of in newEntry function
		return $this->newEntry($params);
	 }/**
 	 * tells you if a user exists or not based on his facebook id
	 * @param ana array which contains 'fbid' key
	 * @return true if user with given 'fbid' exists, else false.
	 * 	 
 	 */
	public function existsUsingFacebook($params)
	{
		if(isset($params['fbid']))
		{
			$this->load->database();
			if($this->db->get_where('userlist',array('fbid'=>$params['fbid']))->num_rows()>0)
			{
				return TRUE;
			}
			return FALSE;
		}
		else{
			throw new Exception('FBID_PARAM_NOT_SET');
		}
	}
	/**
	* Checks if the required parameters are set in the argument passed
	* @param $params params to be checked
	* @return true if required parameters are defined in argument, false otherwise
	*/
	private function isParamsSet($params)
	{
		//required values
		$required = $this->getRequiredParams();
		return $this->paramchecker->isParamsSet($required,$params);
	}
	/**
	* returns greatest id in db, typically to auto-increment the id column
	* @return greatest id value in db
	*/
	private function getLastId()
	{
		$this->load->database();
		$this->db->select_max('id');
		$query = $this->db->get('userlist');
		return $query->row()->id;//only one row should be present, else duplicates will occur
	}
	function getRequiredParams()
	{
		return array('name','lx','ly','age','gender','imei','gcmregid');
	}
		/**
	 * generates and return a random password consisting of characters defined by ASCII codes 48 to 122 (except 96)
	 * @param $length length of password to be generated
	 * @return generated password
	 */
	private function randomPassword()//REMOVE PARAMS
	{
		$length=12;
		$p='';
		for($i=0;$i<$length;$i++)
		{
			$decide = mt_rand(0,2);
			switch($decide)
			{
				case 0:
					$n=mt_rand(48,57); 
					break;
				case 1:
					$n=mt_rand(65,90);
					break;
				case 2:
					$n=mt_rand(97,122);
					break;
			}
			if($n==96||$n==91)//remove
			$n++;
			$c=chr($n);
			$p=$p.$c;
		}
		return $p;
	}
}
