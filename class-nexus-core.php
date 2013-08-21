<?php
/**
 * Nexus Core.
 * This is the core of the Nexus.
 */

class Nexus_Core {

	use Nexus_Singleton;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.0.1
	 * @var     string
	 */
	protected $version = '0.0.1';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    0.0.1
	 * @var      string
	 */
	protected $plugin_slug = 'nexus-core';

	protected $class_prefix = 'nexus';

	public function get_prefix($key = null) {
		return $this->class_prefix . (!empty($key) ? "-$key" : '' );
	}

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    0.0.1
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     0.0.1
	 */
	private function __construct() {

		// Check for required theme and plugins.
		add_action('admin_init', array($this, 'installation_check'));
		// Add the options page and menu item.
		add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
		// Remove unneeded admin items.
		add_action('admin_menu', array($this, 'remove_admin_menu_items'));

		// Add admin script and styles.
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		if (is_admin()) {
			add_filter('the_title', array($this, 'admin_format_episode_title'));
			add_action('save_post', array($this, 'save_episode_number'), 11, 2);
		}
		add_filter('wp_title', array($this, 'page_format_episode_title'));

		// Load public-facing style sheet and JavaScript.
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Register core taxonomies: episodes, episode_attributes and people.
		add_action('init', array($this, 'register_custom_taxonomies'));

		// Clean the header, regardless of theme.
		add_action('init', array($this, 'header_cleanup'));
		add_filter('body_class', array($this, 'add_body_classes'));

		add_action('wp_before_admin_bar_render', array($this, 'remove_admin_bar_items'));

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
		if ( $object instanceof WP_Post ) {
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
		$prefix = $this->get_prefix('episode-number');

		if ( $post->post_type != 'episode' || !current_user_can('edit_post', $post_id ) ) return $post_id;

		$slug = $post->post_name;
		$number = $this->parse_episode_number($slug);
		if ( false == $number ) return $post_id;

		$number = apply_filters('nexus_episode_number', $number);
		do_action('before_save_episode_number', $number);
		update_post_meta($post_id, $prefix, $number);
	}

	/**
	 * Registers core custom post taxonomies.
	 * @since 0.0.1
	 */
	public function register_custom_taxonomies() {
		$this->register_episodes();
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
	 * Ensures the required plugins and themes are activated.
	 * @since 0.0.1
	 */
	public function installation_check() {

		if ( !is_admin() || !current_user_can('manage_options') ) {
			return;
		}

		// $jetpack = 'jetpack'; // also confirm some jetpack functionality is present
		$powerpress = is_plugin_active('powerpress/powerpress.php');
		$theme = wp_get_theme() == 'Coprime';

		if (false == $powerpress) {
			echo('<div class="error"><p>PowerPress is not active! Please activate PowerPress.</p></div>');
		}
		if (false == $theme) {
			echo('<div class="error"><p>Convergence Theme is not active! Please activate the Convergence Theme.</p></div>');
		}
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
		wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     0.0.1
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
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

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    0.0.1
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Nexus Core', $this->plugin_slug ),
			__( 'Nexus Core', $this->plugin_slug ),
			'read',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	public function page_format_episode_title($title) {
		global $post;
		if (!$post || 'episode' != $post->post_type || is_feed()) return $title;
		return $this->format_episode_title($post);
	}

	public function admin_format_episode_title($title) {
		global $post;
		$screen = get_current_screen();

		if ('edit-episode' != $screen->id || !$post || 'episode' != $post->post_type) return $title; 

		return $this->format_episode_title($post);
	}

	public function format_episode_title($object = null) {
		global $wp_query;
		if ( $object instanceof WP_Post ) {
			if ( 'episode' != $object->post_type ) return $object->title;
			$id = $object->ID;
			$number = $this->get_episode_number($object);
			if ( false == $number ) {
				$number = 'X';
			}
			$categories = get_the_category($id);
			$category = 'Episode';
			if ( isset($categories[0]) && strtolower($categories[0]->cat_name) != 'uncategorized' ) {
				$category = $categories[0]->cat_name;
			}
			$title = $object->post_title;

			$formatted_title = "$category #$number: $title";
			return $formatted_title;
		} elseif ( is_numeric($object) ) {
			return $this->format_episode_title( get_post($object) );
		} elseif ( isset($wp_query->post) ) {
			return $this->format_episode_title($wp_query->post);
		}
		new WP_Error('no_post', 'No Episode Title Available');
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.0.1
	 */
	public function display_plugin_admin_page() {
		include( NEXUS_VIEWS . '/admin.php' );
	}

	/**
	 * Creates a human readable time, relative to supplied data
	 * @param type $from 
	 * @param type $to 
	 * @return string
	 */
	public static function human_time_difference( $from, $to = '' ) {
		if ( empty($to) )
			$to = time();
		$diff = (int) abs($to - $from);
		if ($diff <= 3600) {
			$mins = round($diff / 60);
			if ($mins <= 1) {
				$mins = 1;
			}
			/* translators: min=minute */
			$since = sprintf(_n('%s minute', '%s minutes', $mins), $mins);
		} else if (($diff <= 86400) && ($diff > 3600)) {
			$hours = round($diff / 3600);
			if ($hours <= 1) {
				$hours = 1;
			}
			$since = sprintf(_n('%s hour', '%s hours', $hours), $hours);
		} else if ($diff >= 86400 && $diff < 604800) {
			$days = round($diff / 86400);
			if ($days <= 1) {
				$days = 1;
			}
			$since = sprintf(_n('%s day', '%s days', $days), $days);
		} else if ( $diff >= 604800 && $diff < 2629743 ) {
	        $weeks = round($diff / 604800);
	        if ($weeks <= 1) {
	            $weeks = 1;
	        }
	        $since = sprintf(_n('%s week', '%s weeks', $weeks), $weeks);
	    } else if ( $diff >= 2629743 && $diff < 31556926  ) {
	        $months = round($diff / 2629743);
	        if ($months <= 1) {
	            $months = 1;
	        }
	        $since = sprintf(_n('%s month', '%s months', $months), $months);
	    } else {
	        $years = round($diff / 31556926);
	        if ($years <= 1) {
	            $years = 1;
	        }
	        $since = sprintf(_n('%s year', '%s years', $years), $years);
	    }
		return $since;
	}

	public static function _human_length($length) {
		/* this works for standard powerpress times 01:23:45 | hour | minute | second */
		$parts = explode(':', $length);
		$times = array( array('hour', 'hours'), array('minute', 'minutes'), array('second, seconds') );
		$output = '';
		/* ignore seconds */
		for ($i = 0; $i < 2; $i++) {
			$value = (int)$parts[$i];
			if ( $value == 0 ) continue;
			$word = ( $value == 1 ? $times[$i][0] : $times[$i][1] );
			$output = $output . ($value . ' ' . $word . ' ');
		}

		return trim($output);
	}

	public static function human_filesize($size) {
		$base = 1024;
		$sizes = array('B', 'KB', 'MB', 'GB', 'TB');
		$place = 0;
		for (; $size > $base; $place++) { 
			$size /= $base;
		}
		return round($size, 2) . ' ' . $sizes[$place];
	}

}