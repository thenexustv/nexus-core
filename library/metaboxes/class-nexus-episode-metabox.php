<?php

class Nexus_Episode_Metabox extends Nexus_Metabox {

	use Nexus_Singleton;

	// the name of the module
	protected $module_name = 'episode';

	public function __construct() {
		add_action('wp_ajax_episode_search', array($this, 'episode_search'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		add_action('save_post', array($this, 'save'), 10, 2);
	}

	public function episode_search() {
		global $wpdb;
		$arguments = array(
			'post_type' => 'episode',
			'post_status' => 'any',
			'orderby' => 'date',
			'order' => 'DESC',
			'numberposts' => 5,
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
		add_meta_box('episode-box', esc_html('Episode'), array($this, 'display'), 'episode', 'normal');
	}

	public function display($object, $box) {
		$parent_episode_id = get_post_meta($object->ID, 'nexus-parent-episode', true);
		if ($parent_episode_id == '') $parent_episode_title = '';
		else $parent_episode_title = get_post($parent_episode_id)->post_title;

		$fringe_episode_id = get_post_meta($object->ID, 'nexus-fringe-episode', true);
		if ($fringe_episode_id == '') $fringe_episode_title = '';
		else $fringe_episode_title = get_post($fringe_episode_id)->post_title;

		$episode_number = get_post_meta($object->ID, 'nexus-episode-number', true);

		$nsfw_episode = get_post_meta($object->ID, 'nexus-nsfw-episode', true);

		include(NEXUS_CORE_VIEWS . '/metabox-episode-people.php');
	}

	public function save($post_id, $post) {
		if ( $this->verify_nonce() ) return $post_id;


		$parent = $this->is_post_key('nexus-parent-episode-id') ? $this->get_post_field('nexus-parent-episode-id') : '';
		$fringe = $this->is_post_key('nexus-fringe-episode-id') ? $this->get_post_field('nexus-fringe-episode-id') : '';
		$nsfw = $this->is_post_key('nexus-nsfw-episode') ? '1' : '';

		if ( null != get_post($parent) ) $this->common_save($post_id, 'nexus-parent-episode', $parent);
		if ( null != get_post($fringe) ) $this->common_save($post_id, 'nexus-fringe-episode', $fringe);

		$this->common_save($post_id, 'nexus-nsfw-episode', $nsfw);

	}

}

