<?php

namespace Nexus\Metaboxes;

abstract class AbstractMetabox {

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

	public function print_hidden($name, $value) {
		$html = sprintf('<input type="hidden" name="%1$s" id="%1$s" value="%2$s" />', esc_attr($name), esc_attr($value));
		echo($html);
	}

	public function print_meta_value($object, $meta_key) {
		$html = sprintf(' value="%1$s" ', esc_attr(get_post_meta($object->ID, $meta_key, true)));
		echo($html);
	}

	public function common_save($post_id, $meta_key, $new) {
		$meta_value = get_post_meta($post_id, $meta_key, true);
		if ($new && '' == $meta_value) add_post_meta($post_id, $meta_key, $new, true);
		elseif ($new && $new != $meta_value) update_post_meta($post_id, $meta_key, $new);
		elseif ('' == $new && $meta_value) delete_post_meta($post_id, $meta_key, $meta_value);
	}

}

