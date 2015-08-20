<?php

namespace Nexus\Pages;

class GeneralSettingsPage extends SettingsPage {

	private $page_slug = 'nexus-core-general-settings';
	private $page_hook;

	public function __construct() {
		
		$settings = \Nexus\Settings::get_instance()->get_general_settings();

		parent::__construct($settings);

	}	

	public function initialize() {

		register_setting(
			$this->get_settings()->get_key(),
			$this->get_settings()->get_key(),
			array($this, 'sanitize')
		);

		$general_section = 'nexus-core-general-settings';

		add_settings_section(
			'nexus-core-general-settings',
			'General',
			array($this, 'render_blank'),
			$this->page_hook
		);

		add_settings_field(
			'redirect-url',
			'Redirect URL',
			array($this, 'render_redirect_url'),
			$this->page_hook,
			$general_section
		);

	}

	public function add_page() {

		$this->page_hook = add_submenu_page(
			'nexus-core-main',
			'General Settings',
			'General',
			'read',
			$this->page_slug,
			array( $this, 'render' )
		);

	}

	public function sanitize($settings) {

		return $settings;
	}

	public function render() {
		include(NEXUS_CORE_VIEWS . 'page-general-settings.php');
	}

	public function render_redirect_url() {
		$template = sprintf('<input type="text" name="%s" id="%s" value="%s" />',
			$this->get_field_name('redirect-url'),
			$this->get_field_name('redirect-url'),
			$this->get_field_value('redirect-url')
		);
		echo $template;
	}

}