<?php

namespace Nexus;

class Media {

	use Singleton;

	/*
		An array of class names for the active media types.
	*/
	private $active_media = array(
		'\\Nexus\\MP3Media',
		'\\Nexus\\StubMedia'
	);

	public function get_media_types() {
		return $this->active_media;
	}

}

abstract class AbstractMedia {


	// data
	private $data;

	// what it needs to handle

	/*

		1. displaying form in media metabox (body)
		1.1. display a title in the tabs section
		2. save information from that form
		3. represent a singular object
		4. display front end representation

	*/


	public static function render_section($metabox) {
		echo(sprintf('<div id="generic-section" class="section">%s</div>', 'Generic Section'));
	}

	public static function render_tab() {
		echo(sprintf('<li><a href="#generic-section">%s</a></li>', 'Generic Tab'));
	}

	public static function save() {

	}

}

class MP3Media extends AbstractMedia {

	public static function get_url() {

	}

	public static function render_section($metabox) {

		$view = new \Nexus\Metaboxes\MetaboxView('mp3media', 'MP3 Media');

		$view->add_field('url', 'Media URL', array(__CLASS__, 'render_field'));
		$view->add_field('filesize', 'Filesize', array(__CLASS__, 'render_text_hidden'));
		$view->add_field('duration', 'Media URL', array(__CLASS__, 'render_text_hidden'));

		$view->render();

	}

	public static function render_field($args, $field, $view) {
		$html = '
			<input type="text" class="widefat" name="%1$s" id="%2$s" value="%3$s" />
		';

		$output = sprintf(
			$html,
			$view->get_field_name($field['id']),
			$view->get_field_id($field['id']),
			''
		);

		return $output;
	}

	public static function render_text_hidden($args, $field, $view) {
		$html = '
			<span class="text">%1$s</span>
			<input type="hidden" name="%2$s" id="%3$s" value="%4$s" />
		';

		$output = sprintf(
				$html,
				'',
				$view->get_field_name($field['id']),
				$view->get_field_id($field['id']),
				''
		);

		return $output;

	}

	public static function render_tab() {
		echo('<li><a href="#mp3media">MP3</a></li>');
	}

}

class StubMedia extends AbstractMedia {

	public static function render_section($metabox) {
		parent::render_section($metabox);
	}

	public static function render_tab() {
		// echo('<li><a href="#stubmedia">Stub</a></li>');
		parent::render_tab();
	}

}
