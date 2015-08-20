<?php

namespace Nexus;

class Settings {

	use Singleton;

	private $general_settings;

	private $series = array();

	public function __construct() {

		$this->register();

	}

	private function register() {

		$this->general_settings = new \Nexus\Settings\GeneralSettings();

		$series_ids = Series::get_series_ids();

		foreach ($series_ids as $id) {
			$this->series[$id] = new \Nexus\Settings\SeriesSettings($id);
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
