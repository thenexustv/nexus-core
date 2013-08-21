<?php

class Nexus_Episode {

	/*
	

	*/
	public static function factory($object) {
		global $wp_query;

		if ( null == self::$core ) {
			self::$core = Nexus_Core::get_instance();
		}

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

	private $id;
	private $post;

	// a simple reference to the core is kept nearby
	private static $core;


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
		return self::$core->get_episode_number($this->id);
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

	public function get_modified_date() {
		return $this->post->post_modified;
	}

	public function get_title() {
		return $this->post->post_title;
	}

	public function get_content() {
		return $this->post->post_content;
	}

	public function get_formatted_title() {
		return self::$core->format_episode_title($this->id);
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
		var_dump($option);
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

	public function has_enclosure($type = 'podcast') {
		$handle = ( in_array($type, array('podcast', '')) ) ? 'enclosure' : "_{$type}:enclosure";
		$meta = get_post_meta($this->id, $handle);
		return !!$meta;
	}

	private function format_enclosure($enclosure) {
		if ( !$enclosure ) return array();
		$formatted = array();

		$enclosure = explode("\n", $enclosure[0]);

		$formatted['url'] = $enclosure[0];
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

	



}