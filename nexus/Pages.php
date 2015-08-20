<?php

namespace Nexus;

class Pages {

	use Singleton;

	private $pages = array();

	private function __construct() {

		$this->register();

	}

	private function register() {

		$this->pages['main'] = new \Nexus\Pages\MainPage();
		$this->pages['general'] = new \Nexus\Pages\GeneralSettingsPage();
		$this->pages['series_list'] = new \Nexus\Pages\SeriesListPage();

		$this->pages['series'] = array();
		$series_ids = \Nexus\Series::get_series_ids();

		foreach ($series_ids as $series_id) {
			$this->pages['series'][$series_id] = new \Nexus\Pages\SeriesSettingsPage($series_id);
		}

	}


}