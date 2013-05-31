<?php
/*
 * Plugin Name: Blog Categories for Groups
 * Author: Brajesh Singh
 * Plugin URI:http://buddydev.com/plugins/blog-categories-for-groups/
 * Author URI:http://buddydev.com/members/sbrajesh/
 * Description: Allow Group admins;/mods to associate blog categories with groups
 * Version: 1.1
 * Tested with WordPress 3.5.1+BuddyPress 1.7.2
 * License: GPL
 * Date: May 30, 2013
 */

if(!defined('BCG_SLUG'))
    define('BCG_SLUG','blog');

define('BCG_PLUGIN_DIR',  plugin_dir_path(__FILE__));
/**
 * The blog categories for Groups helper class
 * Loads the files and localizations
 */
class BCGroups_Helper{
    
    private static $instance;
    
    private function __construct(){
        
        add_action('bp_include',array($this,'load_extension'));
        //load javascript for comment reply
        add_action('bp_enqueue_scripts',array($this,'enqueue_script'));
        //load localization files
        add_action ( 'bp_init', array($this,'load_textdomain'), 2 );
    }
    
    public static function get_instance(){
        if( ! isset( self::$instance ) )
                self::$instance=new self();
        return self::$instance;
    }
    
    /**
     * Load required files
     */
    public function load_extension(){
        $files=array(
            'bcg-functions.php',
            'template-tags.php',
            'bcg-hooks.php',
            'bcg-admin.php',
            
            
        );
        
        foreach($files as $file)
            include_once (BCG_PLUGIN_DIR.$file);

    }
    /**
     * Load localization files
     * e.g /languages/en_US.mo
     */
    function load_textdomain() {
        $locale = apply_filters( 'bcg_load_textdomain_get_locale', get_locale() );
        // if load .mo file
        if ( !empty( $locale ) ) {
            $mofile_default = sprintf( '%s/languages/%s.mo',BCG_PLUGIN_DIR, $locale );
            $mofile = apply_filters( 'bcg_load_mofile', $mofile_default );
            // make sure file exists, and load it
            if ( file_exists( $mofile ) ) {
                load_textdomain( 'bcg', $mofile );
            }
        }
    }
    /**
     * Enqueue comment js on single post screen
     */
    function enqueue_script(){
        if(bcg_is_single_post ())
            wp_enqueue_script ('comment-reply');

   }
}

//initialize
BCGroups_Helper::get_instance();



class BCG_View_Helper{
 
    private static $instance;
    
    private function __construct() {
        
        //setup nav
        add_action('groups_setup_nav',  array($this,'setup_nav'));
        add_action('bp_ready',          array($this,'screen_group_blog_single_post'),5);
        add_action('bp_init',           array($this,'register_form'));

    }
    
    public static function get_instance(){
        
        if(!isset (self::$instance))
                self::$instance = new self();
        
        return self::$instance;
    }
    
    //setup nav
    function setup_nav($current_user_access){
        global $bp;

        if(!bp_is_group())
            return;
        
        $group_id=bp_get_current_group_id();

        if(bcg_is_disabled($group_id))
            return;

        $current_group=groups_get_current_group();
        
        $group_link = bp_get_group_permalink($current_group);
        
        bp_core_new_subnav_item( array( 
            'name' =>             __( 'Blog', 'bcg' ),
            'slug' =>             BCG_SLUG,
            'parent_url' =>       $group_link,
            'parent_slug' =>      $current_group->slug,
            'screen_function' =>  array($this,'screen_group_blog'),
            'position' =>         10,
            'user_has_access' =>  $current_user_access,
            'item_css_id' =>      'blog'
            ) );

    }
    /**
     * Register the simple front end post plugin
     */
    function register_form(){
       
        $group_id=bp_get_current_group_id();
        //register form if the BPDev PostEditor Exists
        if(function_exists('bp_new_simple_blog_post_form')){
            $form_params=array(
                'post_type'=>'post',
                'post_author'=>  bp_loggedin_user_id(),
                'post_status'=>'draft',
                'current_user_can_post'=>  bcg_current_user_can_post(),
                'tax'=>array(
                    'category'=>array(
                        'include'=>bcg_get_categories($group_id),//selected cats,
                    )
                ),

            'show_tags'=>false,//current version does not support the tag

            'allowed_tags'=>array());
            
            $form=bp_new_simple_blog_post_form('bcg_form',apply_filters('bcg_form_args',$form_params));

        }
        
    }
    //load the blog home page for group
    function screen_group_blog(){
        //load template
        bp_core_load_template( apply_filters( 'groups_template_group_blog', 'bcg/home' ) );
    }
    
    //for single post screen
    function screen_group_blog_single_post(){
       global $bp;
       
       if(function_exists('bp_is_group')&&!bp_is_group())
          return;
       
        //do not catch the request for creating new post
       if(bp_is_action_variable('create',0))
               return;
       
       $current_group=groups_get_current_group();
       
       if(bcg_is_disabled($current_group->id))
               return;
       //if the group is private/hidden and user is not member, return
       if(($current_group->status=='private'||$current_group->status=='hidden')&&(!is_user_logged_in()||!groups_is_user_member(bp_loggedin_user_id(), $current_group->id)))
       return;//avoid prioivacy troubles

       if (bp_is_groups_component() && bp_is_current_action(BCG_SLUG) &&!empty($bp->action_variables[0]) ){

           $wpq=new WP_Query(bcg_get_query());
            if($wpq->have_posts()){
                //load template
             bp_core_load_template( apply_filters( 'groups_template_group_blog_single_post', 'bcg/home' ) );
            }
        else
            bp_core_add_message (__("Sorry, the post does not exists!","bcg"),"error");

       }
    }
    
    
    
}

BCG_View_Helper::get_instance();

