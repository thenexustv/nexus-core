<?php

class Nexus_Pages {

	use Nexus_Singleton;

	private $pages = array();

	private function __construct() {

		$this->register();

	}

	private function register() {

		$this->pages['main'] = new Nexus_Main_Page();
		$this->pages['general'] = new Nexus_General_Settings_Page();
		$this->pages['series_list'] = new Nexus_Series_List_Page();

		$this->pages['series'] = array();
		$series_ids = Nexus_Series::get_series_ids();

		foreach ($series_ids as $series_id) {
			$this->pages['series'][$series_id] = new Nexus_Series_Settings_Page($series_id);
		}

	}


}