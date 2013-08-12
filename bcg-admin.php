<?php
//handle everything except the front end display
if ( class_exists( 'BP_Group_Extension' ) ) :
	class BCG_Group_Extension extends BP_Group_Extension {
		// var $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.
		// var $enable_create_step = true; // enable create step
		// var $enable_nav_item = false; //do not show in front end
		// var $enable_edit_item = true; // If your extensi
	    // var $label = groups_get_groupmeta( bp_get_current_group_id(), 'bcg_tab_label' );

	    function __construct() {

		// BuddyPress is < 1.8, we use the old way
		if( version_compare( bp_get_version(), '1.8', '<' ) ) {
			$this->name = __('Blog Categories','bcg');
			$this->slug = esc_html__( bcg_get_slug() ) ;
			$this->nav_item_name = bcg_get_tab_label();
			$this->enable_nav_item = bcg_is_enabled( bp_get_current_group_id() );

			// BuddyPress is > 1.8, we use the new way
		} else {

			$args = array(
			'slug' => esc_html__( bcg_get_slug() ) ,
			'name' => __('Blog Categories','bcg'),
	        'visibility' => 'public',
	        'enable_nav_item'   => bcg_is_enabled( bp_get_current_group_id() ),
			'nav_item_position' => 31,
			'nav_item_name' => bcg_get_tab_label(),
			);        
        	parent::init( $args );
		}
		
	}

	//on group create step
		function create_screen() {
			if ( !bp_is_group_creation_step( $this->slug ) )
				return false;
			bcg_admin_form();
			wp_nonce_field( 'groups_create_save_' . $this->slug );
		}
	//on group create save
		function create_screen_save() {
			global $bp;

			check_admin_referer( 'groups_create_save_' . $this->slug );
	                $group_id=$bp->groups->new_group_id;
			 $cats=$_POST["blog_cats"];
	                         //print_r($cats);
				if ( !bcg_update_categories($group_id, $cats) || !bcg_update_groupmeta($group_id) ) {
					//bp_core_add_message( __( 'There was an error updating group blog category settings, please try again.', 'buddypress' ), 'error' );
				} else {
					bp_core_add_message( __( 'Group Blog Categories settings were successfully updated.', 'bcg' ) );
				}
		}

		function edit_screen() {
			if ( !bp_is_group_admin_screen( $this->slug ) )
				return false; ?>

	        <h2><?php echo esc_attr( $this->name ) ?></h2>
	<?php
	                    bcg_admin_form();


			wp_nonce_field( 'groups_edit_save_' . $this->slug );
	                ?>
	        <p><input type="submit" value="<?php _e( 'Save Changes', 'bcg' ) ?> &rarr;" id="save" name="save" /></p>
	                <?php
		}

		function edit_screen_save() {
			global $bp;

			if ( !isset( $_POST['save'] ) )
				return false;

			check_admin_referer( 'groups_edit_save_' . $this->slug );
			
			$group_id = $bp->groups->current_group->id;
			$cats = $_POST["blog_cats"];
	         //print_r($cats);

			if ( !bcg_update_categories($group_id, $cats) || !bcg_update_groupmeta($group_id) ) {
				bp_core_add_message( __( 'There was an error updating the Group Blog Categories settings, please try again.', 'bcg' ), 'error' );
			} else {
				bp_core_add_message( __( 'Group Blog Categories settings were successfully updated.', 'bcg' ) );
			}

			bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . 'admin/' . bcg_get_slug() );
		}

		function display() {
			// bp_core_load_template( apply_filters( 'groups_template_group_blog', 'bcg/home-new' ) );
			?>
				<div id="subnav" class="item-list-tabs no-ajax">
					<ul>
					<?php bcg_get_options_menu();?>
					</ul>
				</div>
				<?php
				if(bcg_is_single_post())
					bcg_load_template('bcg/single-post.php' );
				else if(bcg_is_post_create())
					bcg_load_template('bcg/create.php' );
				else
					bcg_load_template( 'bcg/blog.php');
				?>
			<?php
			/* Use this function to display the actual content of your group extension when the nav item is selected */
		}

		function widget_display() {
		}
		
	}

bp_register_group_extension( 'BCG_Group_Extension' );

endif; // if ( class_exists( 'BP_Group_Extension' ) )
