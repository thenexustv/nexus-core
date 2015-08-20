<?php

namespace Nexus\Metaboxes;

class MediaMetabox extends AbstractMetabox {

	private $slug = 'nexus-podcast-media-metabox';

	public function __construct() {
		parent::__construct();
		wp_enqueue_script( 'media-script', NEXUS_CORE_JS . 'admin.js', array( 'jquery' ));
	}

	public function add() {
		add_meta_box($this->slug, 'Podcast Media', array($this, 'render'), 'episode', 'default');
	}

	public function save($post_id, $post_object, $updated) {
		
	}

	public function render() {
		
		include(NEXUS_CORE_VIEWS . '/metabox-podcast-media.php');

	}


}

