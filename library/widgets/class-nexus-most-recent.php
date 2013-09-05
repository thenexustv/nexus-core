<?php

class Nexus_Most_Recent {

	use Nexus_Singleton;

	private $slug;

	public function __construct() {
		$this->slug = Nexus_Core::get_instance()->get_prefix('most-recent');
		add_action('wp_dashboard_setup', array($this, 'setup'));
		add_action('save_post', array($this, 'update_post'), 1, 2);
	}

	public function setup() {
		wp_add_dashboard_widget($this->slug, 'Most Recent', array($this, 'display'));
	}

	public function display() {
		$recent = $this->get_recent_data();
		$core = Nexus_Core::get_instance();
		include(NEXUS_CORE_VIEWS . '/most-recent-dashboard.php');
	}

	public function update_post($post_id, $post) {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
		if ( $post->post_type == 'episode' ) {
			delete_transient($this->slug . '-data');
		}
	}

	public function get_recent_data() {

		$recent = get_transient($this->slug . '-data');
		
		if (false === $recent) {
			$recent = $this->get_data();
			set_transient($this->slug . '-data', $recent, 60 * 60 * 24);
		}

		return $recent;

	}

	private function get_data() {

		$recent = array(
			'show' => array(),
			'fringe' => array()
		);

		$fringe_arguments = array(
			'numberposts' => 1,
			'post_type' => 'episode',
			'post_status' => 'publish'
		);
		$recent['fringe'] = wp_get_recent_posts($fringe_arguments);
		

		$uncategorized = get_category_by_slug('uncategorized');
		$fringe = get_category_by_slug('tf');
		$show_arguments = array(
			'numberposts' => 1,
			'post_type' => 'episode',
			'post_status' => 'publish',
			'post__not_in' => array($recent['fringe'][0]['ID'])
		);
		if ($uncategorized || $fringe) {
			$arguments['category__not_in'] = array($uncategorized->term_id, $fringe->term_id);
		}
		$recent['show'] = wp_get_recent_posts($show_arguments);


		return $recent;
	}


}