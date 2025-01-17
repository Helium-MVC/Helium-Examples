<?php

use app\models\uuid\Users;
use app\models\uuid\UserPasswords;
use app\models\uuid\Posts;
use app\models\uuid\ActionLogger;
use app\services\AuthenticationService;
use app\services\Session\SessionService;

use prodigyview\template\Template;
use prodigyview\network\Router;
use prodigyview\network\Request;
use prodigyview\network\Response;
use prodigyview\util\Tools;
use prodigyview\system\Security;

include('baseController.php');

/**
 * Th API Route is a specialized route for AJAX calls from the frontend of the site. Security measures are up
 * to you.
 */
class apiController extends baseController {
	
	//Store the data recieved in in the request
	private $_data = array();
	
	public function __construct($registry, $configurtion = array()) {
		parent::__construct($registry, $configurtion );
		
		/**
		 * In the first part of the controller, we are going to validate the CSFR token for POST
		 * request 
		 */
		 
		//Get the request. Request is a ProdigyView method
		$request = new Request();
		$this-> _data = $request->getRequestData('array');
		
		//Deny any request that has an invalid CSRF Token
		if($request->getRequestMethod() === 'post' && !$this ->Token->check($this -> _data)) {
			echo Response::createResponse('400', 'CSRF Tokens Are Invalid');
			exit();
		}
		
		/**
		 * In the part,we are going to validate the api key to ensure
		 * the user has access to the api.
		 */
		$public_key = $this -> registry -> get['api_key'];
		
		$signature = $this -> registry -> get['sig'];
		
		$private_key = SessionService::read('api_token');
		
		$session_signature = Security::encodeHmacSignature($public_key, $private_key, 'sha1', false);
		
		if(!hash_equals($signature, $session_signature)) {
			echo Response::createResponse('400', 'Invalid API Verification');
			exit();
		}
		
	}
	
	public function index() : array  {
		
		
		return array();
	}
	
	/**
	 * The API route called for registering a user
	 */
	public function register() : array {
		
		$model = new Users();
		
		if($model -> create($this->_data, array('validate_options' => array('display' => false, 'event' => 'create')))) {
			AuthenticationService::forceLogin($model -> email);
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else {
			$this -> _errorResponse($model);
		}
		
		exit();
	}
	
	/**
	 * The api route called for updating a user
	 */
	public function updateUser() : array {
		
		$model = Users::findOne(array(
			'conditions' => array('user_id' => $this->_data['user_id'])
		));
		
		if($model && $model -> update($this->_data, array('validate_options' => array('display' => false, 'event' => 'update')))) {
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else if(!$model) {
			echo Response::createResponse(404, 'User Not Found' );
		} else {
			$this -> _errorResponse($model);
		}
		
		exit();
	}

	/*
	 * The api route called for updating a users email
	 */
	public function updateEmail() : array {
		
		
		
		$model = Users::findOne(array(
			'conditions' => array('user_id' => $this->_data['user_id'])
		));
		
		$tmp_user = Users::findOne(array(
			'conditions' => array('email' => $this->_data['email'] )
		));
				
		if(!$tmp_user || $tmp_user -> user_id = SessionService::read('user_id')) {
			if($model -> update(array('email' => $this->_data['email']), array('validate_options' => array('display' => false, 'event' => 'update')))){
				$this -> _jsonResponse($model -> getIterator() -> getData());
			} else {
				$this -> _errorResponse($model);
			}
		} else {
			echo Response::createResponse(404, 'Another user has that email');
		}
		
		exit();
	}
	
	/**
	 * API route called for updated a users password
	 */
	public function updatePassword() : array {
		
		
		
		$model = UserPasswords::findOne(array(
			'conditions' => array('user_id' => $this->_data['user_id'])
		));
		
		if($model && $model -> update($this->_data, array('validate_options' => array('display' => false, 'event' => 'update')))) {
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else if(!$model) {
			echo Response::createResponse(404, 'User Not Found' );
		} else {
			$this -> _errorResponse($model);
		}
		
		exit();
	}
	
	/**
	 * API Route called for retrieving a user
	 */
	public function findUser() : array {
		
		$model = Users::findOne(array(
			'conditions' => array('user_id' => $this -> registry -> get['user_id'])
		));
		
		if($model) {
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else {
			echo Response::createResponse(404, 'User Not Found' );
		}
		
		exit();
	}
	
	/**
	 * API route called for logging in
	 */
	public function login() {
		
		
		
		if($this->_data && AuthenticationService::authenticate($this->_data['email'], $this->_data['password'])) {
			$model = Users::findOne(array(
				'conditions' => array('email' => $this->_data['email'])
			));
			
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else if($this -> registry -> post) {
			echo Response::createResponse(404, 'Invalid Username/Password' );
		}
		
		exit();
	}
	
	/**
	 * API route called for creating a post
	 */
	public function createPost() : array {
		
		$model = new Posts();
		
		
		
		if($model -> create($this->_data, array('validate_options' => array('display' => false, 'event' => 'create')))) {
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else {
			$this -> _errorResponse($model);
		}
		
		exit();
	}
	
	/**
	 * API called to update a post
	 */
	public function updatePost() : array {
		
		$model = Posts::findOne(array(
			'conditions' => array('post_id' => $this->_data['post_id'])
		));
		
		if($model && $model -> update($this->_data, array('validate_options' => array('display' => false, 'event' => 'update')))) {
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else if(!$model) {
			echo Response::createResponse(404, 'Post Not Found' );
		} else {
			$this -> _errorResponse($model);
		}
		
		exit();
	}
	
	/**
	 * API route called for finding a single post
	 */
	public function findPost() : array {
		
		$model = Posts::findOne(array(
			'conditions' => array('post_id' => $this -> registry -> get['post_id'])
		));
		
		if($model) {
			$this -> _jsonResponse($model -> getIterator() -> getData());
		} else {
			echo Response::createResponse(404, 'Post Not Found' );
		}
		
		exit();
	}
	
	/**
	 * Retrieves the current session information
	 */
	public function session() {
		
		$this -> _jsonResponse(array(
			'user_id'=> SessionService::read('user_id'),
			'is_loggedin'=> SessionService::read('is_loggedin'),
			'session_id' => SessionService::getID()
		));
		
		exit();
		
	}
	
	
	/**
	 * Creates a JSON Response
	 */
	protected function _jsonResponse($data, $convert = true) {
		header('Content-type: application/json; charset=utf-8');
		
		$json = json_encode($data, JSON_UNESCAPED_UNICODE);
		
		if (extension_loaded("zlib") && (ini_get("output_handler") != "ob_gzhandler")) {
    			ini_set("zlib.output_compression", 1);
		}
		
		if( ! isset($_GET['callback'])){
   			exit($json);
		}


		if($this -> _isValidCallback($_GET['callback']))
		    exit("{$_GET['callback']}($json)");

		header('status: 400 Bad Request', true, 400);
	}

	//Creates an Error response
	protected function _errorResponse($model) {
		
		$errors = $model -> getValidationErrors();
		$string = '';

		foreach ($errors as $error) {
			foreach($error as $suberror) {
				$string .= '<div class="alert alert-danger">' . $suberror . '</div>';
			}
		}
		
		echo Response::createResponse(425, $string  );
	}
	
	
	
}
