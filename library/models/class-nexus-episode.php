<?php

class Nexus_Episode {

	public static function factory($object = null) {
		global $wp_query;

		if ( $object instanceof WP_POST ) {
			if ( 'episode' != $object->post_type ) new WP_Error('not_episode', 'Not An Episode');
			$id = $object->ID;
			return new self($id);
		} elseif ( is_numeric($object) ) {
			return self::factory(get_post($object));
		} elseif ( isset($wp_query->post) ) {
			return self::factory($wp_query->post);
		}

		new WP_Error('not_episode', 'Not An Episode');
	}

	public static function format_episode_title($object = null) {
		$episode = Nexus_Episode::factory($object);
		return $episode->get_formatted_title();
	}	

	private $id;
	private $post;

	/*
		Never constructed directly.
		Please use the factory, above.
	*/
	private function __construct($id) {
		// $id is a post id
		$this->id = $id;
		$this->post = get_post($id);
	}

	public function get_id() {
		return $this->id;
	}

	public function get_post() {
		return $this->post;
	}

	public function get_excerpt() {
		return $this->post->post_excerpt;
	}

	public function get_episode_number() {
		return Nexus_Core::get_instance()->get_episode_number($this->id);
	}

	public function get_series_name() {
		$category = get_the_category( $this->id ); 
		return $category[0]->cat_name;
	}

	public function get_permalink() {
		return get_permalink($this->id);
	}

	public function get_posted_date($format = null) {
		return get_the_time($format, $this->post);
	}

	public function is_new($tolerance = 7) {
		$computed = $this->get_posted_date('U');
		$against = strtotime("-$tolerance days");
		return $computed > $against;
	}

	public function get_modified_date() {
		return $this->post->post_modified;
	}

	public function get_title_raw() {
		$title = $this->post->post_title;
		return $title;
	}

	public function get_title() {
		$title = $this->post->post_title;

		if ( $this->is_fringe() && $this->has_parent() ) {

			if ( !stripos($title, '#') && !(stripos($title, '--') || stripos($title, '-')) ) {
				$parent = Nexus_Episode::factory($this->get_parent());
				$series = $parent->get_series();

				$slug = strtoupper($series->get_slug());

				$number = $parent->get_episode_number();
				$title = "{$slug} #{$number} -- {$title}";
			}

		}


		$title = apply_filters('episode_get_title', $title, $this);
		$title = wptexturize($title);

		return $title;
	}

	public function get_formatted_title() {
		$id = $this->post->ID;
		$number = $this->get_episode_number();
		if ( false == $number ) { $number = 'X'; }

		$series = $this->get_series();
		$name = $series->get_name();

		$name = ('' != $name ? $name : 'Episode' );

		$title = $this->get_title();

		$template = "{$name} #{$number}: {$title}";
		
		$template = wptexturize($template);

		return $template;

	}

	public function get_content() {
		return $this->post->post_content;
	}

	public function is_fringe() {
		return in_category('tf', $this->id);
	}

	public function is_parent() {
		$option = get_post_meta($this->id, 'nexus-parent-episode', true);
		if ( is_numeric($option) ) return true;
		return false;
	}

	public function has_parent() {
		$option = get_post_meta($this->id, 'nexus-parent-episode', true);
		if ( is_numeric($option) ) return true;
		return false;
	}

	public function has_fringe() {
		$option = get_post_meta($this->id, 'nexus-fringe-episode', true);
		if ( is_numeric($option) ) return true;
		return false;
	}

	public function get_fringe() {
		$option = get_post_meta($this->id, 'nexus-fringe-episode', true);	
		return $option;
	}

	public function get_parent() {
		$option = get_post_meta($this->id, 'nexus-parent-episode', true);	
		return $option;
	}

	public function is_nsfw() {
		$option = get_post_meta($this->id, 'nexus-nsfw-episode', true);
		if ($option == '1') return true;
		return false;
	}

	public function get_series() {
		return Nexus_Series::factory($this->id);
	}

	public function get_contact_url() {
		$path = home_url('contact');
		$show = sanitize_title($this->get_series_name());
		$number = $this->get_episode_number();
		$path = $path . "?show={$show}&number={$number}"; 
		return $path;
	}

	public function has_enclosure($type = 'podcast') {
		$handle = ( in_array($type, array('podcast', '')) ) ? 'enclosure' : "_{$type}:enclosure";
		$meta = get_post_meta($this->id, $handle);
		return !!$meta;
	}

	private function format_enclosure($enclosure) {
		if ( !$enclosure ) return array();
		$formatted = array();

		$enclosure = explode("\n", $enclosure[0]);

		$formatted['url'] = $this->get_tracking_url($enclosure[0]);
		$formatted['_url'] = $enclosure[0];
		$formatted['size'] = $enclosure[1];
		$formatted['mime'] = $enclosure[2];

		$extra = unserialize($enclosure[3]);
		$formatted['duration'] = $extra['duration'];

		return $formatted;

	}

	public function get_enclosure($type = 'podcast') {
		$handle = ( in_array($type, array('podcast', '')) ) ? 'enclosure' : "_{$type}:enclosure";
		$meta = get_post_meta($this->id, $handle);
		if ( !$meta ) return false;

		$formatted = $this->format_enclosure($meta);

		return $formatted;
	}

	public function get_tracking_url($url) {

		// leverage PowerPress
		$powerpress_general_settings = get_option('powerpress_general');
		// we need 'redirect0' as the key
		$key = 'redirect1';

		if ( !isset($powerpress_general_settings[$key]) ) return $url; 

		$tracking = $powerpress_general_settings[$key];
		$tracking = str_replace('http://', '', $tracking);

		$url_clean = apply_filters('before_tracking_url', $url);
		$url_clean = str_replace('http://', '', $url_clean);

		$redirect = "http://{$tracking}{$url_clean}";

		// this filter will allow for admin filter
		$redirect = apply_filters('after_tracking_url', $redirect, $url);

		return $redirect;
	}

	public function has_people() {
		$meta = get_post_meta($this->id, 'nexus-episode-people');
		if ( $meta && is_array($meta) ) return true;
		return false;
	}


	/*
		returns two tiers of people; those with and without emails for gravatrs

	*/
	public function get_people() {
		$meta = get_post_meta($this->id, 'nexus-episode-people');
		if ( !$meta || !is_array($meta) ) return false;

		$hosts = array();
		$primary = array();
		$secondary = array();

		foreach ($meta as $person_id) {
			$email = get_post_meta($person_id, 'nexus-people-email', true);
			$host = (get_post_meta($person_id, 'nexus-people-host', true) == '1') ? true : false;

			if ( !is_string($email) || '' == trim($email) ) $secondary[] = $person_id;
			elseif ( $host && is_string($email) ) $hosts[] = $person_id;
			else $primary[] = $person_id;
		}

		$data = array('primary' => $primary, 'secondary' => $secondary, 'hosts' => $hosts);

		return $data;

	}

	public function get_albumart($settings = array()) {
		$default = array(
			'size' => 'medium',
			'link_to_post' => false,
			'format' => 'array',
			'post_id' => $this->get_id()
		);
		$settings = array_merge($default, $settings);
		$settings = apply_filters('get_album_art_settings', $settings);
		$image = get_the_image($settings);
		if ( !$image || empty($image) ) return false;
		return $image;
	}


}