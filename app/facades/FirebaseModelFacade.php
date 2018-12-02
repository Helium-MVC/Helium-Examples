<?php

namespace app\facades;

use app\factories\ServiceFactory;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * FirebaseModelFacade
 * 
 * This Facade will be responsible for acting like a model when creating a record
 * in Firbase. It will have the CRUD operations that pertians to the Users, Posts
 * and Contact models.
 */
class FirebaseModelFacade {
	
	private $_firebase = null;
	
	public function __construct($database) {
		$this -> _firebase = $database;
	}
	
	/**
	 * Creates a user object
	 * 
	 * @param array $data The data to save
	 * 
	 * @return PVCollection
	 */
	public function createUser(array $data) {
		
		//Create Document ID
		$id = $uuid5 = Uuid::uuid4();
				
		//Assign Values
		$data['user_id'] = $id;
		$data['password'] = \PVSecurity::hash($data['password']);
		$data['activation_token'] = \PVTools::generateRandomString();
		$data['preferences'] = array(
			'email_weekly_updates' =>true,
			'email_comment_responses' => true
		);
		
		$data['date_registered'] = date('Y-m-d H:i:s');
		
		$this -> _firebase->getReference('users/'. $data['user_id'])->set($data);
		
		//Send welcome email via queue
		$queue = ServiceFactory::get('queue');
		
		$email_data = array(
			'user' => \PVConversions::arrayToObject($data),
			'site_url' => \PVConfiguration::getConfiguration('sites') -> site3
		);
		
		$queue -> add('sendWelcomeEmail', $email_data);
		
		//Return new user
		return new \PVCollection($data);
	}
	
	/**
	 * Creates a post object
	 * 
	 * @param array $data The data to save
	 * 
	 * @return PVCollection
	 */
	public function createPost(array $data) {
		
		//Create Document ID
		$id = $uuid5 = Uuid::uuid4();
				
		//Assign Values
		$data['post_id'] = $id;
		
		//Embed User In Post
		$data['user'] = $this -> retrieveUser($data['user_id'], 'array');
		
		//Set The Data Created
		$data['date_created'] = date('Y-m-d H:i:s');
		
		//Empty Comments Array
		$data['comments'] = array();
		
		$this -> _firebase->getReference('posts/'. $data['post_id'])->set($data);
		
		return new \PVCollection($data);
	}
	
	/**
	 * Retrieves a single user based on that users id
	 * 
	 * @param string $id The id of the user
	 * @param string $format The format to return the data. Default is object, option value is an array
	 * 
	 * @return mixed Either returns a PVCollection, Array or false if the record was not found
	 */
	public function retrieveUser($id, $format = 'object') {
		$reference  = $this -> _firebase -> getReference('users/' . $id);
		$snapshot = $reference->getSnapshot();

		$value = $snapshot->getValue();
		
		if($value) {
			
			if($format == 'array') {
				return $value;
			}
			
			return new \PVCollection($value);
		}
		
		return false;
	}
	
	/**
	 * Retrieves a single user based on that users id
	 * 
	 * @param string $id The id of the user
	 * @param string $format The format to return the data. Default is object, option value is an array
	 * 
	 * @return mixed Either returns a PVCollection, Array or false if the record was not found
	 */
	public function retrievePost($id, $format = 'object') {
		$reference  = $this -> _firebase -> getReference('posts/' . $id);
		$snapshot = $reference->getSnapshot();

		$value = $snapshot->getValue();
		
		if($value) {
			if($format == 'array') {
				return $value;
			}
			
			return new \PVCollection($value);
		}
		
		return false;
	}
	
	/**
	 * Updates a user record in firebase
	 * 
	 * @param string $id The id of the record to update
	 * @param array	$data They data fields to update
	 * 
	 * @return void
	 */
	public function updateUser($id, array $data = array()) : void {
		
		$reference  = $this -> _firebase -> getReference('users/' . $id) -> update($data);
	}
	
	/**
	 * Updates a post record in firebase
	 * 
	 * @param string $id The id of the record to update
	 * @param array	$data They data fields to update
	 * 
	 * @return void
	 */
	public function updatePost($id, array $data = array()) : void {
		
   		$reference  = $this -> _firebase -> getReference('posts/' . $id) -> update($data);	
	}
	
	/**
	 * Removes a users record from Firebase
	 * 
	 * @param string $id The id of the record to delete
	 * 
	 * @return void
	 */
	public function deleteUser($id) {
		$reference  = $this -> _firebase -> getReference('users/' . $id) -> remove();
	}
	
	/**
	 * Removes a posts record from Firebase
	 * 
	 * @param string $id The id of the record to delete
	 * 
	 * @return void
	 */
	public function deletePost($id, array $data = array()) {
		$reference  = $this -> _firebase -> getReference('posts/' . $id) -> remove();
	}
	
	public function queryUsers(array $queries = array()) {
		
		$reference  = $this -> _firebase -> getReference('users');
		
		foreach($queries as $query) {
			$reference -> orderByChild($query);
		}
		
		$snapshot = $reference->getSnapshot();

		$value = $snapshot->getValue();
		
		return new \PVCollection($value);
		
	}
	
	public function queryPosts(array $queries = array()) {
		
		$reference  = $this -> _firebase -> getReference('posts');
		
		foreach($queries as $query => $value) {
			$reference->orderByChild($query)->equalTo($value);
		}
		
		$snapshot = $reference->getSnapshot();

		$value = $snapshot->getValue();
		
		return new \PVCollection($value);
		
	}
}
