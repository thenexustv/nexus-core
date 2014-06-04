<?php

class Nexus_Series {

	public static function factory($object = NULL) {
		global $wp_query;

		// _deprecated_function(__METHOD__, '1.1.0', 'Nexus_Series::get_by_episode');

		return self::get_by_episode($object);

	}

	public static function get_by_episode($object = NULL) {
		global $wp_query;
		
		if ( $object instanceof WP_Post && $object->post_type == 'episode' ) {
			return self::_get_by_episode($object->ID);
		} elseif ( is_numeric($object) ) {
			return self::factory(get_post($object));
		} elseif ( isset($wp_query->post) ) {
			return self::factory($wp_query->post);
		}

		return new WP_Error('not_series', 'Not A Series');
	}

	public static function get_by_series($id) {
		return get_the_category_by_ID($id);
	}

	private static function _get_by_episode($post_id) {
		$taxonomy = get_the_category($post_id);
		if ( is_array($taxonomy) && $taxonomy[0] && !empty($taxonomy[0]) ) {
			return new Nexus_Series($taxonomy[0]);
		}
		return new Nexus_Series(array());
	}



	// public function 

	public static function get_series_ids() {
		$ids = array();

		$categories = get_categories();
		
		foreach ($categories as $category) {
			if ( $category->name == 'uncategorized' ) {
				continue;
			}

			$id = $category->term_id;
			$ids[] = $id;
		}

		return $ids;
	}

	// object class
	
	private $series_id;

	private $object;
	private $settings;

	private $initialized = false; // indicates if this object has data

	private function __construct($object) {
		$this->object = $object;
		
		if ( isset($object->term_id) ) {
			$this->series_id = $this->get_object()->term_id;
			$this->initialized = true;
			$this->settings = Nexus_Settings::get_instance()->get_series_settings($object->term_id);
		}
	}

	public function get_object() {
		return $this->object;
	}

	public function is_initialized() {
		return $this->initialized;
	}

	public function get_id() {
		return $this->series_id;
	}

	public function get_settings() {
		return $this->settings;
	}

	public function get_name() {
		if ( false == $this->is_initialized() ) {return '';}
		return $this->get_object()->name;
	}

	public function get_slug() {
		if ( false == $this->is_initialized() ) {return '';}
		return $this->get_object()->slug;
	}

	public function get_description() {
		if ( false == $this->is_initialized() ) {return '';}
		return $this->get_object()->description;
	}

	public function is_retired() {
		$retired = $this->get_settings()->get('series-retired');
		return $option['retired'] == '1';
	}

	public function get_permalink() {
		if ( false == $this->is_initialized() ) {return '';}
		return get_category_link($this->get_id());
	}

	public function get_feed_permalink() {
		if ( false == $this->is_initialized() ) {return '';}
		return get_category_feed_link($this->get_id());
	}

	public function get_itunes_subscription_url() {
		
		$powerpress_feed = get_option("powerpress_cat_feed_{$this->get_id()}");
		
		$url = $powerpress_feed['itunes_url'];
		
		$url = apply_filters('get_itunes_subscription_url', $url, $this);

		if ( empty($url) ) return '';

		return $url;

	}

}