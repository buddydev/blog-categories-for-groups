<?php

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Group_Extension' ) ) {
	return;// do not load further.
}

/**
 * Group extension to manage category association to group.
 */
class BCG_Group_Extension extends BP_Group_Extension {

	// 'public' will show your extension to non-group members,
	// 'private' means you have to be a member of the group to view your extension.
	/**
	 * Extension visibility.
	 *
	 * @var string
	 */
	public $visibility = 'public';

	/**
	 * Show on create group screen.
	 *
	 * @var bool
	 */
	public $enable_create_step = true; // enable create step.

	/**
	 * Show in group menu?
	 *
	 * @var bool
	 */
	public $enable_nav_item = false; // do not show in front end.

	/**
	 * Show on manage screen?
	 *
	 * @var bool
	 */
	public $enable_edit_item = true;

	/**
	 * BCG_Group_Extension constructor.
	 */
	public function __construct() {
		$this->name = __( 'Blog Categories', 'blog-categories-for-groups' );
		$this->slug = 'blog-categories';

		$this->create_step_position = 21;
		$this->nav_item_position    = 31;

		do_action_ref_array( 'bcg_created_group_extension', array( &$this ) );
	}

	/**
	 * Show category selection box on create screen.
	 *
	 * @param int $group_id group id.
	 */
	public function create_screen( $group_id = null ) {

		if ( ! bp_is_group_creation_step( $this->slug ) ) {
			return;
		}

		bcg_admin_form( $group_id );

		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	/**
	 * On group create save
	 *
	 * @param int $group_id group id.
	 */
	public function create_screen_save( $group_id = null ) {
		$bp = buddypress();

		check_admin_referer( 'groups_create_save_' . $this->slug );

		$group_id = $bp->groups->new_group_id;

		$cats = isset( $_POST['blog_cats'] ) ? $_POST['blog_cats'] : array();

		if ( empty( $cats ) ) {
			// redirect to the last step.
			bp_core_add_message( __( 'Please select some terms.', 'blog-categories-for-groups' ), 'error' );
			bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create/step/' . bp_get_groups_current_create_step() ) );
		}

		bcg_update_categories( $group_id, $cats );
	}

	/**
	 * Show category selection screen in group manage.
	 *
	 * @param int $group_id group id.
	 */
	public function edit_screen( $group_id = null ) {

		if ( ! bp_is_group_admin_screen( $this->slug ) ) {
			return;
		}

		?>

        <h2><?php echo esc_attr( $this->name ); ?></h2>

		<?php

		bcg_admin_form( $group_id );
		wp_nonce_field( 'groups_edit_save_' . $this->slug );
		?>
        <p><input type="submit" value="<?php _e( 'Save Changes', 'blog-categories-for-groups' ) ?> &rarr;" id="save" name="save"/></p>
		<?php
	}

	/**
	 * Save Categories association.
	 *
	 * @param int $group_id group id.
	 */
	public function edit_screen_save( $group_id = null ) {

		$bp = buddypress();

		if ( ! isset( $_POST['save'] ) ) {
			return;
		}

		check_admin_referer( 'groups_edit_save_' . $this->slug );

		$group_id = $bp->groups->current_group->id;

		$cats = isset( $_POST['blog_cats'] ) ? $_POST['blog_cats'] : array();

		if ( empty( $cats ) ) {
			// redirect to the last step.
			bp_core_add_message( __( 'Please select some terms.', 'blog-categories-for-groups' ), 'error' );
			bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
		}

		bcg_update_categories( $group_id, $cats );
		bp_core_add_message( __( 'Group Blog Categories settings were successfully updated.', 'blog-categories-for-groups' ) );

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	public function display( $group_id = null ) {
		/* Use this function to display the actual content of your group extension when the nav item is selected */
	}

	public function widget_display( $group_id = null ) {
	}

}

// Register Extension.
if ( ! bcg_is_disabled_for_group() ) {
	bp_register_group_extension( 'BCG_Group_Extension' );
}
