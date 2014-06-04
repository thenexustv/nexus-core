<?php

class Nexus_Series_Settings_Page extends Nexus_Settings_Page {

	private static $page_slug_template = 'nexus-core-series-%1$s-settings';
	private $page_hook;

	private $series;

	public function __construct($series_id) {

		$this->page_slug = sprintf(self::$page_slug_template, $series_id);

		$settings = Nexus_Settings::get_instance()->get_series_settings($series_id);

		parent::__construct($settings);

		$this->series = Nexus_Series::get_by_series($series_id);
		
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
		$this->add_setting('feed-description', 'Feed Description', $feed_section, 'render_text_area');
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

		$name = sprintf('Series Settings &raquo; %1$s', $this->series->get_name());

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
		global $post;
		if ( is_object($post) == false ) {
			$post = new stdClass();
			$post->ID = '-1';
		}
		/*
			The above is a monkey-patch solution for the by-default get_the_ID() call in get_the_image.
		*/

		$url = 'data:image/gif;base64,R0lGODlhAQABAIAAAP8AAAAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==';
		$image = false;

		if ( $this->get_settings()->is_set($key) ) {

			$arguments = array(
				'size' => 'medium',
				'link_to_post' => false,
				'format' => 'array',
				'post_id' => $this->get_settings()->get($key)
			);

			$image = get_the_image($arguments);

			if ( $image && !empty($image) ) {
				$url = $image['url'];
			}

		}

		$template = sprintf('
			<div class="set-series-featured-image">
				<input type="hidden" name="%1$s" class="%2$s image-id" value="%3$s" />
				<img src="%4$s" class="image-preview" /><br />
				<input type="button" class="set-post-thumbnail" value="Select Image" data-field="%2$s" />
			</div>',
			$this->get_field_name($key),
			$this->get_field_id($key),
			$this->get_field_value($key),
			$url
		);
		echo $template;
	}

	public function render_series_default_hosts($key) {
		$html_key = sprintf('%s[]', $this->get_field_name($key));
		$template_key = sprintf('%s__template', $this->get_field_id($key));
		$json_key = sprintf('%s__inflate', $key);
		
		$person_ids = array();
		$data = array();
		
		if ( $this->get_settings()->is_set($key) ) {
			$person_ids = $this->get_field_value($key);
		}

		foreach($person_ids as $person_id) {
			$person = Nexus_Person::factory($person_id);
			$data[] = array('label' => $person->get_name(), 'value' => $person_id);
		}

		$underscore_template = sprintf('
		<script type="text/template" class="template people-template %1$s">
			<div class="person-box">
				<span class="label"><strong><%%= label %%></strong> </span>
				<a class="remove-person" href="#">Remove</a><input type="hidden" name="%2$s" value="<%%= value %%>" />
			</div>
		</script>',
			$template_key,
			$html_key
		);

		$json_template = sprintf('<script type="application/javascript" class="people-list-inflate %1$s">%2$s</script>',
			$this->get_field_id($json_key),
			json_encode($data)
		);

		$form_template = sprintf('
			<div class="people-selector">
				%3$s
				%4$s
				<input type="text" class="text-selector" placeholder="type to search" data-template-key="%1$s" data-inflate-key="%2$s" />
				<div class="people-list hidden"></div>
			</div>
			',
			$template_key,
			$json_key,
			$underscore_template,
			$json_template
		);

		// output

		echo $form_template;
	}

}