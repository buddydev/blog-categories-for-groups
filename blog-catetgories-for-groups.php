<?php

/*
 * Plugin Name: Blog Categories for Groups
 * Author: BuddyDev
 * Plugin URI: http://buddydev.com/plugins/blog-categories-for-groups/
 * Author URI: http://buddydev.com/
 * Description: Allow group members blog with BuddyPress
 * Version: 1.2.4
 * License: GPL
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
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

		register_activation_hook( __FILE__, array( $this, 'install' ) );

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
			'core/filters.php',
		);

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$files[] =  'admin/admin.php';
		}

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

	public function install() {

		$default = array(
			'post_type'				=> 'post',
			'post_status'			=> 'publish',
			'comment_status'		=> 'open',
			'show_comment_option'	=> 1,
			'custom_field_title'	=> '',
			'enable_taxonomy'		=> 1,
			'allowed_taxonomies'	=> 1,
			'enable_category'		=> 1,
			'enable_tags'			=> 1,
			'show_posts_on_profile' => false,
			'limit_no_of_posts'		=> false,
			'max_allowed_posts'		=> 20,
			'publish_cap'			=> 'read',
			'allow_unpublishing'	=> 1,
			'post_cap'				=> 'read',
			'allow_edit'			=> 1,
			'allow_delete'			=> 1,
			//'enabled_tags'		=> 1,
			'taxonomies'		    => array( 'category' ),
			'allow_upload'		    => false,
			'max_upload_count'	    => 2
		);

		if ( ! get_option( 'bcg-settings' ) ) {
			add_option( 'bcg-settings', $default  );
		}

	}

	
}

//initialize
BCGroups_Helper::get_instance();
