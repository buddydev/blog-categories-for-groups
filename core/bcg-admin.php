<?php
if ( ! class_exists('BP_Group_Extension' ) ) {
	return ;//do not load further
}

//handle everything except the front end display

class BCG_Group_Extension extends BP_Group_Extension {

	public $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.
	public $enable_create_step = true; // enable create step
	public $enable_nav_item = false; //do not show in front end
	public $enable_edit_item = true; // If your extensi

	public function __construct () {


		$this->name = __( 'Blog Categories', 'blog-categories-for-groups' );
		$this->slug = 'blog-categories';

		$this->create_step_position = 21;
		$this->nav_item_position = 31;
		
		do_action_ref_array( 'bcg_created_group_extension', array( &$this ) );
	}

//on group crate step
	public function create_screen( $group_id = null ) {

		if ( ! bp_is_group_creation_step( $this->slug ) ) {
			return false;
		}

		bcg_admin_form();

		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

//on group create save
	public function create_screen_save( $group_id = null ) {
		$bp = buddypress();

		check_admin_referer( 'groups_create_save_' . $this->slug );

		$group_id = $bp->groups->new_group_id;
		
		$cats = $_POST["blog_cats"];

		if ( ! bcg_update_categories( $group_id, $cats ) ) {
			
		} else {
			bp_core_add_message( __( 'Group Blog Categories settings were successfully updated.', 'blog-categories-for-groups' ) );
		}
	}

	public function edit_screen( $group_id = null ) {
		
		if ( ! bp_is_group_admin_screen( $this->slug ) ) {
			return false;
		}
		
		?>

		<h2><?php echo esc_attr( $this->name ) ?></h2>
		
		<?php
			bcg_admin_form();
			wp_nonce_field( 'groups_edit_save_' . $this->slug );
		?>
		<p><input type="submit" value="<?php _e( 'Save Changes', 'blog-categories-for-groups' ) ?> &rarr;" id="save" name="save" /></p>
	<?php
	}

	public function edit_screen_save( $group_id = null ) {
		
		$bp = buddypress();
		
		if ( ! isset( $_POST['save'] ) ) {
			return false;
		}

		check_admin_referer( 'groups_edit_save_' . $this->slug );

		$group_id = $bp->groups->current_group->id;
		
		$cats = $_POST['blog_cats'];

		if ( ! bcg_update_categories( $group_id, $cats ) ) {
			bp_core_add_message( __( 'There was an error updating Group Blog Categories settings, please try again.', 'blog-categories-for-groups' ), 'error' );
		} else {
			bp_core_add_message( __( 'Group Blog Categories settings were successfully updated.', 'blog-categories-for-groups' ) );
		}

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	public function display ( $group_id = null ) {
		/* Use this function to display the actual content of your group extension when the nav item is selected */
	}

	public function widget_display ( $group_id = null ) {
		
	}

}

if ( ! bcg_is_disabled_for_group() ) {
	bp_register_group_extension( 'BCG_Group_Extension' );
}