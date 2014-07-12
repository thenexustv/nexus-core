<?php

class Nexus_Metaboxes {

	use Nexus_Singleton;

	private $metaboxes = array();

	private function __construct() {

		add_action('admin_init', array($this, 'register'));

	}

	public function register() {

		$this->metaboxes['podcast-media'] = new Nexus_Podcast_Media_Metabox();

	}


}

abstract class Nexus_Metabox2 {

	private $slug;

	public function __construct() {
		$this->register();
	}

	public function register() {
		add_action('add_meta_boxes', array($this, 'add'));
		add_action('save_post', array($this, 'save'), 10, 3);
	}

	abstract public function add();

	abstract public function save($post_id, $post_object, $updated);

	abstract public function render();

	public function get_nonce_key() {
		return sprintf('%1$s-nonce', $this->slug);
	}

	public function get_nonce_path() {
		return basename($_SERVER['PHP_SELF']);
	}

	public function print_nonce() {
		wp_nonce_field($this->get_nonce_path(), $this->get_nonce_key());
	}

	public function verify_nonce() {
		return !isset($_POST[$this->get_nonce_key()]) || !wp_verify_nonce($_POST[$this->get_nonce_key()], $this->get_nonce_path());
	}

	public function is_save_ineligible($post_id) {
		return ( wp_is_post_revision($post_id) || wp_is_post_autosave( $post_id ) );
	}

	public function is_post_key($key) {
		return isset($_POST[$key]) && !empty($_POST[$key]);
	}

	public function get_post_field($key) {
		return $_POST[$key];
	}

	public function common_save($post_id, $meta_key, $new) {
		$meta_value = get_post_meta($post_id, $meta_key, true);
		if ($new && '' == $meta_value) add_post_meta($post_id, $meta_key, $new, true);
		elseif ($new && $new != $meta_value) update_post_meta($post_id, $meta_key, $new);
		elseif ('' == $new && $meta_value) delete_post_meta($post_id, $meta_key, $meta_value);
	}

}

class Nexus_Podcast_Media_Metabox extends Nexus_Metabox2 {

	private $slug = 'nexus-podcast-media-metabox';

	public function __construct() {
		parent::__construct();
	}

	public function add() {
		add_meta_box($this->slug, 'Podcast Media', array($this, 'render'), 'episode', 'default');
	}

	public function save($post_id, $post_object, $updated) {
		
	}

	public function render() {
		include(NEXUS_CORE_VIEWS . '/metabox-podcast-media.php');
	}


}