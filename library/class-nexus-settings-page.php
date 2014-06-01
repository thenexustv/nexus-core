<?php

abstract class Nexus_Settings_Page extends Nexus_Page {

	private $page_slug;
	private $page_hook;

	private $settings;

  //   public static function register($page = null) {

		// if ( null === $page ) {
	 //        $page = new static( Nexus_Settings::get_instance()->get_general_settings() );
		// }
 
  //       $page = parent::register($page);

  //       return $page;
  //   }

	public function __construct($settings) {
		parent::__construct();

		$this->settings = $settings;

	}	

	abstract public function sanitize($settings);

	public function get_field_name($name) {
		return sprintf('%s[%s]', $this->settings->get_key(), $name);
	}

	protected function get_field_id( $id ) {
		return sprintf( '%s__%s', $this->settings->get_key(), $id );
	}	

	public function get_field_value($name, $default = null) {
		if ( $this->settings->is_set($name) ) {
			return $this->settings->get($name);
		}

		return $default;
	}

	public function get_settings() {
		return $this->settings;
	}

	public function render_blank() {}

}