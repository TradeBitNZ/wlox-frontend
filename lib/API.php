<?php 
class API{
	private static $commands,$nonce,$token,$settings_change_id,$request_id;
	
	function add($classname,$method,$arguments=false) {
		API::$commands[$classname][][$method] = $arguments;
	}
	
	function token($token) {
		API::$token = $token;
	}
	
	function settingsChangeId($settings_change_id) {
		API::$settings_change_id = $settings_change_id;
	}
	
	function requestId($request_id) {
		API::$request_id = $request_id;
	}
	
	function send() {
		global $CFG;

		$commands['session_id'] = $_SESSION['session_id'];
		$commands['nonce'] = $_SESSION['nonce'];
		$commands['lang'] = $CFG->language;
		$commands['commands'] = json_encode(API::$commands);
		$commands['token'] = API::$token;
		$commands['settings_change_id'] = urlencode(API::$settings_change_id);
		$commands['request_id'] = API::$request_id;

		if (User::isLoggedIn()) openssl_sign($commands['commands'],$signature,$_SESSION['session_key']);
		$commands['signature'] = $signature;
		
		$ch = curl_init($CFG->api_url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$commands);
		
		$result1 = curl_exec($ch);
		//print_ar($result1);
		$result = json_decode($result1,true);
		//print_ar($result['session_id']);
		//print_ar($result['nonce']);
		//print_ar($result['commands']);
		curl_close($ch);
		User::updateNonce();
		
		API::$commands = array();
		return $result;
	}
}

?>