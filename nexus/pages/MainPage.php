<?php

namespace Nexus\Pages;

class MainPage extends AbstractPage {

	private $page_slug = 'nexus-core-main';
	private $page_hook;

	public function __construct() {
		parent::__construct();
	}

	public function initialize() {

	}

	public function add_page() {

		$this->page_hook = add_menu_page(
			'Nexus Core',
			'Nexus Core',
			'read',
			$this->page_slug,
			array( $this, 'render' )
		);

	}

	public function render() {
		include(NEXUS_CORE_VIEWS . 'page-admin-default.php');
	}

}
