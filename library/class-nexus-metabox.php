<?php

abstract class Nexus_Metabox {

	protected $module_name = 'primary';
	protected $nonce_path;
	protected $nonce_key = 'nonce';

	public function get_prefix($key = null) {
		return Nexus_Core::get_instance()->get_prefix() . "-$this->module_name" . (!empty($key) ? "-$key" : '' );
	}

	public function get_nonce_key() {
		return $this->get_prefix($this->nonce_key);
	}

	public function get_nonce_path() {
		$this->nonce_path = basename($_SERVER['PHP_SELF']);
	}

	public function print_nonce_field() {
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

	public function print_hidden($name, $value) {
		$html = '<input type="hidden" name="'.esc_attr($name).'" id="'.esc_attr($name).'" value="'.esc_attr($value).'"  />';
		echo($html);
	}

	public function print_meta_value($object, $meta_key) {
		
		$value = get_post_meta($object->ID, $meta_key, true);

		$html = ' value="' . esc_attr($value) . '"';

		echo($html);

	}

}