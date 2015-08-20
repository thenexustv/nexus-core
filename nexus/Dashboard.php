<?php

namespace Nexus;

class Dashboard {

	use Singleton;

	private $widgets = array();

	private function __construct() {
		add_action('admin_init', array($this, 'register'));
	}

	public function register() {
		$this->widgets['playboard'] = new \Nexus\Dashboard\Playboard();
		$this->widgets['most-recent'] = new \Nexus\Dashboard\MostRecent();
	}



	
}