<?php

class Nexus_Most_Recent {

	use Nexus_Singleton;

	public function __construct() {
		add_action('save_post', array($this, 'update_post'), 1, 2);
	}

	public function update_post($post_id, $post) {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
		if ( $post->post_type == 'episode' ) {
			delete_transient('nexus-most-recent-data');
		}
	}

	public function get_data() {
		$recent = get_transient('nexus-most-recent-data');
		if (false === $recent) {
			$recent = $this->get_recent_data();
			set_transient('nexus-most-recent-data', $recent, 60 * 60 * 24);
		}
		return $recent;
	}

	private function get_recent_data() {

		$recent = array(
			'show' => null,
			'fringe' => null
		);

		$not_in = array();

		$uncategorized = get_category_by_slug('uncategorized');
		$fringe = get_category_by_slug('tf');

		if ($uncategorized) {
			$not_in[] = $uncategorized->term_id;
		}
		if ($fringe) {
			$not_in[] = $fringe->term_id;
		}

		$fringe_arguments = array(
			'numberposts' => 1,
			'post_type' => 'episode',
			'post_status' => 'publish',
			'cat' => ($fringe ? $fringe->term_id : '')
		);
		$recent['fringe'] = wp_get_recent_posts($fringe_arguments);

		$show_arguments = array(
			'numberposts' => 1,
			'post_type' => 'episode',
			'post_status' => 'publish',
			'post__not_in' => array($recent['fringe'][0]['ID'])
		);
		
		$arguments['category__not_in'] = $not_in;
		$recent['show'] = wp_get_recent_posts($show_arguments);

		$recent['last_update'] = time();

		return $recent;
	}	

}