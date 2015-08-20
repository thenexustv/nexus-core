<?php

namespace Nexus;

class Feeds {

	use Singleton;

	private function __construct() {

		add_action('pre_get_posts', array($this, 'include_the_fringe'));
		add_filter('posts_where', array($this, 'feed_delay'));
		add_filter('the_title_rss', array($this, 'feed_title_format'));
		add_filter('the_content_feed', array($this, 'feed_body'));
	
	}

	public function include_the_fringe( $query ) {


		// only target feeds and the main query when the ?fringe is detected
		// TODO: add support for a query_var
		if ( $query->is_feed() && $query->is_main_query() && isset($_GET['fringe']) ) {
			
			global $wp_query;

			$ids = $this->find_fringe_episodes($query);

			$length = count($ids);
			if ($length < $query->get('posts_per_rss')) $length = $query->get('posts_per_rss');

			$query->set('posts_per_rss', $length);
			$query->set('post__in', $ids);

			/*
				The category is set by the first slug in the list,
				we want to include the fringe, but the primary category
				should still take precedence so the category feed and description
				are set properly.
			*/
			$query->set('category_name', join(',', array($query->query['category_name'], 'tf')) );

			// this doesn't solve anything
			$wp_query = $query;
		} 


		return $query;

	}

	private function find_fringe_episodes($query) {

		$qu = clone $query;
		$qu->set('fields', 'ids');
		$posts = $qu->get_posts();
		$ids = array();

		foreach ($posts as $post_id) {
			$id = $post_id;
			$fringe_id = get_post_meta($id, 'nexus-fringe-episode', true); 
			$ids[] = $id;
			if (is_numeric($fringe_id)) $ids[] = $fringe_id;
		}

		wp_reset_query();
		wp_reset_postdata();

		return $ids;

	}	

	public function feed_delay($where) {
		global $wpdb;

		$instant = isset($_GET['instant']) || is_user_logged_in();

		if (is_feed() && !$instant) {
			$now = gmdate('Y-m-d H:i:s');
			$wait = 60 + rand(-10, 10);
			$device = 'MINUTE';
			$where .= " AND TIMESTAMPDIFF($device, $wpdb->posts.post_date_gmt, '$now') > $wait ";
		}

		return $where;
	}

	public function feed_title_format($content) {
		global $wp_query;

		return Nexus_Episode::format_episode_title($wp_query->post->ID);
	}

	public function feed_body($content) {
		global $wp_query;

		$post_id = $wp_query->post->ID;
		$episode = Nexus_Episode::factory($post_id);
		
		// this is required because the excerpt is not in the content itself
		$excerpt = $episode->get_excerpt() . "<br /><br />";

		// NSFW flags
		$nsfw_prepend = '';
		if ($episode->is_nsfw()) {
			$nsfw_prepend = "This episode of {$episode->get_series_name()} has been tagged as <abbr title=\"Not Safe For Work\">NSFW</abbr>. <strong>Please be advised</strong>.";
		}

		// Fringe flags
		$relation_append = '';
		if ( $episode->has_fringe() ) {
			$fringe = Nexus_Episode::factory($episode->get_fringe());
			$relation_append = "This episode of {$episode->get_series_name()} has a Fringe episode. You should really listen to <a href=\"{$fringe->get_permalink()}\">{$fringe->get_formatted_title()}</a>!";
		} elseif ( $episode->is_fringe() && $episode->has_parent() ) {
			$parent = Nexus_Episode::factory($episode->get_parent());
			$relation_append = "The parent of this Fringe episode is <a href=\"{$parent->get_permalink()}\">{$parent->get_formatted_title()}</a>, you should listen!";
		}

		$header = $excerpt . $this->wrap_tag($nsfw_prepend, 'aside') . $this->wrap_tag($content, 'article');
		$related = $this->wrap_tag($relation_append, 'p');
		$contact = 'Listen to more at <a href='.get_bloginfo('url').'>The Nexus</a> and follow us on <a href="http://twitter.com/thenexustv">Twitter</a> and <a href="https://plus.google.com/b/110459364915252571275/110459364915252571275/posts">Google+</a> for our latest episodes and news.';
		$footer = $this->wrap_tag( $related . $this->wrap_tag($contact, 'p') , 'footer');
		
		$content = $header . $footer;

		return $content;

	}

	private function wrap_tag($content, $tag = 'div') {
		if (strlen($content) <= 0) return '';
		return "<{$tag}>\n{$content}\n</{$tag}>\n";
	}


}