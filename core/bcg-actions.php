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
        if ( is_admin() ) {
            return;
		}
        
        add_action( 'bp_init', array( $this, 'register_form' ), 7 );
        add_action( 'bp_actions', array( $this, 'publish' ) );
        add_action( 'bp_actions', array( $this, 'unpublish' ) );
        add_action( 'bp_actions', array( $this, 'delete' ) );
        add_action( 'bp_after_activity_add_parse_args', array( $this, 'update_activity_args' ) );
		//add_action( 'bp_blogs_format_activity_action_new_blog_post', array( $this, 'format_activity_action' ), 10, 2  );
	   // add_filter( 'bp_get_activity_action', array( $this, 'update_activity_action' ) );

    }

	/**
	 * Get Singleton Instance 
	 * @return BCG_Actions
	 */
    public static function get_instance() {
        
        if ( ! isset ( self::$instance ) ) {
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

    }
    /**
     * delete Post screen
     */
    public function delete() {
        
		if ( ! bcg_is_component() || ! bp_is_action_variable( 'delete' )  ) {
			return;
		}

        $post_id = bp_action_variable( 1 );
		
        if ( ! $post_id ) {
            return;
		}

	    $group_id = bp_get_current_group_id();

        if ( bcg_user_can_delete( $post_id,  get_current_user_id(), $group_id ) ) {

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
		
       if ( !  bcg_is_component() || ! bp_is_action_variable( 'publish', 0 ) ) {
           return;
	   }
           
        $id = bp_action_variable(1);
		
        if ( ! $id ) {
            return;
		}
       
        if ( bcg_user_can_publish( get_current_user_id(), $id ) ) {
			
            wp_publish_post( $id );//change status to publish         
            bp_core_add_message( __( 'Post Published', 'blog-categories-for-groups' ) );   
        }
		
        bp_core_redirect( bcg_get_home_url() );
    }
    /**
     * Unpublish a post
     */
    public function unpublish() {
		
         if ( ! bcg_is_component() || ! bp_is_action_variable( 'unpublish', 0 ) ) {
           return;
		 }
		 
        $id = bp_action_variable( 1 );
        
		if ( ! $id ) {
            return;
		}
         
		if ( bcg_user_can_publish( get_current_user_id(), $id ) ) {
               
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

	    $post_redirect = bcg_get_option( 'post_update_redirect' );
		
		$url = '';

		if ( $post_redirect == 'archive' ) {
			
			$url = bcg_get_home_url();
			
		} elseif ( $post_redirect == 'single' && get_post_status( $post_id ) == 'publish' ) {
			//go to single post
			$url = get_permalink( $post_id );
		}
		
		if ( $url ){
			bp_core_redirect( $url );
		}
	}

	/**
	 * register post form for Posting/editing
	 * @return type 
	 */

	public function register_form() {
		
		//make sure the Front end simple post plugin is active
		if ( ! function_exists( 'bp_new_simple_blog_post_form' ) )
			return;

		$user_id        = get_current_user_id();
		$post_status    = bcg_get_group_post_status( $user_id );
		$group_id       = bp_get_current_group_id();

		$settings = array(
			'post_type'					=> bcg_get_post_type(),
			'post_author'				=> $user_id,
			'post_status'				=> $post_status,
			'comment_status'		    => bcg_get_option( 'comment_status' ),
			'show_comment_option'		=> bcg_get_option( 'show_comment_option' ),
			'custom_field_title'		=> '',//we are only using it for hidden field, so no need to show it
			'custom_fields'				=> array(
				'_is_bcg_post'  => array( 'type' => 'hidden', 'label' => '', 'default' => 1 ),
				'_bcg_group_id' => array( 'type' => 'hidden', 'label' => '', 'default' => $group_id )
			),
			'allow_upload'			    => bcg_get_option( 'allow_upload' ),
			'upload_count'			    => 0,
			'has_post_thumbnail'	    => 1,
			'current_user_can_post'     => bcg_current_user_can_post(),
			'update_callback'		    => array( $this, 'on_save' ),
		);

		if ( bcg_get_option( 'enable_taxonomy' ) ) {

			$taxonomies = array();
			$tax = bcg_get_option( 'allowed_taxonomies' );

			if ( ! empty( $tax ) ) {

				foreach ( (array) $tax as $tax_name ) {
					$view = 'checkbox';
					//is_taxonomy_hierarchical($tax_name);

					$taxonomies[$tax_name] = array(
						'taxonomy'		=> $tax_name,
						'view_type'		=> 'checkbox',//currently only checkbox
					);

				}
			}

			if ( ! empty( $taxonomies ) ) {
				$settings['tax'] = $taxonomies;
			}

		}
	   //use it to add extra fields or filter the post type etc

		$settings = apply_filters( 'bcg_form_args', $settings );

		bp_new_simple_blog_post_form( 'bcg_form',  $settings );

	}

	public function update_activity_args( $args ) {

		if( ! $_REQUEST['custom_fields']['_is_bcg_post'] && ( $args['component'] !== 'blogs' || $args['type'] !=='new_blog_post' ) ) {
			return $args;
		}

		$args['component']          = 'groups';
		$args['item_id']            = absint( $_REQUEST['custom_fields']['_bcg_group_id'] );

		return $args;
	}

	public function get_edit_post_data() {

		ob_start();

		bcg_load_template( 'edit.php' );

		$content = ob_get_clean();

		echo $content;
	}

}
//instantiate
BCG_Actions::get_instance();

add_filter( 'bp_activity_get_activity_id', 'bcg_update_group_post_activity', 0, 2);
function bcg_update_group_post_activity( $id, $args ) {


	if ( $args['component'] == 'blogs' && $args['type'] == 'new_blog_post' ) {

		unset( $args['item_id']);
		//now set component to groups
		$args['component'] = buddypress()->groups->id;

		$new_id = bp_activity_get_activity_id( $args );

		if( $new_id ) {
			$id = $new_id;
		}


	}

	return $id;
}

add_action( 'bp_activity_post_type_unpublished', 'bcg_delete_group_post_activity', 0 , 3 );
function bcg_delete_group_post_activity( $delete_activity_args, $post, $deleted ) {

	if( $delete_activity_args['component'] == 'blogs' && $delete_activity_args['type'] == "new_blog_post") {

		unset( $delete_activity_args['item_id'] );
		$delete_activity_args['component'] = buddypress()->groups->id;
		$deleted = bp_activity_delete( $delete_activity_args );

	}
	return $deleted;

}

function bcg_format_activity_action( $action, $activity  ) {

	$user_link = bp_core_get_userlink( $activity->user_id );

	//$user_name = bp_core_get_user_displayname( $activity->user_id );

	if ( isset( $activity->post_url ) ) {
		$post_url = $activity->post_url;
	}

	$post_title = bp_activity_get_meta( $activity->id, 'post_title' );

	if ( empty( $post_title ) ) {
		// Defaults to no title.
		$post_title = esc_html__( '(no title)', 'blog-categories-for-groups' );

	}else {
		$post_title = esc_html__( $post_title, 'blog-categories-for-groups' );
	}

	$group = groups_get_group( array( 'group_id' => $activity->item_id ) );
	$group_permalink = bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug;

	$post_link  = '<a href="' . esc_url( $post_url ) . '">' . $post_title . '</a>';
	$group_link =  '<a href="' . esc_url( $group_permalink ) . '">' . esc_html( $group->name ) . '</a>';

	$action = sprintf( __( '%1$s wrote a new post in %2$s, %3$s', 'blog-categories-for-groups' ), $user_link, $group_link, $post_link );

	return $action;
}

add_action( 'groups_register_activity_actions', 'bcg_register_group_activity_action' );
function bcg_register_group_activity_action() {

	$bp = buddypress();
	bp_activity_set_action(
		$bp->groups->id,
		'new_blog_post',
		__( 'Group details edited', 'buddypress' ),
		'bcg_format_activity_action',
		__( 'Group Updates', 'buddypress' ),
		array( 'activity', 'group', 'member', 'member_groups' )
	);

}