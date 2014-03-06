<?php
/*
Plugin Name: WP Comment Smart Honeypot
Plugin URI: https://github.com/freak3dot/wp-smart-honeypot
Description: Processes the comment form to stop spam bots. Renames the normal fields on this page. Then, creates a honeypot with one of the names of the original fields. The Honeypot is removed with JavaScript.
Version: 1.0.0
Author: Ryan Johnston
Author URI: http://www.newsunflowerchurch.org
*/

new wpCommentSmartHoneypot_plugin();

class wpCommentSmartHoneypot_plugin {

	protected $insertAt;
	protected $label_list = array('Name', 'Email', 'Website', 'Comment');
	protected $icon_list = array('user', 'envelope', 'home', 'comment');
	protected $label;
	protected $addOn;
	protected $salt = '(xPj(77ios0V5iikTZ9W!K1NQ)0aLexnLuKGNam1am7$(pO74KFf&22@m2rRgze&';
	// Change the salt when you install this: http://www.sethcardoza.com/tools/random-password-generator/
	protected $nameRegex = '/name="[a-z0-9]*"/i';
	protected $idRegex = '/id="[a-z0-9]*"/i';
	protected $approvedComment = '';

	function __construct() {
		add_filter( 'comment_form_before_fields', array( &$this, 'wpcsh_init') );
		add_filter( 'comment_form_field_author', array( &$this, 'wpcsh_encrypt_author') );
		add_filter( 'comment_form_field_email', array( &$this, 'wpcsh_encrypt_email') );
		add_filter( 'comment_form_field_url', array( &$this, 'wpcsh_encrypt_url') );
		add_filter( 'comment_form_field_comment', array( &$this, 'wpcsh_encrypt_comment') );
		add_action( 'pre_comment_approved', array( &$this, 'wpcsh_check_form') );
		add_action( 'pre_comment_on_post', array( &$this, 'wpcsh_pre_post') );
		add_action( 'comment_form', array( &$this, 'wpcsh_hidden_field_and_script') );
	}

	function wpcsh_init() {
		$this->insertAt = rand()&3;
		$this->label = rand()&3;
		$this->addOn = substr(sha1(time().$this->salt),0,6);
	}

	function wpcsh_insertHoneyPot(){
		$hp_field = '<div class="control-group"><label for="' . strtolower($this->label_list[$this->label]) . '">' .
			$this->label_list[$this->label] . '</label><div class="input-prepend"><span class="add-on"><i class="icon-' .
			$this->icon_list[$this->label] . '"></i></span><input type="text" name="' . strtolower($this->label_list[$this->label]) .
			'" id="' . strtolower($this->label_list[$this->label]) . '" value="" placeholder="' .
			$this->label_list[$this->label] . '" aria-required="true"></div></div>';
		return $hp_field;
	}

	private function wpcsh_make_field($unique, $addOn = false){
		if($addOn === false){ $addOn = $this->addOn; }
		return md5($unique . $addOn);
	}
	
	private function wpcsh_replace_name_id($unique, $field){
		$field_name = $this->wpcsh_make_field($unique);
		$field = preg_replace($this->nameRegex, 'name="' . $field_name . '"', $field);
		$field = preg_replace($this->idRegex, 'id="' . $field_name . '"', $field);
		return $field;
	}
	
	function wpcsh_encrypt_author($author_field){
		$author_field = $this->wpcsh_replace_name_id('author', $author_field);
		if($this->insertAt == 0) { $author_field = $this->wpcsh_insertHoneyPot() . $author_field; }
		return $author_field;
	}

	function wpcsh_encrypt_email($email_field){
		$email_field = $this->wpcsh_replace_name_id('email', $email_field);
		if($this->insertAt == 1) { $email_field = $this->wpcsh_insertHoneyPot() . $email_field; }
		return $email_field;
	}

	function wpcsh_encrypt_url($url_field){
		$url_field = $this->wpcsh_replace_name_id('url', $url_field);
		if($this->insertAt == 2) { $url_field = $this->wpcsh_insertHoneyPot() . $url_field; }
		return $url_field;
	}

	function wpcsh_encrypt_comment($comment_field){
		$comment_field = $this->wpcsh_replace_name_id('comment', $comment_field);
		if($this->insertAt == 3) { $comment_field = $this->wpcsh_insertHoneyPot() . $comment_field; }
		return $comment_field;
	}

	function wpcsh_pre_post(){
		$output = base64_decode($_POST['enc-type']);
		$output = rtrim($output, '');
		$addOn   = substr($output, 0, -1);
		if (!empty($_POST[strtolower($this->label_list[substr($output, -1, 1)])])) {
				$this->approved = 'spam';
		}
		$author = $this->wpcsh_make_field('author', $addOn);
		$_POST['author'] = $_POST[$author];
		$email = $this->wpcsh_make_field('email', $addOn);
		$_POST['email'] = $_POST[$email];
		$url = $this->wpcsh_make_field('url', $addOn);
		$_POST['url'] = $_POST[$url];
		$comment = $this->wpcsh_make_field('comment', $addOn);
		$_POST['comment'] = $_POST[$comment];
	}

	function wpcsh_check_form($approved) {
		if ($this->approved == 'spam') { return 'spam'; }
		return $approved;
	}

	function wpcsh_hidden_field_and_script(){
		$output = base64_encode($this->addOn . $this->label);
		echo '<input type="hidden" name="enc-type" value="' .$output . '"/>';
		echo '<script type="text/JavaScript">nscrmhp = document.getElementById("' . strtolower($this->label_list[$this->label])  . '"); nscrmhpp = nscrmhp.parentNode; nscrmhppp = nscrmhpp.parentNode; nscrmhppp.parentNode.removeChild(nscrmhppp);</script>';
	}
}
