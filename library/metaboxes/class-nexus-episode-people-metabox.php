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
		$arguments = array(
			'post_type' => 'person',
			'post_status' => 'any',
			'numberposts' => 5,
			'order' => 'ASC',
			's' => sanitize_text_field($_REQUEST['term'])
		);
		$posts = get_posts($arguments);
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
		$original_size = 0;
		$unique_size = 0;
		if ( !is_array($meta) ) {
			$meta = array();
		} else {
			$original_size = count($meta);
			$meta = array_unique($meta);
			$unique_size = count($meta);
		}

		$has_duplicates = ( $original_size != $unique_size );

		foreach ($meta as $value) {
			$post = get_post( $value );
			$members[] = array('label' => $post->post_title, 'value' => $value);
		}

		include(NEXUS_CORE_VIEWS . '/episode-people-metabox.php');
	}

	public function save($post_id, $post) {
		if ( $this->verify_nonce() ) return $post_id;

		// used to ensure a script locally wrote previous values
		if ( !$this->is_post_key('nexus-person-commit') ) return $post_id;

		$people = $this->is_post_key('nexus-person') ? $this->get_post_field('nexus-person') : array();

		// get existing people attached to this episode
		$meta = get_post_meta($post_id, 'nexus-episode-people', false);
		if ( !is_array($meta) ) {
			$meta = array();
		} else {
			$meta = array_unique($meta, SORT_NUMERIC);
		}

		$ids = array();

		foreach ($people as $person) {
			$person_id = intval($person);

			if ( !is_numeric($person_id) ) continue;
			
			$ids[] = $person_id;
		}

		$ids = array_unique($ids);

		array_walk($ids, function($id) use ($post_id, $meta) {
			if ( in_array($id, $meta) ) return;

			$post = get_post( $id );
			if (empty($post)) return;

			add_post_meta($post_id, 'nexus-episode-people', $id);
		});

		$delete = array_diff($meta, $ids);

		foreach ($delete as $person_id) {
			delete_post_meta($post_id, 'nexus-episode-people', $person_id);
		}

		$this->_debug($post_id, $meta, $ids);

	}

	private function _debug($post_id, $ids, $meta) {
		$raw_meta = get_post_meta($post_id, 'nexus-episode-people', false);
		$array = array(
			'date' => date(DATE_RFC2822),
			'post_id' => $post_id,
			'ids' => $ids,
			'meta' => $meta,
			'raw_meta' => $raw_meta,
			'duplicate' => ( count(array_unique($raw_meta)) != count($raw_meta) ) ? 'true' : 'false'
		);
		$handle = fopen(WP_CONTENT_DIR . '/_debug.txt', 'a');
		$string = print_r($array, true);
		fwrite($handle, $string . "\n");
		fclose($handle);
	}

}

