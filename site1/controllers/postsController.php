<?php

use app\models\basic\Comments;
use app\models\basic\Posts;
use app\models\basic\Images;
use app\services\session\SessionService;

use prodigyview\template\Template;
use prodigyview\network\Router;
use prodigyview\network\Request;
use prodigyview\network\Response;
use prodigyview\util\Validator;
use prodigyview\util\FileManager;

include('baseController.php');

class postsController extends baseController {
	
	/**
	 * In the construct, we are restricting access to certian routes of this controller.
	 */
	public function __construct($registry, $configurtion = array()) {
		parent::__construct($registry, $configurtion );
		
		$restricted_routes = array(
			'/posts/create',
			'/posts/update',
			'/posts/delete'
		);
		
		if(!SessionService::read('is_loggedin') && in_array($this->registry -> route[0], $restricted_routes)) {
			Template::errorMessage('The section is restricted to members. Please login.');
			Router::redirect('/login');
		}
		
	}
	

	public function index() : array {
		
		//We are going to use the commentModel to
		//get the table name recored in its schema
		$commentModel = new Comments();
		
		$posts = Posts::findAll(array(
			'fields' => 'posts.*, users.*, (SELECT COUNT(comment_id) FROM ' . $commentModel-> getTableName() . ' WHERE comments.post_id=posts.post_id ) AS comment_count',
			'conditions' => array('is_deleted' => 0, 'is_published' => 1),
			'join' => array('user'),
			'order_by' => 'date_created'
		));
		
		return array('posts' => $posts);
	}
	
	public function create()  {
		
		$post = new Posts();
		
		if($this -> registry -> post && $this->Token->check('post_token', $this -> registry -> post['csrf_token']) && $post -> create($this -> registry -> post)) {
				
			if(isset($this -> registry -> files['header_image'] ) && $this -> registry -> files['header_image']['error'] == 0 && Validator::isImageFile(FileManager::getFileMimeType($this -> registry -> files['header_image']['tmp_name'])) ) {	
				$image = Images::uploadImage($this -> registry -> files['header_image']['tmp_name']);
				
				if($image) {
					$image -> update(array('entity_type' => 'post', 'entity_id' => $post -> post_id));
				}
			}
			
			Template::successMessage('Post successfully created.');
			return $this -> redirect('/posts/view/' . $post -> post_id);
		}
		
		
		return array('post' => $post);
	}
	
	public function update() {
		
		$post = Posts::findOne(array(
			'conditions' => array('post_id' => $this -> registry -> route['id'])
		));
		
		if(!$post) {
			return $this -> error404(array('post_id' => $this -> registry -> route['id']),  'Post Not Found');
		}
		
		if($post->hasAccess(SessionService::read('user_id'))) {
			return$this-> accessdenied(array('post_id' => $this -> registry -> route['id'], 'user_id' =>SessionService::read('user_id') ));
		}
		
		if($this -> registry -> post && $this->Token->check('post_token', $this -> registry -> post['csrf_token']) && $post -> update($this -> registry -> post)) {
			
			if(isset($this -> registry -> files['header_image'] ) && $this -> registry -> files['header_image']['error'] == 0 && Validator::isImageFile(FileManager::getFileMimeType($this -> registry -> files['header_image']['tmp_name'])) ) {	
				$image = Images::uploadImage($this -> registry -> files['header_image']['tmp_name']);
				
				if($image) {
					$image -> update(array('entity_type' => 'post', 'entity_id' => $post -> post_id));
				}
			}
			
			Template::successMessage('Post successfully updated.');
			return $this -> redirect('/posts/view/' . $post -> post_id);
		}
		
		return array('post' => $post);
	}

	public function view() : array  {
		
		$post = Posts::findOne(array(
			'conditions' => array('post_id' => $this -> registry -> route['id']),
			'join' => array('user', 'image_left')
		));
		
		if(!$post) {
			return $this -> error404(array('post_id' => $this -> registry -> route['id']),  'Post Not Found');
		}
		
		$comment = new Comments();
		
		if($this -> registry -> post && $this->Token->check('comment_token', $this -> registry -> post['csrf_token']) && $comment -> create($this -> registry -> post)) {
			Template::successMessage('Comment successfully created');
		}
		
		$comments = Comments::findAll(array(
			'conditions' => array('post_id' => $post -> post_id, 'is_removed' => 0),
			'join' => array('user'),
			'order_by' => 'date_added'
		));
		
		return array('post' => $post, 'comments' => $comments);
	}

	public function delete()  {
		
		$post = Posts::findOne(array(
			'conditions' => array('post_id' => $this -> registry -> route['id'])
		));
		
		if(!$post) {
			return $this -> error404(array('post_id' => $this -> registry -> route['id']),  'Post Not Found');
		}
		
		if($post->hasAccess(SessionService::read('user_id'))) {
			return$this-> accessdenied(array('post_id' => $this -> registry -> route['id'], 'user_id' =>SessionService::read('user_id') ));
		}
		
		if($this -> registry -> post && $this->Token->check('delete_token', $this -> registry -> post['csrf_token'])) {
			if(isset($this -> registry -> post['yes']) && $post -> update(array('is_deleted' => 1))) {
				Template::successMessage('Post successfully deleted.');
			}
			
			return $this -> redirect('/posts');
		}
		
		return array('post' => $post);
	}

	public function rss() : array {
		
		$posts = Posts::findAll(array(
			'conditions' => array('is_deleted' => 0, 'is_published' => 1),
			'join' => array('user'),
			'order_by' => 'date_created'
		));
		
		//Changes the template to blank.html.php
		$this -> _renderTemplate(array('prefix' => 'blank'));
		
		return array('posts' => $posts);
	}
	
	
}
