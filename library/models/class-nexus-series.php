<?php

class Nexus_Series {


	public static function factory($object) {
		global $wp_query;

		if ( null == self::$series_core ) {
			self::$series_core = Nexus_Core::get_instance();
		}

		if ( $object instanceof WP_Post ) {

			if ( 'episode' != $object->post_type ) new WP_Error('not_episode', 'Not An Episode');
			return new self($object->ID);

		} elseif ( is_numeric($object) ) {
			return self::factory(get_post($object));
		} elseif ( isset($wp_query->post) ) {
			return self::factory($wp_query->post);
		}

		new WP_Error('not_episode', 'Not An Episode');

	}
	
	private static $series_core;


	private $post_id;

	// the $series_id is the primary category ID
	private $series_id;

	// the $object is the array of categories, but generally
	// we're only interested in the primary category ID
	private $object;

	private $primary;

	private function __construct($post_id) {
		$this->post_id = $post_id;
		$object = get_the_category($this->post_id);
		if ( !is_array($object) ) $object = array();
		$this->object = $object;

		if ( $object && isset($this->object[0]) && !empty($this->object[0]) ) {
			$this->primary = $this->object[0];
			$this->series_id = $this->get_primary()->term_id;
		}

	}

	public function get_primary() {
		return $this->primary;
	}

	public function get_name() {
		if ( !$this->primary ) return '';
		return $this->primary->name;
	}

	public function get_slug() {
		if ( !$this->primary ) return '';
		return $this->primary->slug;
	}

	public function get_description() {
		if ( !$this->primary ) return '';
		return $this->primary->description;
	}

	public function is_retired() {
		$term_id = $this->series_id;
		$option = get_option("nexus_core_series_$term_id");
		if (!$option || !isset($option['retired'])) return false;
		return $option['retired'] == '1';
	}

	public function get_permalink() {
		if ( !$this->primary ) return '';
		return get_category_link($this->series_id);
	}

	public function get_feed_permalink() {
		if ( !$this->primary ) return '';
		return get_category_feed_link($this->series_id);
	}

	public function get_itunes_subscription_url() {
		// itunes_url
		$powerpress_feed = get_option("powerpress_cat_feed_{$this->series_id}");
		// var_dump($powerpress_general_settings);
		$url = $powerpress_feed['itunes_url'];
		
		$url = apply_filters('get_itunes_subscription_url', $url, $this);

		if ( empty($url) ) return '';

		return $url;

	}

}