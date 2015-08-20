<?php

namespace Nexus;

class Core {

	use Singleton;

	protected $plugin_slug = 'nexus-core';

	protected $class_prefix = 'nexus';

	public function get_prefix($key = null) {
		return $this->class_prefix . (!empty($key) ? "-$key" : '' );
	}

	protected $version;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     0.0.1
	 */
	private function __construct() {

		$this->setup();

		// Remove unneeded admin items.
		add_action('admin_menu', array($this, 'remove_admin_menu_items'));

		// Add admin script and styles.
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		// Clean up the dashboard
		add_action('wp_dashboard_setup', array($this, 'remove_dashboard_widgets'));

		if (is_admin()) {
			add_filter('the_title', array($this, 'admin_format_episode_title'));
			add_action('save_post', array($this, 'save_episode_number'), 11, 2);
		}
		add_filter('wp_title', array($this, 'page_format_episode_title'));
		add_filter('wp_title', array($this, 'format_site_wide_titles'));

		// Load public-facing style sheet and JavaScript.
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Register core taxonomies: episodes, episode_attributes and people.
		add_action('init', array($this, 'register_custom_taxonomies'));

		// Clean the header, regardless of theme.
		add_action('init', array($this, 'header_cleanup'));
		add_filter('body_class', array($this, 'add_body_classes'));

		add_action('wp_before_admin_bar_render', array($this, 'remove_admin_bar_items'));

		add_filter('after_tracking_url', array($this, 'modify_after_tracking_url'), 10, 2);

		add_filter('pre_get_posts', array($this, 'filter_search_results'));

		add_filter('admin_footer_text', array($this, 'modify_admin_version_footer'));

		// handle Person rewriting for hosts and guests
		add_action('init', array($this, 'people_rewrite'));
		add_action('query_vars', array($this, 'people_rewrite_query_vars'));
		add_action('pre_get_posts', array($this, 'people_pre_get_posts'));
		add_filter('redirect_canonical', array($this, 'people_paginate_redirect_canonical'));

		// Open graph handling
		add_filter('jetpack_open_graph_tags', array($this, 'modify_og_img_photon'), 11, 2);
		add_filter('jetpack_open_graph_tags', array($this, 'modify_open_graph'), 10, 2);
		add_filter('jetpack_open_graph_output', array($this, 'modify_twitter_site_card'));
	}

	/*
		Forced Open Graph tags to be served with Photon.
		https://github.com/Automattic/jetpack/blob/2f6f4a418eb7177a3892994e98fdbba46b7822f9/class.photon.php#L525
	*/
	public function modify_og_img_photon($tags, $parameters) {

		if ( !class_exists( 'Jetpack' ) ) {
			return $tags;
		}

		if ( empty( $tags['og:image'] ) ) {
			return $tags;
		}

		// $photon_args = array(
		// 	'fit' => sprintf( '%d,%d', 2 * $parameters['image_width'], 2 * $parameters['image_height'] ),
		// );
		$photon_args = array();

		if ( is_array( $tags['og:image'] ) ) {
			$images = array();
			foreach ( $tags['og:image'] as $image ) {
				$images[] = jetpack_photon_url( $image, $photon_args );
			}
			$tags['og:image'] = $images;
		} else {
			$tags['og:image'] = jetpack_photon_url( $tags['og:image'], $photon_args );
		}

		return $tags;
	}

	/*
		Modify Jetpack Open Graph support.
	*/
	public function modify_open_graph($tags, $sizes) {

		if ( is_home() || is_front_page() ) {
			$tags['og:type'] = 'website';
			$tags['og:title'] = $this->format_site_wide_titles('');

			// You should feel horrible for using a URL directly
			$tags['og:image'] = 'http://s3.amazonaws.com/the-nexus-tv/static/album-art/nx-logo/large-nx-true.png';
		}

		if ( is_singular('episode') ) {
			$tags['og:title'] = $this->page_format_episode_title('');
		}
		return $tags;

	}

	/*
		TODO:
		Twitter features might open up soon to filtering and/or actions.
		https://github.com/Automattic/jetpack/blob/4a22379af056b5728e95e2ab43e4162c58cc75d4/functions.twitter-cards.php
	*/
	public function modify_twitter_site_card($output) {
		if ( stripos($output, 'twitter:site') ) {
			return '<meta name="twitter:site" content="@thenexustv" />';
		}
		return $output;
	}

	public function people_paginate_redirect_canonical($url) {
		if ( is_singular('person') ) {
			$url = false;
		}

		return $url;
	}

	public function people_rewrite() {

		add_rewrite_rule('person/guests/page/([0-9]{1,})/?$', 'index.php?post_type=person&target=guests&paged=$matches[1]', 'top');
		add_rewrite_rule('person/guests', 'index.php?post_type=person&target=guests', 'top');

		add_rewrite_rule('person/hosts/page/([0-9]{1,})/?$', 'index.php?post_type=person&target=hosts&paged=$matches[1]', 'top');
		add_rewrite_rule('person/hosts', 'index.php?post_type=person&target=hosts', 'top');

	}

	public function people_rewrite_query_vars($query_vars) {
		$query_vars[] = 'target';
		return $query_vars;
	}

	public function people_pre_get_posts($query) {
		if ( $query->is_post_type_archive('person') && !is_admin() ) {

			$query->set('posts_per_page', 12);
			$query->set('orderby', 'title');
			$query->set('order', 'ASC');
			$query->set('meta_key', 'nexus-people-host');

			if ( isset($query->query_vars['target']) && $query->query_vars['target'] == 'hosts' ) {
				$query->set('meta_key', 'nexus-people-host');
				$query->set('meta_value', '1');
			}
			else if ( isset($query->query_vars['target']) && $query->query_vars['target'] == 'guests' ) {
				$query->set('meta_key', 'nexus-people-host');
				$query->set('meta_value', '0');
			}

			add_filter('posts_orderby', array($this, 'people_pre_get_posts_orderby'));
		}

		return $query;
	}

	public function people_pre_get_posts_orderby($orderby) {
		global $wpdb;
		$orderby = $wpdb->postmeta . '.meta_value DESC, ' . $orderby;

		remove_filter('posts_orderby', array($this, 'people_pre_get_posts_orderby'));

		return $orderby;
	}


	private function setup() {
		$theme = wp_get_theme(); // get the current theme's data
		$this->version = $theme->Version;
	}

	public function remove_dashboard_widgets() {
		global $wp_meta_boxes;

		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);

		remove_meta_box('dashboard_activity', 'dashboard', 'normal');

	}

	public function filter_search_results($query) {
		if ( $query->is_search && false == (defined('DOING_AJAX') && DOING_AJAX) ) {
			$query->set('post_type', 'episode');
		}
		return $query;
	}

	public function modify_after_tracking_url($redirect, $url) {
		if ( current_user_can('manage_options') || isset($_GET['nt']) ) return $url;
		return $redirect;
	}

	public function add_body_classes($classes) {
		$classes[] = 'nexus-core';
		return $classes;
	}

	/*
		Clean the WordPress header. Because it's dirty.
	*/
	public function header_cleanup() {
		remove_action('wp_head', 'wp_generator', 1);
		remove_action('wp_head', 'hybrid_meta_template', 1);
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'index_rel_link' );
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		remove_action( 'wp_head', 'wp_generator' );
		add_filter('style_loader_src', array($this, 'remove_version'), 9999 );
		add_filter('script_loader_src', array($this, 'remove_version'), 9999 );
		add_filter('style_loader_tag', array($this, 'fix_link_quotes'));
		// when the day comes, add script filtering too
	}

	public function remove_version($source) {
		if ( strpos($source, 'ver=') ) {
			$source = remove_query_arg('ver', $source);
		}
		return $source;
	}

	// this will fix the annoying singular quotes
	public function fix_link_quotes($structure) {
		$fixed = str_replace("'", '"', $structure);
		return $fixed;
	}

	/**
	 * Gets the episode number from the permalink by stripping away the front alpha-characters.
	 * @since 0.0.1
	 * @return string
	 */
	function get_episode_number($object = null) {
		global $wp_query;
		$prefix = $this->get_prefix('episode-number');
		if ( $object instanceof \WP_Post ) {
			$number = get_post_meta($object->ID, 'nexus-episode-number', true);
			if (!is_numeric($number)) {
				// this will attempt at a straight up parsing
				return $this->get_episode_number($object->post_name);
			}
			return $number;
		} elseif ( is_numeric($object) ) {
			return $this->get_episode_number(get_post($object));
		} elseif ( is_string($object) ) {
			$number = $this->parse_episode_number($object);
			return $number;
		} elseif ( isset($wp_query->post) ) {
			return $this->get_episode_number($wp_query->post);
		}
		return false;
	}

	function parse_episode_number($string) {
		if ( stripos($string, 'http://') > 0 ) {
			$parts = explode("/", $string);
			$slug = $parts[count($parts)-2];
		} else {
			$slug = $string;
		}
		$value = preg_replace('/[^0-9]/i', '', $slug);
		if (!is_numeric($value)) return false;
		return $value;
	}

	function save_episode_number($post_id, $post) {

		if ( $post->post_type != 'episode' || !current_user_can('edit_post', $post_id ) ) return $post_id;

		$slug = $post->post_name;
		$number = $this->parse_episode_number($slug);
		if ( false == $number ) return $post_id;

		$number = apply_filters('Episode_number', $number);
		do_action('before_save_episode_number', $number);
		update_post_meta($post_id, 'Episode_number', $number);
	}

	/**
	 * Registers core custom post taxonomies.
	 * @since 0.0.1
	 */
	public function register_custom_taxonomies() {
		$this->register_episodes();

		// we will always have post thumbnails so why not put this here?
		add_theme_support( 'post-thumbnails', array('episode'));
		// TODO: does this work?

		// no longer registering a series
		$this->register_episode_attributes();
		$this->register_people();
		// allow episodes to appear on category pages
		// this may not be needed in the future
		add_action('pre_get_posts', array($this, 'allowed_post_types'));
	}

	public function allowed_post_types($query) {
		if ($query->is_category) {
			$query->set('post_type', array('episode'));
		}
		return $query;
	}

	/**
	* Adds the paramters to the WP_Tax query to exclude the 'hidden' episode attribute taxonomy.
	*
	* Add 'hidden' Attribute meta to hide an episode from regular visitors.
	*
	* @param type $query
	* @return type
	*/
	function exclude_episode_attribute_hidden($query) {

		if ( is_object($query->tax_query) && $query->tax_query->queries && !is_user_logged_in() ) {
			$tax_query = $query->tax_query->queries;
			$tax_query['hidden'] = array(
		      'taxonomy' => 'episode_attributes',
		      'terms' => 'hidden',
		      'field' => 'slug',
		      'operator' => 'NOT IN'
		    );
			$query->set('tax_query', $tax_query);
		}

		return $query;
	}

	private function register_series() {
		$labels = array(
	        'name' => _x( 'Series', 'series' ),
	        'singular_name' => _x( 'Series', 'series' ),
	        'search_items' => _x( 'Search Series', 'series' ),
	        'popular_items' => _x( 'Popular Series', 'series' ),
	        'all_items' => _x( 'All Series', 'series' ),
	        'parent_item' => _x( 'Parent Series', 'series' ),
	        'parent_item_colon' => _x( 'Parent Series:', 'series' ),
	        'edit_item' => _x( 'Edit Series', 'series' ),
	        'update_item' => _x( 'Update Series', 'series' ),
	        'add_new_item' => _x( 'Add New Series', 'series' ),
	        'new_item_name' => _x( 'New Series', 'series' ),
	        'separate_items_with_commas' => _x( 'Separate series with commas', 'series' ),
	        'add_or_remove_items' => _x( 'Add or remove series', 'series' ),
	        'choose_from_most_used' => _x( 'Choose from the most used series', 'series' ),
	        'menu_name' => _x( 'Series', 'series' ),
    	);

	    $args = array(
	        'labels' => $labels,
	        'public' => true,
	        'show_in_nav_menus' => true,
	        'show_ui' => true,
	        'show_tagcloud' => false,
	        'show_admin_column' => true,
	        'hierarchical' => true,

	        'rewrite' => true,
	        'query_var' => true
	    );

	    register_taxonomy( 'series', array('episode'), $args );
	}

	private function register_episodes() {
	    $labels = array(
	        'name' => _x( 'Episodes', 'episode' ),
	        'singular_name' => _x( 'Episode', 'episode' ),
	        'add_new' => _x( 'Add New', 'episode' ),
	        'add_new_item' => _x( 'Add New Episode', 'episode' ),
	        'edit_item' => _x( 'Edit Episode', 'episode' ),
	        'new_item' => _x( 'New Episode', 'episode' ),
	        'view_item' => _x( 'View Episode', 'episode' ),
	        'search_items' => _x( 'Search Episodes', 'episode' ),
	        'not_found' => _x( 'No episodes found', 'episode' ),
	        'not_found_in_trash' => _x( 'No episodes found in Trash', 'episode' ),
	        'parent_item_colon' => _x( 'Parent Episode:', 'episode' ),
	        'menu_name' => _x( 'Episodes', 'episode' ),
	    );

	    $args = array(
	        'labels' => $labels,
	        'hierarchical' => false,

	        'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
	        'taxonomies' => array( 'category', 'episode_attributes' ),
	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'menu_position' => 5,

	        'show_in_nav_menus' => true,
	        'publicly_queryable' => true,
	        'exclude_from_search' => false,
	        'has_archive' => true,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => true,
	        'capability_type' => 'post'
	    );

	    register_post_type( 'episode', $args );
	}

	private function register_episode_attributes() {
	    $labels = array(
	        'name' => _x( 'Episode Attributes', 'episode_attributes' ),
	        'singular_name' => _x( 'Episode Attribute', 'episode_attributes' ),
	        'search_items' => _x( 'Search Episode Attributes', 'episode_attributes' ),
	        'popular_items' => _x( 'Popular Episode Attributes', 'episode_attributes' ),
	        'all_items' => _x( 'All Episode Attributes', 'episode_attributes' ),
	        'parent_item' => _x( 'Parent Episode Attribute', 'episode_attributes' ),
	        'parent_item_colon' => _x( 'Parent Episode Attribute:', 'episode_attributes' ),
	        'edit_item' => _x( 'Edit Episode Attribute', 'episode_attributes' ),
	        'update_item' => _x( 'Update Episode Attribute', 'episode_attributes' ),
	        'add_new_item' => _x( 'Add New Episode Attribute', 'episode_attributes' ),
	        'new_item_name' => _x( 'New Episode Attribute', 'episode_attributes' ),
	        'separate_items_with_commas' => _x( 'Separate episode attributes with commas', 'episode_attributes' ),
	        'add_or_remove_items' => _x( 'Add or remove Episode Attributes', 'episode_attributes' ),
	        'choose_from_most_used' => _x( 'Choose from most used Episode Attributes', 'episode_attributes' ),
	        'menu_name' => _x( 'Attributes', 'episode_attributes' ),
	    );

	    $args = array(
	        'labels' => $labels,
	        'public' => true,
	        'show_in_nav_menus' => true,
	        'show_ui' => true,
	        'show_tagcloud' => false,
	        'hierarchical' => true,

	        'rewrite' => true,
	        'query_var' => true
	    );

	    register_taxonomy( 'episode_attributes', array('episode'), $args );
	}

	private function register_people() {
		$labels = array(
	        'name' => _x( 'People', 'person' ),
	        'singular_name' => _x( 'Person', 'person' ),
	        'add_new' => _x( 'Add New', 'person' ),
	        'add_new_item' => _x( 'Add New Person', 'person' ),
	        'edit_item' => _x( 'Edit Person', 'person' ),
	        'new_item' => _x( 'New Person', 'person' ),
	        'view_item' => _x( 'View Person', 'person' ),
	        'search_items' => _x( 'Search People', 'person' ),
	        'not_found' => _x( 'No people found', 'person' ),
	        'not_found_in_trash' => _x( 'No people found in Trash', 'person' ),
	        'parent_item_colon' => _x( 'Parent Person:', 'person' ),
	        'menu_name' => _x( 'People', 'person' ),
	    );

	    $args = array(
	        'labels' => $labels,
	        'hierarchical' => false,

	        'supports' => array( 'title', 'editor', 'excerpt' ),

	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'menu_position' => 8,

	        'show_in_nav_menus' => true,
	        'publicly_queryable' => true,
	        'exclude_from_search' => false,
	        'has_archive' => true,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => true,
	        'capability_type' => 'post'
	    );

	    register_post_type( 'person', $args );
	}


	/**
	 * Removes extra admin menu items on /wp-admin.
	 * @since 0.0.1
	 */
	public function remove_admin_menu_items() {
		remove_menu_page('edit.php');
		remove_menu_page('link-manager.php');
	}

	/**
	 * Removes extra admin bar items.
	 * @since 0.0.1
	 */
	public function remove_admin_bar_items() {
		global $wp_admin_bar;

		// remove links to old media types in the admin bar
		$wp_admin_bar->remove_menu('new-post', 'new-content');
		$wp_admin_bar->remove_menu('new-link', 'new-content');
		$wp_admin_bar->remove_menu('new-media', 'new-content');
		$wp_admin_bar->remove_menu('new-page', 'new-content');
		$wp_admin_bar->remove_menu('new-user', 'new-content');

		// remove the WordPress logo; it is not needed
		//$wp_admin_bar->remove_menu('wp-logo');

		// we don't need comment support right now, we can change it later
		$wp_admin_bar->remove_menu('comments');

		// experimental - remove items under site-name
		$wp_admin_bar->remove_menu('themes', 'site-name');
		$wp_admin_bar->remove_menu('customize', 'site-name');
		$wp_admin_bar->remove_menu('header', 'site-name');
		$wp_admin_bar->remove_menu('widgets', 'site-name');
		$wp_admin_bar->remove_menu('menus', 'site-name');
	}

	public function get_page_statistics() {

		$theme = wp_get_theme();
		$plugin = get_plugin_data(NEXUS_CORE);
		$timer = timer_stop(0, 2);
		$queries = get_num_queries();
		$memory = Utility::human_filesize(memory_get_usage(true));

		$template = '
		<span class="admin-statistics">
		<a href="https://github.com/thenexustv/nexus-core">Nexus Core %1$s</a> | <a href="https://github.com/thenexustv/coprime">Coprime %2$s</a> | %3$s seconds | %4$s queries | %5$s
		</span>
		';
		$output = sprintf($template, $plugin['Version'], $theme->Version, $timer, $queries, $memory);

		return $output;

	}

	public function modify_admin_version_footer($f) {

		return $this->get_page_statistics();
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.0.1
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.0.1
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.0.1
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     0.0.1
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( $this->plugin_slug .'-admin-styles', NEXUS_CORE_CSS . 'admin.css', array(), $this->version );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     0.0.1
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-admin-script', NEXUS_CORE_JS . 'admin.js', array( 'jquery' ), $this->version );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

	}


	/*
		Page directives. See Nexus_Core_Page.
	*/

	public function format_site_wide_titles($title) {
		$sep = '&#8250;';

		if ( is_front_page() || is_home() ) {
			return "The Nexus $sep Podcasts from the Technological Convergence";
		}

		if ( is_feed() ) {
			return $title;
		}

		if ( is_category() ) {
			$title = "$title $sep Episode Archive";
		}

		if ( is_post_type_archive('episode') ) {
			$title = "Episode Archive";
		}

		if ( is_singular('person') ) {
			$title = "$title";
		}

		if ( is_post_type_archive('person') ) {
			$title = '';
			if ( get_query_var('guests') ) {
				$title = "Guests $sep";
			} elseif ( get_query_var('hosts') ) {
				$title = "Hosts $sep";
			}
			$title = "$title People Archive";
		}

		if ( get_query_var('paged') > 0 ) {
			$page = get_query_var('paged');
			$title = "$title $sep Page $page";
		}

		return "$title $sep The Nexus";
	}

	public function page_format_episode_title($title) {
		global $post;
		if (!$post || 'episode' != $post->post_type || is_feed() || is_archive()) return $title;
		return Episode::format_episode_title($post);
	}

	public function admin_format_episode_title($title) {
		global $post;
		$screen = get_current_screen();

		if ('edit-episode' != $screen->id || !$post || 'episode' != $post->post_type) return $title;

		return Episode::format_episode_title($post);
	}

	public function get_version() {
		return $this->version;
	}

}
