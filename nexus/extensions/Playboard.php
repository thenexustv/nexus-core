<?php

namespace Nexus\Extensions;

class Playboard {

	use \Nexus\Singleton;

	public function __construct() {
		add_action('save_post', array($this, 'update_post'), 1, 2);
	}

	public function update_post($post_id, $post) {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
		if ( $post->post_type == 'episode' ) {
			delete_transient('nexus-playboard-data');
		}
	}	

	public function get_data() {
		$playboard = get_transient('nexus-playboard-data');
		if ( false === $playboard ) {
			$playboard = $this->get_playboard_data();
			set_transient('nexus-playboard-data', $playboard, 60 * 60 * 8);
		}

		return $playboard;
	}

	private function get_playboard_data() {
		
		$playboard = array(
			'series' => array(),
			'total_all' => 0,
			'total ninety' => 0,
			'total_thirty' => 0,
			'total_seven' => 0
		);

		$series_ids = \Nexus\Series::get_series_ids();
		$total = 0;

		foreach ($series_ids as $series_id) {
			$series = \Nexus\Series::get_by_series($series_id);
			$data = array(
				'name' => $series->get_name(),
				'slug' => strtoupper($series->get_slug()),
				'count' => $series->get_count()
			);
			$data = apply_filters('nexus_core_playboard_data', $data);
			$playboard['series'][] = $data;
			$total = $total + $series->get_count();
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

