<?php 
namespace Clystnet\Vtiger;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Mockery\CountValidator\Exception;
use Storage;
use Config;

class Vtiger
{
	protected $url;
	protected $username;
	protected $accesskey;
	protected $client;
	protected $retry;
	protected $maxretry;

	public function __construct() {
		// set the API url and username
		$this->url = Config::get('vtiger.url');
		$this->username = Config::get('vtiger.username');
		$this->accesskey = Config::get('vtiger.accesskey');

		$this->client = new Client(['http_errors' => false, 'verify' => false]); //GuzzleHttp\Client

		$this->retry = true;
		$this->maxretry = 10;
   	}

   	// user can pass other connection parameters to override the contructor
   	public function connection($url, $username, $accesskey)
   	{
   		$this->url = $url;
   		$this->username = $username;
   		$this->accesskey = $accesskey;

   		return $this;
   	}

	protected function sessionid() 
	{
		$tries = 0;
		$keep = true;

		while($keep && $tries < $this->maxretry) {

			// session file exists
			if(Storage::disk('local')->exists('session.json')) {
				$json = json_decode(Storage::disk('local')->get('session.json'));

				if(isset($obj) && property_exists($json, 'expireTime') && property_exists($json, 'token')) {

					if($json->expireTime < time() || empty($json->token)) {
						$json = $this->storesession();
					}
				}
				else {
					$json = $this->storesession();
				}
			}
			else {
				$json = $this->storesession();
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

				$keep = $this->retry;

				if(!$login_result->success && $keep) {
					if($login_result->error->code == "INVALID_USER_CREDENTIALS" || $login_result->error->code == "INVALID_SESSIONID") {
						$tries++;

						if (Storage::disk('local')->exists('session.json')) {
							Storage::disk('local')->delete('session.json');
						}
						continue;
					}
				}

				if ($response->me) {
					return json_encode(array(
		    			'success' => false,
		    			'message' => $login_result->error->message
		    		));
				}
			}

			// login ok so get sessionid
			$sessionid = $login_result->result->sessionName;

			return $sessionid;
		}
	}

	protected function storesession()
	{
		$updated = self::gettoken();

		$json = (object) $updated;

		Storage::disk('local')->put('session.json', json_encode($json));

		return $json;
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
		// send a request to close current connection
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

		for($i = 0; (!isset($data->success) && $i < 10); $i++) {
			// send a request using a database query to get back any matching records
			$response = $this->client->request('GET', $this->url, [
				'query' => [
					'operation' => 'query',
					'sessionName' => $sessionid,
					'query' => $query
				]
			]);

			// decode the response
			$data = json_decode($response->getBody()->getContents());
		}

		self::close($sessionid);

		return (isset($data->success)) ? $data : false;
	}

	public function retrieve($id)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		for($i = 0; (!isset($data->success) && $i < 10); $i++) {
			// send a request to retrieve a record
			$response = $this->client->request('GET', $this->url, [
				'query' => [
					'operation' => 'retrieve',
					'sessionName' => $sessionid,
					'id' => $id
				]
			]);

			// decode the response
			$data = json_decode($response->getBody()->getContents());
		}

		self::close($sessionid);

		return (isset($data->success)) ? $data : false;
	}

	public function create($elem, $data)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		for($i = 0; (!isset($data->success) && $i < 10); $i++) {
			// send a request to create a record
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
		}

		self::close($sessionid);

		return (isset($data->success)) ? $data : false;
	}

	public function update($object)
	{
		$sessionid = self::sessionid();

		if(isset($sessionid->success)) {
			return $sessionid->message;
		}

		for($i = 0; (!isset($data->success) && $i < 10); $i++) {
			// send a request to update a record
			$response = $this->client->request('POST', $this->url, [
				'form_params' => [
	                'operation' => 'update', 
	                'sessionName' => $sessionid,
	                'element' => json_encode($object),
	            ]
	        ]);

	        // decode the response
			$data = json_decode($response->getBody()->getContents());
		}

		self::close($sessionid);

		return (isset($data->success)) ? $data : false;
	}

	public function delete($id)
	{
		$sessionid = self::sessionid();

		if (isset($sessionid->success)) {
		    return $sessionid->message;
		}

		for ($i = 0; (!isset($data->success) && $i < 10); $i++) {
			// send a request to delete a record
		    $response = $this->client->request('GET', $this->url, [
				'query' => [
				    'operation' => 'delete',
				    'sessionName' => $sessionid,
				    'id' => $id
				]
		    ]);

			// decode the response
		    $data = json_decode($response->getBody()->getContents());
		}

		self::close($sessionid);

		return (isset($data->success)) ? $data : false;
	}
	
	public function describe($elementType)
	{
		$sessionid = self::sessionid();

		if (isset($sessionid->success)) {
		    return $sessionid->message;
		}

		for ($i = 0; (!isset($data->success) && $i < 10); $i++) {
			// send a request to describe a module (which returns a list of available fields) for a Vtiger module
		    $response = $this->client->request('GET', $this->url, [
				'query' => [
				    'operation' => 'describe',
				    'sessionName' => $sessionid,
				    'elementType' => $elementType
				]
		    ]);

				// decode the response
		    $data = json_decode($response->getBody()->getContents());
		}

		self::close($sessionid);

		return (isset($data->success)) ? $data : false;
	}
}
