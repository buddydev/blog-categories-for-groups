<?php
//we use options buddy
require_once dirname( __FILE__ ) . '/options-buddy/ob-loader.php';


class BCGroups_Admin {
 
    private $page;
    
    public function __construct() {
		
        //create a options page
        //make sure to read the code below
        $this->page = new OptionsBuddy_Settings_Page( 'bcg-settings' );
        $this->page->set_bp_mode();//make it to use bp_get_option/bp_update_option

        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu') );
        add_action( 'admin_footer', array( $this, 'admin_css' ) );
    }

    public function admin_init() {

        //set the settings
        
        $page = $this->page;
        //add_section
        //you can pass section_id, section_title, section_description, the section id must be unique for this page, section descriptiopn is optional
        $page->add_section( 'basic_section', __( 'Settings', 'blog-categories-for-groups' ), __('Settings for Blog Category for Groups.', 'blog-categories-for-groups' ) );

		$post_types = get_post_types( array( 'public'=> true ) );//public post types
		
		$post_type_options = array();
		
		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			$post_type_options[$post_type] = $post_type_object->labels->name;
		}
		
		/*$post_statuses = array(
			'publish'	=> __( 'Published', 'blog-categories-for-groups' ),
			'draft'		=> __( 'Draft', 'blog-categories-for-groups' )
		);*/
		
		$comment_statuses = array(
			'open'	=> __( 'Open', 'blog-categories-for-groups' ),
			'close'	=> __( 'Closed', 'blog-categories-for-groups' )
		);
		
		$default_post_type = bcg_get_post_type();

		$taxonomies = get_object_taxonomies( $default_post_type );
	    
		if ( isset( $taxonomies['post_format'] ) ) {
			unset( $taxonomies['post_format'] );
		}
		$tax= array();
		
		foreach ( $taxonomies  as $taxonomy ) {
			$tax_object = get_taxonomy( $taxonomy );
			$tax[$taxonomy] = $tax_object->labels->name;
		}
		
        //add fields
        $page->get_section('basic_section')->add_fields(array( //remember, we registered basic section earlier
                array(
                    'name'		=> 'post_type',
                    'label'		=> __( 'Group Blog Post Type', 'blog-categories-for-groups' ),//you already know it from previous example
                    'desc'		=> __( 'Set the post type for group blog.', 'blog-categories-for-groups' ),// this is used as the description of the field
                    'type'		=> 'select',
                    'default'	=> $default_post_type,
                    'options'	=> $post_type_options
                ),

                array(
                    'name'		=> 'allow_upload',
                    'label'		=> __( 'Allow Upload?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'Want to allow user to upload?', 'blog-categories-for-groups' ),
                    'type'		=> 'select',
                    'default'	=> 1,
                    'options'	=> array(
						1 => __( 'Yes', 'blog-categories-for-groups' ),
						0 => __( 'No', 'blog-categories-for-groups' ),
						
					),
                    
                ),
                array(
                    'name'		=> 'comment_status',
                    'label'		=> __( 'Comment status?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'Do you want to allow commenting on user posts?', 'blog-categories-for-groups' ),
                    'type'		=> 'select',
                    'default'	=> 'open',
                    'options'	=> $comment_statuses,
                    
                ),
                array(
                    'name'		=> 'show_comment_option',
                    'label'		=> __( 'Allow post author to enable/disable comment?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'If you enable, A user will be able to change the comment status for his/her post.', 'blog-categories-for-groups' ),
                    'type'		=> 'radio',
                    'default'	=> 1,
                    'options'	=> array(
							1 => __( 'Yes', 'blog-categories-for-groups' ),
							0 => __( 'No', 'blog-categories-for-groups' ),
                    ),
                    
                ),
                array(
                    'name'		=> 'post_update_redirect',
                    'label'		=> __( 'Where to redirect after creating/updating post?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'If you select archive, user will be redirected to the post list, if single, user will be redirected to single post page if the post is published.', 'blog-categories-for-groups' ),
                    'type'		=> 'select',
                    'default'	=> 'archive',
                    'options'	=> array(
							'archive'	=> __( 'Archive page', 'blog-categories-for-groups' ),
							'single'	=> __( 'Single post page', 'blog-categories-for-groups' ),
                    ),
                    
                ),
                array(
                    'name'		=> 'enable_taxonomy',
                    'label'		=> __( 'Enable Taxonomy?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'If you enable, users will be able to select terms from the selected taxonomies.', 'blog-categories-for-groups' ),
                    'type'		=> 'radio',
                    'default'	=> 1,
                    'options'	=> array(
							1 => __( 'Yes', 'blog-categories-for-groups' ),
							0 => __( 'No', 'blog-categories-for-groups' ),
                    ),
                    
                ),
                array(
                    'name'		=> 'allowed_taxonomies',
                    'label'		=> __( 'Select allowed taxonomies', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'Please check the taxonomies you want users to be able to attach to their post.', 'blog-categories-for-groups' ),
                    'type'		=> 'multicheck',
                    'default'	=> 'category',
                    'options'	=> $tax,
                    
                ),
                array(
                    'name'		=> 'limit_no_of_posts',
                    'label'		=> __( 'Limit number of posts a user can create?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'If you enable it, You can control the allowed number of posts from the next option.', 'blog-categories-for-groups' ),
                    'type'		=> 'radio',
                    'default'	=> 0,
                    'options'	=> array(
							1 => __( 'Yes', 'blog-categories-for-groups' ),
							0 => __( 'No', 'blog-categories-for-groups' ),
                    ),
                    
                ),
                array(
                    'name'		=> 'max_allowed_posts',
                    'label'		=> __( 'How many posts a user can create?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'Only applies if you have enabled the limit on posts from above option.', 'blog-categories-for-groups' ),
                    'type'		=> 'text',
                    'default'	=> 10,
                                        
                ),
                array(
                    'name'		=> 'publish_cap',
                    'label'		=> __( 'Which capability is required for publishing?', 'blog-categories-for-groups' ),
                    'type'		=> 'radio',
                    'default'	=> 'members',//Group's Members
                    'options'	=> array(
	                    'members'   => __( 'Group Members', 'blog-categories-for-groups' ),
	                    'admin'     => __( 'Group Admin', 'blog-categories-for-groups' ),
                    ),
                                        
                ),
                array(
                    'name'		=> 'allow_unpublishing',
                    'label'		=> __( 'Allow users to unpublish their own post?', 'blog-categories-for-groups' ),
                    'desc'		=> '',
                    'type'		=> 'radio',
                    'default'	=> 0,
                    'options'	=> array(
							1 => __( 'Yes', 'blog-categories-for-groups' ),
							0 => __( 'No', 'blog-categories-for-groups' ),
                    ),
                                        
                ),
                array(
                    'name'		=> 'post_cap',
                    'label'		=> __( 'Who can create post?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'Option to check what type of User can create post in Group default is group member.', 'blog-categories-for-groups' ),
                    'type'		=> 'radio',
                    'default'	=> 'members',//Group's Members
                    'options'	=> array(
	                    'members'   => __( 'Group Members', 'blog-categories-for-groups' ),
	                    'admin'     => __( 'Group Admin', 'blog-categories-for-groups' ),
                    ),
                ),
                array(
                    'name'		=> 'allow_edit',
                    'label'		=> __( 'Allow user to edit their post?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'if you disable it, user will not be able to edit their own post.', 'blog-categories-for-groups' ),
                    'type'		=> 'radio',
                    'default'	=> 1,
                    'options'	=> array(
							1 => __( 'Yes', 'blog-categories-for-groups' ),
							0 => __( 'No', 'blog-categories-for-groups' ),
                    ),
                ),    
            
                array(
                    'name'		=> 'dashboard_edit_cap',
                    'label'		=> __( 'Which capability can edit post in backend(WordPress Dashboard)?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'User with these capabilities will nto be redirected to front end editor for editing post., user will not be able to edit their own post.', 'blog-categories-for-groups' ),
                    'type'		=> 'text',
                    'default'	=> 'publish_posts',
                ),    
            
                array(
                    'name'		=> 'allow_delete',
                    'label'		=> __( 'Allow user to delete their post?', 'blog-categories-for-groups' ),
                    'desc'		=> __( 'if you disable it, user will not be able to delete their own post.', 'blog-categories-for-groups' ),
                    'type'		=> 'radio',
                    'default'	=> 1,
                    'options'	=> array(
							1 => __( 'Yes', 'blog-categories-for-groups' ),
							0 => __( 'No', 'blog-categories-for-groups' ),
                    ),
                ),    
            
				
               
            ));
        
      
       
        $page->init();
        
    }

    public function admin_menu() {
        add_options_page( __( 'Blog Category Settings', 'blog-categories-for-groups' ), __( 'Blog Category Settings', 'blog-categories-for-groups' ), 'manage_options', 'blog-categories-for-groups', array( $this->page, 'render' ) );
    }

    

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    
    public function admin_css() {
        
        if ( ! isset( $_GET['page'] ) || $_GET['page'] != 'blog-categories-for-groups' ) {
            return;
		}
        
        ?>

<style type="text/css">
    .wrap .form-table{
        margin:10px;
    }
    
</style>

   <?php     
        
    } 


}

new BCGroups_Admin();