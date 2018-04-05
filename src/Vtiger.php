<?php 
namespace Clystnet\Vtiger;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Storage;
use Config;

class Vtiger
{
	protected $url;
    protected $username;
    protected $accesskey;
    protected $client;

	public function __construct() {
        // set the API url and username
        $this->url = Config::get('vtiger.url');
        $this->username = Config::get('vtiger.username');
        $this->accesskey = Config::get('vtiger.accesskey');

        $this->client = new Client(['http_errors' => false, 'verify' => false]); //GuzzleHttp\Client
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

		// login using username and accesskey
		$response = $this->client->request('POST', $this->url, [
		    'form_params' => [
		       	'operation' => 'login', 
				'username' => $this->username, 
				'accessKey' => $generatedkey
		    ]
		]);

		// decode the response
        $login_result = json_decode($response->getBody()->getContents());

        // If api login failed
		if($response->getStatusCode() !== 200 || !$login_result->success) {
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
			// perform API GET request
	        $response = $this->client->request('GET', $this->url, [
	        	'query' => [
	        		'operation' => 'getchallenge',
	        		'username' => $this->username
	        	]
	        ]);

	        // decode the response
			$challenge = json_decode($response->getBody());

			// If challenge failed
			if($response->getStatusCode() === 200 || $challenge->success) {
				$bool = true;
			}
		}
		while(!$bool);

		// Everything ok so create a token from response
		$json = array(
			'token' => $challenge->result->token,
			'expireTime' => $challenge->result->expireTime
		);

		return $json;
	}

	protected function close($sessionid)
	{
		// send a request using a database query to get back any user with the email from the form POST request 
		$response = $this->client->request('GET', $this->url, [
			'query' => [
				'operation' => 'logout',
				'sessionName' => $sessionid
			]
		]);

		// decode the response
		$data = json_decode($response->getBody()->getContents());

		return $data;
	}

	public function query($query)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		// send a request using a database query to get back any user with the email from the form POST request 
		$response = $this->client->request('GET', $this->url, [
			'query' => [
				'operation' => 'query',
				'sessionName' => $sessionid,
				'query' => $query
			]
		]);

		// decode the response
		$data = json_decode($response->getBody()->getContents());

		self::close($sessionid);

		return $data;
	}

	public function retrieve($id)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		// send a request using a database query to get back any user with the email from the form POST request 
		$response = $this->client->request('GET', $this->url, [
			'query' => [
				'operation' => 'retrieve',
				'sessionName' => $sessionid,
				'id' => $id
			]
		]);

		// decode the response
		$data = json_decode($response->getBody()->getContents());

		self::close($sessionid);

		return $data;
	}

	public function create($elem, $data)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		// send a request using a database query to get back any user with the email from the form POST request 
		$response = $this->client->request('POST', $this->url, [
			'form_params' => [
				'operation' => 'create',
				'sessionName' => $sessionid,
				'element' => $data,
                'elementType' => $elem
			]
		]);

		// decode the response
		$data = json_decode($response->getBody()->getContents());

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
		$response = $this->client->request('POST', $this->url, [
			'form_params' => [
                'operation' => 'update', 
                'sessionName' => $sessionid,
                'element' => json_encode($object),
            ]
        ]);

        // decode the response
		$data = json_decode($response->getBody()->getContents());

		self::close($sessionid);

		return $data;
	}
}