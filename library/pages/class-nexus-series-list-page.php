<?php

class Nexus_Series_List_Page extends Nexus_Page {

	private $page_slug = 'nexus-core-series-list';
	private $page_hook;

	public function __construct() {
		parent::__construct();
	}

	public function initialize() {

	}

	public function add_page() {

		$this->page_hook = add_submenu_page(
			'nexus-core-main',
			'Series Settings',
			'Series',
			'read',
			$this->page_slug,
			array( $this, 'render' )
		);
	}

	public function render() {
		include(NEXUS_CORE_VIEWS . 'page-series-list.php');
	}

}