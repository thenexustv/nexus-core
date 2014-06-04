<?php

class Nexus_Playboard_Dashboard {

	private $slug = 'nexus-playboard-dashboard';

	public function __construct() {
		add_action('wp_dashboard_setup', array($this, 'setup'));
	}

	public function setup() {
		wp_add_dashboard_widget($this->slug, 'Playboard', array($this, 'display'));
	}

	public function display() {
		$playboard = Nexus_Playboard::get_instance()->get_data();
		include(NEXUS_CORE_VIEWS . '/dashboard-playboard.php');
	}

}