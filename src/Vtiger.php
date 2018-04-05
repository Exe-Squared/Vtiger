<?php 
namespace Clystnet\Vtiger;

use Storage;
use Config;

class Vtiger
{
	protected $url;
    protected $username;
    protected $accesskey;

	public function __construct() {
        // set the API url and username
        $this->url = Config::get('vtiger.url');
        $this->username = Config::get('vtiger.username');
        $this->accesskey = Config::get('vtiger.accesskey');
    }

	protected function sessionid() 
	{
		// session file exists
		if(Storage::disk('local')->exists('session.json')) {
			$json = json_decode(Storage::disk('local')->get('session.json'));

			if($json->expireTime < time() || empty($json->token)) {
				$updated = self::gettoken();

				$json = (object) $updated;

				Storage::disk('local')->put('session.json', json_encode($json));
			}
		}
		else {
			$updated = self::gettoken();

			$json = (object) $updated;

			Storage::disk('local')->put('session.json', json_encode($json));	
		}

		$token = $json->token;

		// Create unique key using combination of challengetoken and accesskey
		$generatedkey = md5($token . $this->accesskey);

		$post = array(
			'operation' => 'login', 
			'username' => $this->username, 
			'accessKey' => $generatedkey
		);

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// decode the response
        $login_result = json_decode($result);

        // If api login failed
		if($httpcode !== 200 || !$login_result->success) {
			return json_encode(array(
    			'success' => false,
    			'message' => $login_result->error->message
    		));
		}

		// login ok so get sessionid
		$sessionid = $login_result->result->sessionName;

		return $sessionid;
	}

	protected function gettoken() 
	{
		$bool = false;

		do {
			$ch = curl_init(); 

			curl_setopt($ch, CURLOPT_URL, $this->url . "?operation=getchallenge&username=" . $this->username);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//curl_setopt($ch, CURLOPT_HEADER, false); 

			$result = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			if(curl_error($ch)) {
			    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
			}

			curl_close($ch);

	        // decode the response
			$challenge = json_decode($result);

			// If challenge failed
			if($httpcode === 200 || $challenge->success) {
				$bool = true;
			}
		}
		while(!$bool);

		$json = array(
			'token' => $challenge->result->token,
			'expireTime' => $challenge->result->expireTime
		);

		return $json;
	}

	protected function close($sessionid)
	{
		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $this->url . "?operation=logout&sessionName=" . $sessionid);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false); 

		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// decode the response
		$data = json_decode($result);

		return $data;
	}

	public function query($query)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $this->url . "?operation=query&sessionName=" . $sessionid . "&query=" . urlencode($query));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false); 

		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if(curl_error($ch)) {
		    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}

		curl_close($ch);

		// decode the response
		$data = json_decode($result);

		self::close($sessionid);

		return $data;
	}

	public function retrieve($id)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $this->url . "?operation=retrieve&sessionName=" . $sessionid . "&id=" . $id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false); 

		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// decode the response
		$data = json_decode($result);

		self::close($sessionid);

		return $data;
	}

	public function create($elem, $data)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		$post = array(
			'operation' => 'create',
			'sessionName' => $sessionid,
			'element' => $data,
            'elementType' => $elem
		);

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// decode the response
		$data = json_decode($result);

		self::close($sessionid);

		return $data;
	}

	public function update($object)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		// send a request using a database query to get back any user with the email from the form POST request 
		$post = array(
            'operation' => 'update', 
            'sessionName' => $sessionid,
            'element' => json_encode($object),
        );

        $ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// decode the response
		$data = json_decode($result);

		self::close($sessionid);

		return $data;
	}
}