<?php

class Nexus_Series_Settings {

	use Nexus_Singleton;

	private $default_options = array(
		'retired' => ''
	);

	public function get_options($term_id) {
		$options = get_option("nexus_core_series_$term_id");
		if ( false === $options ) return $this->default_options;
		return array_merge($this->default_options, $options);
	}

	public function save_options($term_id, $options) {
		update_option("nexus_core_series_$term_id", $options);
	}

	private function __construct() {

			add_action('category_add_form_fields', array($this, 'add_new_display'), 10);
			add_action('category_edit_form_fields', array($this, 'edit_display'), 10);
			add_action('edited_category', array($this, 'save'), 10, 2 );  
			add_action('create_category', array($this, 'save'), 10, 2 );

	}

	public function add_new_display($taxonomy) {
		include(NEXUS_CORE_VIEWS . '/add-new-series-fields.php');
	}

	public function edit_display($term) {
		$term_id = $term->term_id;
		$term_meta = get_option("nexus_core_series_$term_id");
		include(NEXUS_CORE_VIEWS . '/edit-series-fields.php');
	}

	public function save($term_id) {

		if ( !current_user_can('manage_options') ) return false;

		$terms = isset($_POST['term_meta']) ? $_POST['term_meta'] : array();
		$term_meta = $this->get_options($term_id);
		$keys = array_merge( array_keys($terms), array_keys($term_meta) );
		foreach ($keys as $key) {
			if ('force' == $key) continue;
			if ( isset($terms[$key]) ) {
				$term_meta[$key] = sanitize_text_field($terms[$key]);
			} elseif ( isset($term_meta[$key]) && !isset($terms[$key]) ) {
				$term_meta[$key] = '';
			}
		}

		$this->save_options($term_id, $term_meta);
		
		return true;
	}

}