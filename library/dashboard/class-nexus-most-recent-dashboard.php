<?php

class Nexus_Most_Recent_Dashboard {

	use Nexus_Singleton;

	private $slug = 'nexus-most-recent';

	public function __construct() {
		add_action('wp_dashboard_setup', array($this, 'setup'));
	}

	public function setup() {
		wp_add_dashboard_widget($this->slug, 'Most Recent', array($this, 'display'));
	}

	public function display() {
		$recent = Nexus_Most_Recent::get_instance()->get_data();
		include(NEXUS_CORE_VIEWS . '/dashboard-most-recent.php');
	}

}