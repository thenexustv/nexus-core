<?php

class Nexus_Settings {

	use Nexus_Singleton;

	private $general_settings;

	private $series = array();

	public function __construct() {

		$this->general_settings = new Nexus_General_Settings();

		
		$series_ids = Nexus_Series::get_series_ids();

		foreach ($series_ids as $id) {
			$this->series[$id] = new Nexus_Series_Settings($id);
		}


	}

	public function get_general_settings() {
		return $this->general_settings;
	}

	public function get_series_settings($series_id) {
		if ( !array_key_exists($series_id, $this->series) ) {
			return false;
		}
		return $this->series[$series_id];

	}
	
}

abstract class Nexus_Setting {

	protected $default_settings = array();

	protected $settings_key;

	protected $settings;

	public function __construct() {

    	add_option(
    		$this->settings_key,
    		$this->default_settings
    	);

    	$values = get_option($this->settings_key, $this->default_settings);

		$this->settings = $values;
	}

	public function get_key() {
		return $this->settings_key;
	}

	public function get($name) {
		return $this->settings[$name];
	}

	public function get_settings() {
		return $this->settings;
	}

	public function is_set($name) {
		return isset($this->settings[$name]);
	}

}

class Nexus_General_Settings extends Nexus_Setting {

	protected $default_settings = array(
		'redirect-url' => ''
	);

	public $settings_key = 'nexus-core-general-settings';

	public function __construct() {
		parent::__construct();
	}

}

class Nexus_Series_Settings extends Nexus_Setting {

	protected $default_settings = array(

		/*
			Feed settings.
				Directly impacting the feed.
		*/
		'feed-title' => '',
		'feed-description' => '',
		'feed-landing-url' => '',
		'feed-geographic-location' => '',
		'feed-episode-frequency' => '',
		'feed-image-url' => '',

		/*
			iTunes settings.
				Directly impacting the iTunes settings in the feeds.
		*/
		'itunes-subscription-url' => '',
		'itunes-subtitle' => '',
		'itunes-summary' => '',
		'itunes-keywords' => '',
		'itunes-category1' => '',
		'itunes-category2' => '',
		'itunes-category3' => '',
		'itunes-explicit' => 'no',
		'itunes-email' => '',
		'itunes-image-url' => '',

		/*
			Series state settings.
		*/
		'series-retired' => false,
		'series-hiatus' => false,

		/*
			What album art should be attached to an episode upon being saved?
		*/
		'series-default-album-art' => '',

		/*
			What are the usual hosts of this episode?
			These hosts are expected to be IDs of People, i.e. Nexus_Person objects
		*/
		'series-default-hosts' => array()

	);	

	public $settings_key;

	public function __construct($series_id) {

		$settings_key = sprintf('nexus-core-series-%1$s-settings', $series_id);

		$this->settings_key = $settings_key;

    	add_option(
    		$this->settings_key,
    		$this->default_settings
    	);

    	$values = get_option($this->settings_key, $this->default_settings);

		$this->settings = $values;
	}

}