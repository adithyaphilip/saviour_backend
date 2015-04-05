<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
class Facebook_tools extends  CI_Model{
	/*
	 * Requires the following permissions:
	 * Basic: email, user_about_me, user_location, user_hometown
	 * Extended: email
	 * For publishing:
	 * publish_actions,publish_stream,share_item
	 * For eactra info:
	 * user_activities,user_likes,user_friends,user_hometown,user_location,user_interests,
	 */
	 //TODO remove $this->load->library('facebook'... in functions as added in constructor
	//CHANGE BELOW FOR ACTUAL DEPLOYMENT
	public $fbconfig = array(
		'appId' => '520540954730080',
		'secret' => 'e37882e5b773722f028ce0be332afb1b',
		'fileUpload' => false, // optional
		'allowSignedRequest' => false, //should be set to false for non-canvas apps
		);
	function __construct()
	{
		parent::__construct();
		$this->load->library('facebook',$this->fbconfig);
	}
	/**
	 * returns all the user details from facebook (obtainable using supplied fbtoken). Differs from getUserDetail as this function returns all user details
	 * permissions: 
	 * @param contains 'fbtoken' key which is the facebook token of the user whose details are to be requested
	 * @return the obtained details of the user
	 */
	function getUserDetails($fbtoken)//TODO
	{
		$this->facebook->setAccessToken($fbtoken);
		$response = $this->facebook->api('/me');
		return $respone;
	}
	/**
	 * returns the facebookid of a user associated with the given token
	 * @param $fbtoken facebook token of the user whose facebookid is to be obtained
	 * @return an array with an 'id' key whose value is the fbid of the user who has authorised the token
	 */
	function getUserId($fbtoken)
	{
		$this->facebook->setAccessToken($fbtoken);
		$response = $this->facebook->api('/me?fields=id');
		return $response['id'];
	}
	function getUserName($fbtoken)
	{
		$this->facebook->setAccessToken($fbtoken);
		$response = $this->facebook->api('/me?fields=name');
		echo "<br>TEST:",json_encode($response),"<br>";//TEST
		return $response;
	}
	/**
	 * returns a particular detail of a user as requested
	 * @param $params must contain 'fbtoken' key and 'fields' key (which is a comma separated collection of required fields). 
	 * @return response from request to facebook
	 */
	function getUserDetail($params)
	{
		if(!isset($params['fbtoken']))
			throw new Exception("fbtoken parameter not passed");
		$fbtoken = $params['fbtoken'];
		$fields = $params['fields'];
		$this->load->library('facebook',$this->fbconfig);
		$this->facebook->setAccessToken($fbtoken);
		$response = $this->facebook->api('/me?fields='.$fields);
		return $response;
	}
	/**
	 * required to post information to a user's facebook wall
	 * @param $param must contain 'fbtoken' and (optional) 'message' and 'link' params
	 * @return the response from the fb query
	 */
	function postToWall($params)
	{
		$fbtoken = $params['fbtoken'];
		//$msg = $params['message'];
		$this->load->library('facebook',$this->fbconfig);
		$this->facebook->setAccessToken($fbtoken);
		$request = array();
		foreach($params as $key=>$value)
		{
			switch($key)
			{
				case 'message':
				case 'link':
				$request[$key]=$value;
			}
		}
		$response = $this->facebook->api('/me/feed','POST',$request);
		return $response;
	}
	/**
	 * gets information required to post to a user's wall
	 * @return returns an array with message and link keys
	 */
	function getPostInfo()//TODO!
	{
		$response['message']="Vote for a future. Spread the awareness. Who do you think will win the next elections? Predict now and win an iPhone!";
		$response['link']='facebook.com';//TODO
		return $response;
	}
	/**
	 * posts with standard link and message to users wall
	 * @param array containing 'fbtoken' key, 'link' key
	 */
	function doStandardPost($params)//TODO!
	{
		$fbtoken = $params['fbtoken'];
		$info['fbtoken']=$fbtoken;
		$info['message']="Vote for a future. Spread the awareness. Who do you think will win the next elections? Predict now and win an iPhone!";
		$info['link']=$params['link'];//TODO
		return postToWall($info);
	}
	/**
	 * checks whether the user associated with a particular access token is verified or not
	 *
	 * @param $fbtoken fbtoken of user
	 * @return true or false if user is verified or not respectively
	 */
	 function isVerifiedUser($fbtoken)
	 {
	 	$request['fbtoken']=$fbtoken;
		$request['fields']='verified';
		$response = $this->getUserDetail($request);
		if(isset($response['verified']))
		{
			if($response['verified']=='true')
				return TRUE;
		}
		return FALSE;
	 }
	 /**
	  * For website-based login
	  * creates and return a login url to be used for allowing minimum access to the app (registration data). Does not contain publishing permissions.
	  * @param $param array containing 'redirect_uri' key which holds the address to which the login url should redirect
	  * @return required login url
	  */
	 function getLoginUrlForAccess($params)
	 {
	 	if(!isset($params['redirect_uri']))
			throw new Exception('redirect_uri not set');
		
	 	$perm = $this->getBasicPermissions();
	 	$pieces = $perm['permissions'];
	 	$scope = implode(',', $pieces);
	 	$login_params = array(
  		'scope' => $scope,
  		'redirect_uri' => $params['redirect_uri']
		);
		return $this->facebook->getLoginUrl($login_params);
	 }
	 /**
	  * For website based login
	  * creates and returns a login url which returns a token with permissions to publish (includes permission to view data also)
	  * @param $param array containing 'redirect_uri' key which holds the address to which the login url should redirect
	  * @return the required login url
	  */
	 function getLoginUrlForPublish($params)
	 {
	 	if(!isset($params['redirect_uri']))
			throw new Exception('redirect_uri not set');
		
	 	$perm = $this->getPublishingPermissions();
	 	$pieces = $perm['permissions'];
	 	$scope = implode(',', $pieces);
	 	$login_params = array(
  		'scope' => $scope,
  		'redirect_uri' => $params['redirect_uri']
		);
		return $this->facebook->getLoginUrl($login_params);
	 }
	 /**
	  * used to get permissions associated with basic login to register a new user and access features necessary for registration
	  * @return array of strings under 'permissions' key, each string a permission
	  */
	  public function getBasicPermissions()
	  {
	  	$permission['permissions'] = array('user_about_me', 'user_location','user_hometown', 'user_birthday', 'email', 'user_friends','read_friendlists');
		return $permission;
	  }
	  /**
	   * used to retrieve permisssion required to publish to a user's wall (includes basic permissions)
	   * @return array of strings under 'permissions' key, each string a permission
	   */	
	  function getPublishingPermissions()
	  {
	  	$permission['permissions'] = array('publish_actions','publish_stream','user_about_me', 'user_location','user_hometown', 'user_birthday', 'email', 'user_friends','read_friendlists');
	  	return $permission;
	  }
}
