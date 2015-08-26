<?php
/**
 * Plugin Name: WP Comment Smart Honeypot
 * Plugin URI: https://github.com/freak3dot/wp-smart-honeypot
 * Description: Processes the comment form to stop spam bots. Renames the normal fields on this page. Then, creates a honeypot with one of the names of the original fields. The Honeypot is removed with JavaScript.
 * Version: 1.0.0
 * Author: Ryan Johnston
 * Author URI: http://www.newsunflowerchurch.org
 *
 * @package wp-smart-honeypot
 */

new wpCommentSmartHoneyPotPlugin();

/**
 * Wordpress Comment Smart Honeypot Plugin.
 *
 * @package wp-smart-honeypot
 */
class wpCommentSmartHoneyPotPlugin {

	/** @var int Location index of the inserted honeypot. */
	protected $insertAt;
	/** @var array List of field names. */
	protected $label_list = array( 'Name', 'Email', 'Website', 'Comment' );
	/** @var array List of bootstrap icons corresponding to the abvoe field names. */
	protected $icon_list = array( 'user', 'envelope', 'home', 'comment' );
	/** @var int Honeypot Label id. */
	protected $label;
	/** @var string Random string for obfuscation of field names. */
	protected $addOn;
	/** @var string Salt for obfuscation of field names.
	 * Change the salt when you install this.
	 * http://www.sethcardoza.com/tools/random-password-generator
	 */
	protected $salt = '(xPj(77ios0V5iikTZ9W!K1NQ)0aLexnLuKGNam1am7$(pO74KFf&22@m2rRgze&';
	/** @var string Regex to find name html property. */
	protected $nameRegex = '/name="[a-z0-9]*"/i';
	/** @var string Regex to find id html property. */
	protected $idRegex = '/id="[a-z0-9]*"/i';
	/** @var string Approved Comment. */
	protected $approvedComment = '';

	/**
	 * Construct the class.
	 *
	 * Set up the wordpress filters that will use this plugin.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		add_filter( 'comment_form_before_fields', array( &$this, 'wpcsh_init' ) );
		add_filter( 'comment_form_field_author', array( &$this, 'wpcsh_encrypt_author' ) );
		add_filter( 'comment_form_field_email', array( &$this, 'wpcsh_encrypt_email' ) );
		add_filter( 'comment_form_field_url', array( &$this, 'wpcsh_encrypt_url' ) );
		add_filter( 'comment_form_field_comment', array( &$this, 'wpcsh_encrypt_comment' ) );
		add_action( 'pre_comment_approved', array( &$this, 'wpcsh_check_form' ) );
		add_action( 'pre_comment_on_post', array( &$this, 'wpcsh_pre_post' ) );
		add_action( 'comment_form', array( &$this, 'wpcsh_hidden_field_and_script' ) );
	}

	/**
	 * Initialize WP Comment Smart Honeypot Plugin.
	 *
	 * @since 1.0.0
	 */
	function wpcsh_init() {
		$this->insertAt = rand( )&3;
		$this->label = rand( )&3;
		$this->addOn = substr( sha1( time( ) . $this->salt ), 0, 6 );
	}

	/**
	 * Insert the Honeypot Field
	 *
	 * Copy one of the existing comment fields and return this wrapped in a
	 * bootstrap control-group.
	 *
	 * @since 1.0.0
	 *
	 * @return string Honeypot Field as HTML
	 */
	function wpcsh_insert_honey_pot() {
		$hp_field = '<div class="control-group"><label for="' . strtolower( $this->label_list[ $this->label ] ) . '">' .
			$this->label_list[ $this->label ] . '</label><div class="input-prepend"><span class="add-on"><i class="icon-' .
			$this->icon_list[ $this->label ] . '"></i></span><input type="text" name="' . strtolower( $this->label_list[ $this->label ] ) .
			'" id="' . strtolower( $this->label_list[ $this->label ] ) . '" value="" placeholder="' .
			$this->label_list[ $this->label ] . '" aria-required="true"></div></div>';
		return $hp_field;
	}

	/**
	 * Generate a unique md5 obfuscated field name.
	 *
	 * Add the field name to the $addOn and encode it in md5. If $addOn is not
	 * provided, then the default $addOn for this class will be used.
	 *
	 * @since 1.0.0
	 *
	 * @param string $unique Original Field Name.
	 * @param string $addOn Add on, like a salt, so the md5 is more difficult to undo.
	 *
	 * @return string Honeypot Field as HTML
	 */
	private function wpcsh_make_field($unique, $addOn = false) {
		if ( false === $addOn ) { $addOn = $this->addOn; }
		return md5( $unique . $addOn );
	}

	/**
	 * Replace name and id HTML attributes with unique md5 obfuscated field name.
	 *
	 * Add the field name to the $addOn and encode it in md5. If $addOn is not
	 * provided, then the default $addOn for this class will be used.
	 *
	 * @since 1.0.0
	 *
	 * @param string $unique Original Field Name.
	 * @param string $field HTML for the field.
	 *
	 * @return string HTML for the field.
	 */
	private function wpcsh_replace_name_id($unique, $field) {
		$field_name = $this->wpcsh_make_field( $unique );
		$field = preg_replace( $this->nameRegex, 'name="' . $field_name . '"', $field );
		$field = preg_replace( $this->idRegex, 'id="' . $field_name . '"', $field );
		return $field;
	}

	/**
	 * Obfuscate the author field.
	 *
	 * Replaces name and id HTML attributes in the author field with unique md5 obfuscated field name.
	 * Also inserts the honeypot if the random location selected is at 0. 0 would be before the author field.
	 * This is used by a wordpress filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $author_field HTML for the author field.
	 *
	 * @return string HTML for the field.
	 */
	function wpcsh_encrypt_author($author_field) {
		$author_field = $this->wpcsh_replace_name_id( 'author', $author_field );
		if ( $this->insertAt === 0 ) { $author_field = $this->wpcsh_insert_honey_pot() . $author_field; }
		return $author_field;
	}

	/**
	 * Obfuscate the email field.
	 *
	 * Replaces name and id HTML attributes in the email field with unique md5 obfuscated field name.
	 * Also inserts the honeypot if the random location selected is at 1. 1 would be before the email field.
	 * This is used by a wordpress filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email_field HTML for the email field.
	 *
	 * @return string HTML for the field.
	 */
	function wpcsh_encrypt_email($email_field) {
		$email_field = $this->wpcsh_replace_name_id( 'email', $email_field );
		if ( $this->insertAt === 1 ) { $email_field = $this->wpcsh_insert_honey_pot() . $email_field; }
		return $email_field;
	}

	/**
	 * Obfuscate the url field.
	 *
	 * Replaces name and id HTML attributes in the url field with unique md5 obfuscated field name.
	 * Also inserts the honeypot if the random location selected is at 2. 2 would be before the url field.
	 * This is used by a wordpress filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url_field HTML for the url field.
	 *
	 * @return string HTML for the field.
	 */
	function wpcsh_encrypt_url($url_field) {
		$url_field = $this->wpcsh_replace_name_id( 'url', $url_field );
		if ( $this->insertAt === 2 ) { $url_field = $this->wpcsh_insert_honey_pot() . $url_field; }
		return $url_field;
	}

	/**
	 * Obfuscate the comment field.
	 *
	 * Replaces name and id HTML attributes in the comment field with unique md5 obfuscated field name.
	 * Also inserts the honeypot if the random location selected is at 3. 3 would be before the comment field.
	 * This is used by a wordpress filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $comment_field HTML for the comment field.
	 *
	 * @return string HTML for the field.
	 */
	function wpcsh_encrypt_comment($comment_field) {
		$comment_field = $this->wpcsh_replace_name_id( 'comment', $comment_field );
		if ( $this->insertAt === 3 ) { $comment_field = $this->wpcsh_insert_honey_pot() . $comment_field; }
		return $comment_field;
	}

	/**
	 * Pre Comment On Post
	 *
	 * Undo the obfuscate that was done on the comment form. This will enable wordpress to understand the form again.
	 * This method also checks the honeypot for content and will mark the comment form as spam.
	 * This is used by a wordpress action.
	 *
	 * @since 1.0.0
	 */
	function wpcsh_pre_post() {
		$output = base64_decode( $_POST['enc-type'] );
		$output = rtrim( $output, '' );
		$addOn = substr( $output, 0, -1 );
		if ( ! empty( $_POST[ strtolower( $this->label_list[ substr( $output, -1, 1 ) ] ) ] ) ) {
			$this->approved = 'spam';
		}
		$author = $this->wpcsh_make_field( 'author', $addOn );
		$_POST['author'] = $_POST[ $author ];
		$email = $this->wpcsh_make_field( 'email', $addOn );
		$_POST['email'] = $_POST[ $email ];
		$url = $this->wpcsh_make_field( 'url', $addOn );
		$_POST['url'] = $_POST[ $url ];
		$comment = $this->wpcsh_make_field( 'comment', $addOn );
		$_POST['comment'] = $_POST[ $comment ];
	}

	/**
	 * Comment Approved.
	 *
	 * Determine if the comment form was flagged as spam by the wpcsh_pre_post method.
	 * If the comment form is not honeypot spam, return the original status.
	 * The original status was presumably approved.
	 * This is used by a wordpress action.
	 *
	 * @since 1.0.0
	 *
	 * @param string $approved Form Status.
	 *
	 * @return string Form Status.
	 */
	function wpcsh_check_form($approved) {
		if ( $this->approved === 'spam' ) { return 'spam'; }
		return $approved;
	}

	/**
	 * Comment Form Creation.
	 *
	 * Inject the hidden field for our $addOn which will be used to undo the
	 * obfuscation of field names. Also, add the intentionally raw JavaScript
	 * that will remove the honeypot for our human users.
	 * This is used by a wordpress action.
	 *
	 * @since 1.0.0
	 */
	function wpcsh_hidden_field_and_script() {
		$output = base64_encode( $this->addOn . $this->label );
		echo '<input type="hidden" name="enc-type" value="' . esc_attr( $output ) . '"/>';
		echo '<script type="text/JavaScript">nscrmhp = document.getElementById("' . esc_js( strtolower( $this->label_list[ $this->label ] ) ) . '"); nscrmhpp = nscrmhp.parentNode; nscrmhppp = nscrmhpp.parentNode; nscrmhppp.parentNode.removeChild(nscrmhppp);</script>';
	}
}
