<?php

class Nexus_Person {

	public static function factory($object) {
		global $wp_query;

		if ( null == self::$person_core ) {
			self::$person_core = Nexus_Core::get_instance();
		}

		if ( $object instanceof WP_Post ) {

			if ( 'person' != $object->post_type ) new WP_Error('not_person', 'Not A Person');
			return new self($object->ID);

		} elseif ( is_numeric($object) ) {
			return self::factory(get_post($object));
		} elseif ( isset($wp_query->post) ) {
			return self::factory($wp_query->post);
		}

		new WP_Error('not_person', 'Not An Person');
	}

	private static $person_core;

	private $post_id;

	private $post;

	private function __construct($post_id) {
		$this->post_id = $post_id;
		$this->post = get_post($post_id);
	}

	public function get_id() {
		return $this->post_id;
	}

	public function get_name() {
		return $this->post->post_title;
	}

	public function get_email() {
		$meta = get_post_meta($this->post_id, 'nexus-people-email', true);
		return $meta;
	}

	public function get_twitter_url() {
		$meta = get_post_meta($this->post_id, 'nexus-people-twitter-url', true);
		return $meta;
	}

	public function get_twitter_handle() {
		$url = $this->get_twitter_url();
		$parts = explode('/', $url);
		return end($parts);
	}

	public function get_googleplus_url() {
		$meta = get_post_meta($this->post_id, 'nexus-people-googleplus-url', true);
		return $meta;
	}

	public function get_website_url() {
		$meta = get_post_meta($this->post_id, 'nexus-people-website-url', true);
		return $meta;
	}

	public function get_permalink() {
		return get_permalink($this->post_id);
	}

	public function get_content() {
		return $this->post->post_content;
	}


}