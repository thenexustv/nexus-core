<?php

namespace Nexus\Settings;

class SeriesSettings extends AbstractSettings {

	protected $default_settings = array(

		/*
			Feed settings.
				Directly impacting the feed.
		*/
		'feed-title' => '',
		'feed-description' => '',
		'feed-landing-url' => '',
		'feed-geographic-location' => '',
		'feed-episode-frequency' => '',
		'feed-image-url' => '',

		/*
			iTunes settings.
				Directly impacting the iTunes settings in the feeds.
		*/
		'itunes-subscription-url' => '',
		'itunes-subtitle' => '',
		'itunes-summary' => '',
		'itunes-keywords' => '',
		'itunes-category1' => '',
		'itunes-category2' => '',
		'itunes-category3' => '',
		'itunes-explicit' => 'no',
		'itunes-email' => '',
		'itunes-image-url' => '',

		/*
			Series state settings.
		*/
		'series-retired' => false,
		'series-hiatus' => false,

		/*
			What album art should be attached to an episode upon being saved?
		*/
		'series-default-album-art' => '',

		/*
			What are the usual hosts of this episode?
			These hosts are expected to be IDs of People, i.e. Nexus_Person objects
		*/
		'series-default-hosts' => array()

	);	

	public $settings_key;

	public function __construct($series_id) {

		$settings_key = sprintf('nexus-core-series-%1$s-settings', $series_id);

		$this->settings_key = $settings_key;

    	add_option(
    		$this->settings_key,
    		$this->default_settings
    	);

    	$values = get_option($this->settings_key, $this->default_settings);

		$this->settings = $values;
	}

}