<?php
/*
Plugin Name: Nexus Core
Plugin URI: http://thenexus.tv/?nexus-core
Description: The core of the Nexus.
Version: 1.0.1
Author:
Author URI: http://ryanrampersad.com
License: MIT
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('NEXUS_CORE', __FILE__);
define('NEXUS_CORE_PATH', plugin_dir_path( __FILE__ ));
define('NEXUS_CORE_LIBRARY', NEXUS_CORE_PATH . 'library/');
define('NEXUS_CORE_VIEWS', NEXUS_CORE_PATH . 'views/');
define('NEXUS_CORE_METABOXES', NEXUS_CORE_LIBRARY . 'metaboxes/');
define('NEXUS_CORE_MODELS', NEXUS_CORE_LIBRARY . 'models/');
define('NEXUS_CORE_WIDGETS', NEXUS_CORE_LIBRARY . 'widgets/');
define('NEXUS_CORE_VENDOR', NEXUS_CORE_PATH . 'vendor/');

define('NEXUS_CORE_JS', NEXUS_CORE_PATH . 'js/');
define('NEXUS_CORE_CSS', NEXUS_CORE_PATH . 'css/');

// generic includes
require_once( NEXUS_CORE_LIBRARY . 'trait-nexus-singleton.php' );
require_once( NEXUS_CORE_LIBRARY . 'class-nexus-metabox.php' );

// core
require_once( NEXUS_CORE_LIBRARY . 'class-nexus-core.php' );

// feeds and settings
require_once( NEXUS_CORE_LIBRARY . 'class-nexus-feed.php' );
require_once( NEXUS_CORE_LIBRARY . 'class-nexus-series-settings.php' );

// metaboxes
require_once( NEXUS_CORE_METABOXES . 'class-nexus-episode-people-metabox.php' );
require_once( NEXUS_CORE_METABOXES . 'class-nexus-people-metabox.php' );
require_once( NEXUS_CORE_METABOXES . 'class-nexus-episode-metabox.php' );

// models
require_once( NEXUS_CORE_MODELS . 'class-nexus-series.php' );
require_once( NEXUS_CORE_MODELS . 'class-nexus-episode.php' );
require_once( NEXUS_CORE_MODELS . 'class-nexus-person.php' );

// widgets
require_once( NEXUS_CORE_WIDGETS . 'class-nexus-playboard.php' );
require_once( NEXUS_CORE_WIDGETS . 'class-nexus-most-recent.php' );

// vendor

require_once( NEXUS_CORE_VENDOR . 'get-the-image/get-the-image.php' );
require_once( NEXUS_CORE_VENDOR . 'loop-pagination/loop-pagination.php' );

// activation and deactivation hooks
register_activation_hook( __FILE__, array( 'Nexus_Core', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Nexus_Core', 'deactivate' ) );

// starter
Nexus_Core::get_instance();
Nexus_Episode_People_Metabox::get_instance();
Nexus_Episode_Metabox::get_instance();
Nexus_People_Metabox::get_instance();
Nexus_Playboard::get_instance();
Nexus_Most_Recent::get_instance();
Nexus_Series_Settings::get_instance();
Nexus_Feed::get_instance();