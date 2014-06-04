<?php

class Nexus_Settings {

	use Nexus_Singleton;

	private $general_settings;

	private $series = array();

	public function __construct() {

		$this->register();

	}

	private function register() {

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
