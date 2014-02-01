<?php

class Nexus_Playboard {

	use Nexus_Singleton;

	private $slug;

	public function __construct() {
		$this->slug = Nexus_Core::get_instance()->get_prefix('playboard');
		add_action('wp_dashboard_setup', array($this, 'setup'));
		add_action('save_post', array($this, 'update_post'), 1, 2);
	}

	public function setup() {
		wp_add_dashboard_widget($this->slug, 'Playboard', array($this, 'display'));
	}

	public function display() {
		$playboard = $this->get_playboard_data();
		include(NEXUS_CORE_VIEWS . '/playboard-dashboard.php');
	}

	public function update_post($post_id, $post) {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
		if ( $post->post_type == 'episode' ) {
			delete_transient($this->slug . '-data');
		}
	}

	public function get_playboard_data() {
		// $playboard = get_transient($this->slug . '-data');
		$playboard = false;
		if ( false === $playboard ) {
			$playboard = $this->get_data();
			set_transient($this->slug . '-data', $playboard, 60 * 60 * 24);
		}

		return $playboard;
	}

	private function get_data() {

		// hide empty categories
		$arguments = array('hide_empty' => true);
		$uncategorized = get_category_by_slug('uncategorized');

		// hide uncategorized episodes
		if ($uncategorized) {
			$arguments['exclude'] = join(',', array($uncategorized->term_id));
		}

		$playboard = array(
			'series' => array(),
			'total_all' => 0,
			'total ninety' => 0,
			'total_thirty' => 0,
			'total_seven' => 0
		);

		/*
			name, slug, count
		*/
		$categories = get_categories($arguments);
		$total = 0;

		foreach ($categories as $category) {
			$data = array(
				'name' => $category->name,
				'slug' => strtoupper($category->slug),
				'count' => $category->category_count
			);
			$data = apply_filters('nexus_core_playboard_data', $data);
			$playboard['series'][] = $data;
			$total = $total + $category->category_count;
		}


		$playboard['total_all'] = $total;

		$playboard['total_ninety'] = $this->get_range_total(90);
		$playboard['total_thirty'] = $this->get_range_total(30);
		$playboard['total_seven'] = $this->get_range_total(7);

		$playboard['last_update'] = time();

		return $playboard;
	}

	private function get_range_total($days) {
		global $wpdb;
		if ($days <= 0) $days = 1;
		$sql_query = "SELECT COUNT(id) FROM $wpdb->posts WHERE post_status = 'publish' AND post_date >= DATE_SUB(CURRENT_DATE, INTERVAL %d DAY);";
		$query = $wpdb->prepare($sql_query, $days);
		$results = $wpdb->get_var($query);
		return $results;
	}


}