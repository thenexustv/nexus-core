<?php

class Nexus_Episode_People_Metabox extends Nexus_Metabox {

	use Nexus_Singleton;

	// the name of the module
	protected $module_name = 'episode-people';

	public function __construct() {
		add_action('wp_ajax_episode_people_search', array($this, 'episode_people_search_callback'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		add_action('save_post', array($this, 'save'), 10, 2);
	}

	public function episode_people_search_callback() {
		global $wpdb;
		$posts = get_posts(array('post_type' => 'person', 's' => sanitize_text_field($_REQUEST['term']) ));
		$suggestions = array();
		global $post;
		foreach ($posts as $post):
			setup_postdata($post);
			$suggestion = array();
			$suggestion['label'] = esc_html($post->post_title);
			$suggestion['value'] = esc_html($post->ID);
			$suggestions[] = $suggestion;
		endforeach;
		echo(json_encode($suggestions));
		exit();
	}

	public function enqueue_admin_scripts() {
		wp_enqueue_script('jquery-ui-autocomplete');
	}

	public function add_meta_box() {
		add_meta_box('people-box', esc_html('People'), array($this, 'display'), 'episode', 'side');
	}

	public function display($object, $box) {

		// assemble arrays of members present
		$members = array();
		$meta = get_post_meta($object->ID, 'nexus-episode-people');
		foreach ($meta as $value) {
			$post = get_post( $value );
			$members[] = array('label' => $post->post_title, 'value' => $value);
		}

		include(NEXUS_VIEWS . '/episode-people-metabox.php');
	}

	public function save($post_id, $post) {
		if ( $this->verify_nonce() ) return $post_id;

		/*
			TODO:
			confluence-people TO nexus-episode-person
		*/

		$people = $this->is_post_key('nexus-person') ? $this->get_post_field('nexus-person') : array();
		// because people are singular when not together

		$meta = get_post_meta($post_id, 'nexus-episode-people');
		$ids = array();

		foreach ($people as $person) {
			$person_id = intval($person);
			$ids[] = $person_id;

			// does the person_id exist?
			$post = get_posts( 'p=' . $person_id );
			if (!empty($post)) continue;

			if ( in_array($person_id, $meta) ) continue;

			
			add_post_meta($post_id, 'nexus-episode-people', $person_id);
		}

		$delete = array_diff($meta, $ids);
		foreach ($delete as $person_id) {
			delete_post_meta($post_id, 'nexus-episode-people', $person_id);
		}

	}

}

