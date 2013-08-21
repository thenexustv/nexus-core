<?php
/*
Plugin Name: Nexus Core
Plugin URI: http://thenexus.tv/?nexus-core
Description: The core of the Nexus.
Version: 0.0.1
Author:
Author URI: http://ryanrampersad.com
License: MIT
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('NEXUS_VIEWS', 'views');

// primary includes
require_once( plugin_dir_path( __FILE__ ) . 'trait-nexus-singleton.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-core.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-series.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-episode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-feed.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-series-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-metabox.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-episode-people-metabox.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-people-metabox.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-episode-metabox.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-playboard.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-nexus-most-recent.php' );

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