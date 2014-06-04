<?php

class Nexus_Pages {

	use Nexus_Singleton;

	private $pages = array();

	private function __construct() {

		$this->register();

	}

	public function register() {

		$pages['main'] = new Nexus_Main_Page();
		$pages['general'] = new Nexus_General_Settings_Page();
		$pages['series_list'] = new Nexus_Series_List_Page();

		$pages['series'] = array();
		$series_ids = Nexus_Series::get_series_ids();

		foreach ($series_ids as $series_id) {
			$pages['series'][$series_id] = new Nexus_Series_Settings_Page($series_id);
		}

	}


}