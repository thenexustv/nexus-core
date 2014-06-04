<?php

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