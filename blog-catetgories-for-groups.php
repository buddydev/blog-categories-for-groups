<?php

/*
 * Plugin Name: Blog Categories for Groups
 * Author: Brajesh Singh
 * Plugin URI: http://buddydev.com/plugins/blog-categories-for-groups/
 * Author URI: http://buddydev.com/members/sbrajesh/
 * Description: Allow Group admins;/mods to associate blog categories with groups
 * Version: 1.2.1
 * License: GPL
 * Last Updated: October 30, 2015
 * Tested with WordPress 4.3 + BuddyPress 2.3.4
 * 
 */

if( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

//Component slug used in url, can be overridden in bp-custom.php
if ( ! defined( 'BCG_SLUG' ) ) {
	define( 'BCG_SLUG', 'blog' );
}

define( 'BCG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * The blog categories for Groups helper class
 * Loads the files and localizations
 */
class BCGroups_Helper {

	private static $instance = null;

	private $path;
	private $url;
	
	
	private function __construct() {
		
		$this->path = plugin_dir_path( __FILE__ );
		$this->url = plugin_dir_url( __FILE__ );
		
		$this->setup_hooks();
		
	}
	/**
	 * Singleton factory method
	 * 
	 * @return type
	 */
	public static function get_instance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Setup basic hooks
	 */
	private function setup_hooks() {
		
		add_action( 'bp_include', array( $this, 'load_extension' ) );

		//load javascript for comment reply
		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_script' ) );

		//load localization files
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 2 );
	}

	/**
	 * Load required files
	 */
	public function load_extension() {
		
		$files = array(
			'core/bcg-functions.php',
			'core/template-tags.php',
			'core/bcg-hooks.php',
			'core/bcg-permissions.php',
			'core/bcg-actions.php',
			'core/bcg-screens.php',
			'core/bcg-template.php',
			'core/bcg-admin.php',
		);

		foreach ( $files as $file ) {
			require_once $this->path . $file;
		}
	}

	/**
	 * Load localization files
	 * e.g /languages/blog-categories-for-groups-en_US.mo
	 */
	public function load_textdomain() {
		
		load_plugin_textdomain( 'blog-categories-for-groups', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Enqueue comment js on single post screen
	 */
	public function enqueue_script () {
		
		if ( bcg_is_single_post() ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

}

//initialize
BCGroups_Helper::get_instance();
