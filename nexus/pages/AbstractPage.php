<?php

namespace Nexus\Pages;

abstract class AbstractPage {

	private $page_slug;
	private $page_hook;

	public function __construct() {
		$this->register();
	}

	public function register() {
		add_action('admin_init', array($this, 'initialize'));
		add_action('admin_menu', array($this, 'add_page'));
	}

	abstract public function initialize();

	abstract public function add_page();

	abstract public function render();

	public function get_slug() {
		return $this->page_slug;
	}

	public function get_hook() {
		return $this->page_hook;
	}

}
