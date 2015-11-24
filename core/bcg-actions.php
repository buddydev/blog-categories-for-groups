<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Handles various BCG Actions
 * 
 */
class BCG_Actions {
    /**
	 *
	 * @var BCG_Actions 
	 */
    private static $instance;
    
    private function __construct() {
        
        /**
         * Register form for new/edit resume
         */
        if( is_admin() ) {
            return;
		}
        
        add_action( 'bp_init',    array( $this, 'register_form' ), 7 );
        add_action( 'bp_actions', array( $this, 'publish' ) );
        add_action( 'bp_actions', array( $this, 'unpublish' ) );
        add_action( 'bp_actions', array( $this, 'delete' ) );
        
    }
	/**
	 * Get Singleton Instance 
	 * @return BCG_Actions
	 */
    public static function get_instance() {
        
        if( ! isset ( self::$instance ) ) {
            self::$instance = new self();
		}
		
        return self::$instance;
    }
    
    /**
     * Create Posts screen
     */
    public function create() {
        //No Need to implement it, BP Simple FEEditor takes care of this
    }
    /**
     * Edit Posts screen
     */
    public function edit() {
        //No Need to implement it, BP Simple FEEditor takes care of this
    }
    /**
     * delete Post screen
     */
    public function delete() {
        
		if ( ! bcg_is_component() || ! bp_is_action_variable( 'delete' )  ) {
		 return;
		}
		
          
        $post_id = bp_action_variable( 1 );
		
        if( ! $post_id ) {
            return;
		}
        
        if( bcg_user_can_delete( $post_id,  get_current_user_id() ) ) {

            wp_delete_post( $post_id, true );
            bp_core_add_message ( __( 'Post deleted successfully' ), 'blog-categories-for-groups' );
            //redirect
            wp_redirect( bcg_get_home_url() );//hardcoding bad
            exit( 0 );  
			
        } else {
         
            bp_core_add_message ( __( 'You should not perform unauthorized actions', 'blog-categories-for-groups' ),'error');
        }
        
    }
    /**
     * Publish Post
     */
    public function publish() {
		
       if( !  bcg_is_component() || ! bp_is_action_variable( 'publish', 0 ) ) {
           return;
	   }
           
        $id = bp_action_variable(1);
		
        if( ! $id ) {
            return;
		}
       
        if( bcg_user_can_publish( get_current_user_id(), $id ) ) {
			
            wp_publish_post( $id );//change status to publish         
            bp_core_add_message( __( 'Post Published', 'blog-categories-for-groups' ) );   
        }
		
        bp_core_redirect( bcg_get_home_url() );
    }
    /**
     * Unpublish a post
     */
    public function unpublish() {
		
         if( ! bcg_is_component() || ! bp_is_action_variable( 'unpublish', 0 ) ) {
           return;
		 }
		 
        $id = bp_action_variable( 1 );
        
		if( ! $id ) {
            return;
		}
         
		if( bcg_user_can_unpublish( get_current_user_id(), $id ) ) {
               
			$post = get_post( $id, ARRAY_A );
			$post['post_status'] = 'draft';
			wp_update_post( $post );
			//unpublish
			bp_core_add_message( __('Post unpublished','blog-categories-for-groups') );
                
        }
         
        bp_core_redirect( bcg_get_home_url() );
     
    }
    
	/**
	 * This gets called when a post is saved/updated in the database
	 * after create/edit action handled by BP simple front end post plugin
	 *  
	 * @param int $post_id
	 * @param boolean $is_new
	 * @param type $form_object
	 */
    public function on_save( $post_id, $is_new , $form_object  ) {
		
		$post_redirect = 'single';
		
		$url = '';
		if ( $post_redirect == 'archive' ) {
			
			$url = bcg_get_home_url();
			
		} elseif( $post_redirect == 'single' && get_post_status( $post_id ) == 'publish' ) {
			//go to single post
			$url = get_permalink( $post_id );
		}
		
		if( $url ){
			bp_core_redirect( $url );
		}
	}

	/**
	 * register post form for Posting/editing
	 * @return type 
	 */

	public function register_form() {
		
		//make sure the Front end simple post plugin is active
		if( ! function_exists( 'bp_new_simple_blog_post_form' ) )
			return;

		$post_status = 'draft';
		$user_id = get_current_user_id();
		$group_id = bp_get_current_group_id();
		

		$settings = array(
			'post_type'					=> bcg_get_post_type(),
			'post_author'				=> $user_id,
			'post_status'				=> $post_status,
			'comment_status'			=> 'open',
			'show_comment_option'		=> false,
			'custom_field_title'		=> '',//we are only using it for hidden field, so no need to show it
			'custom_fields'				=> array(
				'_is_bcg_post'			=> array(
					'type'		=> 'hidden',
					'label'		=> '',
					'default'	=> 1
				)
			),      
			'tax'						=> array(
				bcg_get_taxonomy() => array(
					'include' => bcg_get_categories( $group_id ), //selected cats,
				)
			),
			'upload_count'			=> 3,
			'has_post_thumbnail'	=> 1,
			'current_user_can_post' => bcg_current_user_can_post(),
			'update_callback'		=> array( $this, 'on_save' ),
		);
		
		
	   //use it to add extra fields or filter the post type etc

		$settings = apply_filters( 'bcg_form_args', $settings );

		$form = bp_new_simple_blog_post_form( 'bcg_form',  $settings );

	}
}    
//instantiate
BCG_Actions::get_instance();
