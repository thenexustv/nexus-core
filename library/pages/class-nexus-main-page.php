<?php

class Nexus_Main_Page extends Nexus_Page {

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
		$view = new Nexus_View(NEXUS_CORE_VIEWS . 'admin.php');

		$view->render();
	}

}

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
		$view = new Nexus_View(NEXUS_CORE_VIEWS . 'page-series-list.php');

		$view->render();
	}

}

class Nexus_General_Settings_Page extends Nexus_Settings_Page {

	private $page_slug = 'nexus-core-general-settings';
	private $page_hook;

	public function __construct() {
		
		$settings = Nexus_Settings::get_instance()->get_general_settings();
		
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

class Nexus_Series_Settings_Page extends Nexus_Settings_Page {

	private static $page_slug_template = 'nexus-core-series-%1$s-settings';
	private $page_hook;

	/*
		TODO:
			Get a true Nexus_Series object in the future.
	*/
	private $internal;

	public function __construct($series_id) {

		$this->page_slug = sprintf(self::$page_slug_template, $series_id);

		$settings = Nexus_Settings::get_instance()->get_series_settings($series_id);

		parent::__construct($settings);

		$this->internal = get_category($series_id);

		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_media();
			wp_enqueue_script('jquery-ui-autocomplete');
		});

	}

	public function initialize() {

		register_setting(
			$this->get_settings()->get_key(),
			$this->get_settings()->get_key(),
			array($this, 'sanitize')
		);

		$feed_section = 'feed-settings';

		add_settings_section(
			$feed_section,
			'General Feed Settings',
			function(){echo "Feed specific settings for this particular series.";},
			$this->page_hook
		);

		$this->add_setting('feed-title', 'Feed Title', $feed_section, 'render_text_field');
		$this->add_setting('feed-description', 'Feed Description', $feed_section, 'render_text_field');
		$this->add_setting('feed-landing-url', 'Landing URL', $feed_section, 'render_text_field');
		$this->add_setting('feed-geographic-location', 'Geographic Location', $feed_section, 'render_text_field');
		$this->add_setting('feed-episode-frequency', 'Episode Frequency', $feed_section, 'render_text_field');
		$this->add_setting('feed-image-url', 'Feed Image URL', $feed_section, 'render_text_field');

		$itunes_section = 'iTunes Section';

		add_settings_section(
			$itunes_section,
			'iTunes Feed Settings',
			function(){echo "Additional iTunes for feed specific settings for this particular series.";},
			$this->page_hook
		);

		$this->add_setting('itunes-subscription-url', 'iTunes Subscription URL', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-subtitle', 'iTunes Subtitle', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-summary', 'iTunes Summary', $itunes_section, 'render_text_area');
		$this->add_setting('itunes-keywords', 'iTunes Keywords', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-category1', 'iTunes Category Tier 1', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-category2', 'iTunes Category Tier 2A', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-category3', 'iTunes Category Tier 2B', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-explicit', 'iTunes Explicit', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-email', 'iTunes Email Contact', $itunes_section, 'render_text_field');
		$this->add_setting('itunes-image-url', 'iTunes Image URL', $itunes_section, 'render_text_field');

		$status_section = 'status_section';

		add_settings_section(
			$status_section,
			'Status Section',
			function(){echo "Defines the status for this podcast.";},
			$this->page_hook
		);

		$this->add_setting('series-retired', 'Retired', $status_section, 'render_check_box');
		$this->add_setting('series-hiatus', 'Hiatus', $status_section, 'render_check_box');

		$special_section = 'special_section';

		add_settings_section(
			$special_section,
			'Special Section',
			function(){echo "Defines special data for each series.";},
			$this->page_hook
		);

		$this->add_setting('series-default-album-art', 'Default Album Art', $special_section, 'render_series_default_album_art');
		$this->add_setting('series-default-hosts', 'Default Hosts', $special_section, 'render_series_default_hosts');

	}

	private function add_setting($name, $title, $section, $render) {
		add_settings_field(
			$name,
			$title,
			array($this, $render),
			$this->page_hook,
			$section,
			$name
		);
	}

	public function add_page() {

		$name = sprintf('Series Settings &raquo; %1$s', $this->internal->name);

		$this->page_hook = add_submenu_page(
			'options.php',
			$name,
			$name,
			'read',
			$this->page_slug,
			array( $this, 'render' )
		);
	}

	public function sanitize($settings) {

		return $settings;
	}

	public function render() {
		include(NEXUS_CORE_VIEWS . 'page-series-settings.php');
		var_dump($this->get_settings());
	}

	// render generators

	public function render_text_field($key) {
		$template = sprintf('<input type="text" name="%s" id="%s" value="%s" />',
			$this->get_field_name($key),
			$this->get_field_name($key),
			$this->get_field_value($key)
		);
		echo $template;
	}

	public function render_text_area($key) {
		$template = sprintf('<textarea rows="5" cols="30" name="%s" id="%s">%s</textarea>',
			$this->get_field_name($key),
			$this->get_field_name($key),
			$this->get_field_value($key)
		);
		echo $template;
	}

	public function render_check_box($key) {
		$template = sprintf('<input type="checkbox" name="%s" id="%s" value="%s" %s />',
			$this->get_field_name($key),
			$this->get_field_name($key),
			'1',
			checked($this->get_field_value($key), '1' , false)
		);
		echo $template;
	}

	// render fields below

	public function render_series_default_album_art($key) {
		$template = sprintf('<input type="type" readonly="readonly" value="%3$s" id="%1$s_readonly" />
			<input type="hidden" name="%1$s" id="%2$s" value="%3$s" />
			<input type="button" name="_set-post-thumbnail" id="set-post-thumbnail" value="Select Image" data-field="%2$s" />',
			$this->get_field_name($key),
			$this->get_field_id($key),
			$this->get_field_value($key)
		);
		echo $template;
		/*
			TODO:
				add a preview of the currently selected default album art
		*/
	}

	public function render_series_default_hosts($key) {

	}

}