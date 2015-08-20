<?php

namespace Nexus\Metaboxes;

class PeopleMetabox extends LegacyAbstractMetabox {

	use \Nexus\Singleton;

	// the name of the module
	protected $module_name = 'episode';

	public function __construct() {
		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		add_action('save_post', array($this, 'save'), 10, 2);
	}

	public function add_meta_box() {
		add_meta_box('episode-box', esc_html('Personnel Details'), array($this, 'display'), 'person', 'normal');
	}

	public function display($object, $box) {
		include(NEXUS_CORE_VIEWS . '/metabox-people.php');
	}

	public function save($post_id, $post) {
		if ( $this->verify_nonce() ) return $post_id;

		$fields = array();
		$fields = array('nexus-people-email', 'nexus-people-twitter-url', 'nexus-people-googleplus-url', 'nexus-people-website-url');

		foreach ($fields as $field) {
			$value = $this->is_post_key($field) ? $this->get_post_field($field) : '';
			$this->common_save($post_id, $field, sanitize_text_field($value));
		}

		// force this to always have a value; otherwise meta_key ordering will not work
		// then force a persistent update
		$is_host = $this->is_post_key('nexus-people-host') ? '1' : '0';
		update_post_meta($post_id, 'nexus-people-host', $is_host);

	}

}

