<?php
/*
Plugin Name: Nexus Core
Plugin URI: http://thenexus.tv/?nexus-core
Description: The core of the Nexus.
Version: 1.1.0
Author:
Author URI: http://ryanrampersad.com
License: MIT
*/

if (!defined('ABSPATH')) {
	exit();
}

$loader = require('vendor/autoload.php');

define('NEXUS_CORE', __FILE__);
define('NEXUS_CORE_PATH', plugin_dir_path( __FILE__ ));
define('NEXUS_CORE_URL', plugin_dir_url( __FILE__ ));
define('NEXUS_CORE_LIBRARY', NEXUS_CORE_PATH . 'library/');
define('NEXUS_CORE_VIEWS', NEXUS_CORE_PATH . 'views/');
define('NEXUS_CORE_MEDIA_VIEWS', NEXUS_CORE_PATH . 'views/media/');
define('NEXUS_CORE_METABOXES', NEXUS_CORE_LIBRARY . 'metaboxes/');
define('NEXUS_CORE_PAGES', NEXUS_CORE_LIBRARY . 'pages/');
define('NEXUS_CORE_SETTINGS', NEXUS_CORE_LIBRARY . 'settings/');
define('NEXUS_CORE_EXTENSIONS', NEXUS_CORE_LIBRARY . 'extensions/');
define('NEXUS_CORE_DASHBOARD', NEXUS_CORE_LIBRARY . 'dashboard/');
define('NEXUS_CORE_VENDOR', NEXUS_CORE_PATH . 'vendor/');

define('NEXUS_CORE_JS', NEXUS_CORE_URL . 'js/');
define('NEXUS_CORE_CSS', NEXUS_CORE_URL . 'css/');

// // starter
\Nexus\Core::get_instance();
\Nexus\Feeds::get_instance();
\Nexus\Pages::get_instance();
\Nexus\Settings::get_instance();
\Nexus\Dashboard::get_instance();
\Nexus\Media::get_instance();

\Nexus\Metaboxes::get_instance();
\Nexus\Metaboxes\EpisodeMetabox::get_instance();
\Nexus\Metaboxes\PeopleMetabox::get_instance();
\Nexus\Metaboxes\EpisodePeopleMetabox::get_instance();

\Nexus\Extensions\Playboard::get_instance();
\Nexus\Extensions\MostRecent::get_instance();
