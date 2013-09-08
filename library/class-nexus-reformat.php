<?php
/*
	This file will port existing Convergence data to Nexus Core style data.

	Do not run this.
*/
class Nexus_Reformat {

	public function get_place() {
		if ( isset($_GET['place']) && !empty($_GET['place']) ) return $_GET['place'];
		return false;
	}

	public function get_episode_ids() {
		$arguments = array(
			'post_type' => 'episode',
			'posts_per_page' => -1,
			'post_status' => 'published'
		);
		$query = new WP_Query($arguments);
		$episode_ids = array();
		while ($query->have_posts()) {
			$query->the_post();
			$episode_ids[] = get_the_ID();
		}
		return $episode_ids;
	}

	public function get_person_ids() {
		$arguments = array(
			'post_type' => 'person',
			'posts_per_page' => -1,
			'post_status' => 'published'
		);
		$query = new WP_Query($arguments);
		$person_ids = array();
		while ($query->have_posts()) {
			$query->the_post();
			$person_ids[] = get_the_ID();
		}
		return $person_ids;
	}

	public function update_episode($id) {
		$updates = array();
		$updates['fringe'] = $this->fringe_to_id($id);
		$updates['people'] = $this->people_to_id($id);
		$updates['nsfw'] = $this->nsfw_to_nsfw($id);
		return $updates;
	}

	public function update_person($id) {
		$updates = array();
		$updates['gravatar'] = $this->gravatar_to_email($id);
		$updates['host'] = $this->host_to_host($id);
		$updates['website'] = $this->website_to_website($id);
		return $updates;
	}

	// person

	public function gravatar_to_email($id) {
		$value = get_post_meta($id, 'confluence-person-gravatar', true);
		if ( $value == '' || $value == false ) return true;
		return update_post_meta($id, 'nexus-people-email', $value);
	}

	public function host_to_host($id) {
		$value = get_post_meta($id, 'confluence-person-host', true);
		if ( $value == '' || $value == false ) return true;
		return update_post_meta($id, 'nexus-people-host', $value);
	}

	public function website_to_website($id) {
		$value = get_post_meta($id, 'confluence-person-website', true);
		if ( $value == '' || $value == false ) return true;
		delete_post_meta($id, 'nexus-people-website');
		return update_post_meta($id, 'nexus-people-website-url', $value);
	}

	// episode

	public function fringe_to_id($id) {
		$value = get_post_meta($id, 'confluence-fringe-url', true);
		if ( $value == '' || $value == false ) return true;

		$parts = explode("/", $value);
		$slug = $parts[count($parts)-2];

		$query = new WP_Query( array('post_type' => 'episode', 'name' => $slug) );
		$post_id = false;
		if ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();
		}
		if ( $post_id == false ) return false;
		return update_post_meta($id, 'nexus-fringe-episode', $post_id);
	}

	public function people_to_id($id) {
		$value = get_post_meta($id, 'confluence-people', false);
		if ( !is_array($value) ) return -1;

		foreach ($value as $slug) {

			$query = new WP_Query( array('post_type' => 'person', 'name' => $slug) );
			$post_id = false;
			if ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				var_dump($post_id);
			}

			if ( $post_id ) add_post_meta($id, 'nexus-episode-people', $post_id);
		}

		return true;
	}

	public function nsfw_to_nsfw($id) {
		$value = get_post_meta($id, 'confluence-nsfw', true);
		if ( $value == '' || $value == false ) return true;
		return update_post_meta($id, 'nexus-nsfw-episode',$value);
	}



}