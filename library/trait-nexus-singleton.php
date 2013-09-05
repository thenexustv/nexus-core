<?php

if ( trait_exists('Nexus_Singleton') ) return;

trait Nexus_Singleton {
	// A reference to the single instance of this class
	protected static $instance = null;

	/**
	* Provides access to a single instance of this class.
	* @return	object	A single instance of this class.
	*/
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}