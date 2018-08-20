<?php

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Class BCG_Screen_Helper
 */
class BCG_Screen_Helper {

	/**
	 * Singleton.
	 *
	 * @var BCG_Screen_Helper
	 */
	private static $instance = null;

	/**
	 * BCG_Screen_Helper constructor.
	 */
	private function __construct() {
		// setup nav.
		$this->setup_hooks();
	}

	/**
	 * Get singleton.
	 *
	 * @return BCG_Screen_Helper
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add to nav.
	 */
	private function setup_hooks() {
		add_action( 'groups_setup_nav', array( $this, 'setup_nav' ) );
		// add_action( 'bp_ready', array( $this, 'screen_group_blog_single_post' ), 5 );
	}

	/**
	 * Add to nav.
	 *
	 * @param bool $current_user_access whether current user can access or not.
	 */
	public function setup_nav( $current_user_access ) {

		if ( ! bp_is_group() ) {
			return;
		}

		$group_id = bp_get_current_group_id();

		if ( bcg_is_disabled( $group_id ) ) {
			return;
		}

		$current_group = groups_get_current_group();

		$group_link = bp_get_group_permalink( $current_group );

		bp_core_new_subnav_item( array(
			'name'            => __( 'Blog', 'blog-categories-for-groups' ),
			'slug'            => BCG_SLUG,
			'parent_url'      => $group_link,
			'parent_slug'     => $current_group->slug,
			'screen_function' => array( $this, 'display' ),
			'position'        => 10,
			'user_has_access' => $current_user_access,
			'item_css_id'     => 'blog',
		) );
	}


	/**
	 * Show posts.
	 */
	public function display() {
		// switch based on current view.
		$current_action = bp_action_variable( 0 );

		if ( 'create' === $current_action ) {
			$this->view_create();
		} elseif ( 'edit' === $current_action ) {
			$this->view_edit();
		} elseif ( bcg_is_single_post() ) {
			$this->view_single();
		} else {
			$this->view_blog();
		}
		// just load the plugins template,
        // above functions will attach the content generators.
		bp_core_load_template( 'groups/single/plugins' );
	}


	/**
	 * Posts screen.
	 */
	public function view_blog() {
		add_action( 'bp_template_content', array( $this, 'get_blog_contents' ) );
	}

	/**
	 * Create screen.
	 */
	public function view_create() {
		add_action( 'bp_template_content', array( $this, 'get_new_post_contents' ) );
	}

	/**
	 * Edit post screen.
	 */
	public function view_edit() {
		add_action( 'bp_template_content', array( $this, 'get_edit_post_contents' ) );
	}

	/**
	 * Single post screen.
	 */
	public function view_single() {

		$bp = buddypress();

		if ( function_exists( 'bp_is_group' ) && ! bp_is_group() ) {
			return;
		}

		// do not catch the request for creating new post.
		if ( bp_is_action_variable( 'create', 0 ) ) {
			return;
		}

		$current_group = groups_get_current_group();

		if ( bcg_is_disabled( $current_group->id ) ) {
			return;
		}
		// if the group is private/hidden and user is not member, return.
		if ( ( $current_group->status == 'private' || $current_group->status == 'hidden' ) && ( ! is_user_logged_in() || ! groups_is_user_member( bp_loggedin_user_id(), $current_group->id ) ) ) {
			return; //avoid prioivacy troubles
		}

		if ( bcg_is_component() && ! empty( $bp->action_variables[0] ) ) {
			// should we check for the existence of the post?
			add_action( 'bp_template_content', array( $this, 'get_single_post_contents' ) );
		}
	}

	/**
	 * Load contents for 'posts' screen.
	 */
	public function get_blog_contents() {
		$this->display_options_nav();
		bcg_load_template( 'posts.php' );
	}

	/**
	 * Load contents for single edit post screen.
	 */
	public function get_edit_post_contents() {
		$this->display_options_nav();
		bcg_load_template( 'edit.php' );
	}

	/**
	 * Load content for new post screen.
	 */
	public function get_new_post_contents() {
		$this->display_options_nav();
		bcg_load_template( 'edit.php' );
	}

	/**
	 * Load content for single post view screen.
	 */
	public function get_single_post_contents() {
		$this->display_options_nav();
		bcg_load_template( 'single-post.php' );

	}

	/**
	 * Render options nav.
	 */
	public function display_options_nav() { ?>
        <div id="subnav" class="item-list-tabs no-ajax">
            <ul>
				<?php bcg_get_options_menu(); ?>
            </ul>
        </div>
	<?php }
}

BCG_Screen_Helper::get_instance();

