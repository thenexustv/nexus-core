<?php

namespace Nexus;

class Metaboxes {

	use Singleton;

	private $metaboxes = array();

	private function __construct() {

		add_action('admin_init', array($this, 'register'));

	}

	public function register() {

		$this->metaboxes['podcast-media'] = new \Nexus\Metaboxes\MediaMetabox();

	}


}



