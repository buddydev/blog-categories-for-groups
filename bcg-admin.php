<?php
//handle everything except the front end display

class BCG_Group_Extension extends BP_Group_Extension {
var $visibility = 'public'; // 'public' will show your extension to non-group members, 'private' means you have to be a member of the group to view your extension.

var $enable_create_step = true; // enable create step
var $enable_nav_item = false; //do not show in front end
var $enable_edit_item = true; // If your extensi
	function bcg_group_extension() {
		$this->name = __('Blog Categories','bcg');
		$this->slug = 'blog-categories';

		$this->create_step_position = 21;
		$this->nav_item_position = 31;
	}
//on group crate step
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
			if ( !bcg_update_categories($group_id, $cats) ) {
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


                $group_id=$bp->groups->current_group->id;
		 $cats=$_POST["blog_cats"];
                         //print_r($cats);
			if ( !bcg_update_categories($group_id, $cats) ) {
				bp_core_add_message( __( 'There was an error updating Group Blog Categories settings, please try again.', 'bcg' ), 'error' );
			} else {
				bp_core_add_message( __( 'Group Blog Categories settings were successfully updated.', 'bcg' ) );
			}

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	function display() {
		/* Use this function to display the actual content of your group extension when the nav item is selected */
	}

	function widget_display() {
	}
}
if(!bcg_is_disabled_for_group())
bp_register_group_extension( 'BCG_Group_Extension' );

?>