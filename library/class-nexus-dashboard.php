<?php

class Nexus_Dashboard {

	use Nexus_Singleton;

	private $widgets = array();

	private function __construct() {
		add_action('admin_init', array($this, 'register'));
	}

	public function register() {
		$this->widgets['playboard'] = new Nexus_Playboard_Dashboard();
		$this->widgets['most-recent'] = new Nexus_Most_Recent_Dashboard();
	}



	
}