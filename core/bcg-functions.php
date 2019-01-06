<?php
// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Get an option value by name
 *
 * @param string $option_name option name.
 *
 * @return mixed
 */
function bcg_get_option( $option_name ) {

	$settings = bcg_get_options();

	if ( isset( $settings[ $option_name ] ) ) {
		return $settings[ $option_name ];
	}

	return '';
}

/**
 * Get all options for the BCG
 *
 * @return array
 */
function bcg_get_options() {
	$default = array(

		'post_type'              => 'post',
		'post_status'            => 'publish',
		'comment_status'         => 'open',
		'show_comment_option'    => 1,
		'custom_field_title'     => '',
		'enable_taxonomy'        => 1,
		'allowed_taxonomies'     => 1,
		'enable_category'        => 1,
		'enable_tags'            => 1,
		'show_posts_on_profile'  => 0,
		'limit_no_of_posts'      => 0,
		'max_allowed_posts'      => 20,
		'publish_cap'            => 'read',
		'allow_unpublishing'     => 1, // subscriber //see https://codex.wordpress.org/Roles_and_Capabilities.
		'post_cap'               => 'read',
		'allow_edit'             => 1,
		'allow_delete'           => 1,
		'allow_upload'           => 0,
		//'enabled_tags'			=> 1,
		'taxonomies'             => array( 'category' ),
		'max_upload_count'       => 2,
		'post_update_redirect'   => 'archive',
		'allow_group_tab_toggle' => 1, // allow group admin to toggle tab.
		'group_based_permalink'  => 1, // Group based permalink or normal permalink.
	);

	return bp_get_option( 'bcg-settings', $default );
}

/**
 * Are we dealing with blog categories pages?
 *
 * @return boolean
 */
function bcg_is_component() {

	$bp = buddypress();

	if ( bp_is_current_component( $bp->groups->slug ) && bp_is_current_action( BCG_SLUG ) ) {
		return true;
	}

	return false;
}

/**
 * Are we looking at the blog categories landing page
 *
 * @return boolean
 */
function bcg_is_home() {

	$bp = buddypress();

	if ( bcg_is_component() && empty( $bp->action_variables[0] ) ) {
		return true;
	}

	return false;
}

/**
 * Is it single post?
 *
 * @return boolean
 */
function bcg_is_single_post() {
	$bp = buddypress();

	if ( bcg_is_component() && ! empty( $bp->action_variables[0] )
	     && ! in_array( $bp->action_variables[0], array_merge( array( 'create', 'page', 'edit' ), bcg_get_taxonomies() ) ) ) {
		return true;
	}

	return false;
}

/**
 * Is it post create screen
 *
 * @return boolean
 */
function bcg_is_post_create() {
	$bp = buddypress();

	if ( bcg_is_component() && ! empty( $bp->action_variables[0] ) && $bp->action_variables[0] == 'create' ) {
		return true;
	}

	return false;
}

/**
 * Check if we are on the single term screen
 *
 * @return boolean
 */
function bcg_is_term() {
	$bp = buddypress();

	if ( bcg_is_component() && ! empty( $bp->action_variables[1] ) && in_array( $bp->action_variables[0], bcg_get_taxonomies() ) ) {
		return true;
	}

	return false;
}

/**
 * Is it single category view
 *
 * For back compatibility we are keeping this function
 *
 * @return boolean
 */
function bcg_is_category() {
	return bcg_is_term();
}

/**
 * Check if blogging is disabled for the current group
 *
 * @return bool
 */
function bcg_is_disabled_for_group() {

	$group_id = false;

	if ( bp_is_group_create() ) {
		$group_id = $_COOKIE['bp_new_group_id'];
	} elseif ( bp_is_group() ) {
		$group_id = bp_get_current_group_id();
	}

	return apply_filters( 'bcg_is_disabled_for_group', bcg_is_disabled( $group_id ) );
}

/**
 * Check if Blogging is disabled for the given group
 *
 * @param int $group_id group id.
 *
 * @return bool
 */
function bcg_is_disabled( $group_id ) {

	if ( empty( $group_id ) ) {
		return false;
	}

	$is_disabled = groups_get_groupmeta( $group_id, 'bcg_is_active' );

	return apply_filters( 'bcg_is_disabled', intval( $is_disabled ), $group_id );
}

/**
 * Get associated post type
 *
 * @return string
 */
function bcg_get_post_type() {

	$post_type = ( bcg_get_option( 'post_type' ) ) ? bcg_get_option( 'post_type' ) : 'post';

	return apply_filters( 'bcg_get_post_type', $post_type );
}

/**
 * Get all allowed taxonomies names as array
 *
 * @return mixed|void
 */
function bcg_get_taxonomies() {

	$taxonomy = ( bcg_get_option( 'allowed_taxonomies' ) ) ? bcg_get_option( 'allowed_taxonomies' ) : 'category';

	return apply_filters( 'bcg_get_taxonomies', (array) $taxonomy );
}

/**
 * Get all terms.
 *
 * @return array|int|WP_Error
 */
function bcg_get_all_terms() {

	$taxonomy = bcg_get_taxonomies();
	$cats = get_terms( $taxonomy, array( 'fields' => 'all', 'get' => 'all' ) );

	return $cats;
}

/**
 * Placeholder.
 *
 * @param int $group_id group id.
 */
function bcg_get_associated_terms( $group_id ) {
}

/**
 * Placeholder.
 *
 * @param int    $group_id group id.
 * @param string $taxonomy tax.
 */
function bcg_get_terms( $group_id, $taxonomy = 'category' ) {
}

/**
 * Get categories associated with teh group.
 *
 * @param int $group_id group id.
 *
 * @return array
 */
function bcg_get_categories( $group_id ) {

	$cats = groups_get_groupmeta( $group_id, 'group_blog_cats' );

	return maybe_unserialize( $cats );
}

/**
 * Update associated categories.
 *
 * @param int   $group_id group id.
 * @param array $cats categories id.
 *
 * @return bool|int
 */
function bcg_update_categories( $group_id, $cats ) {

	$cats = maybe_serialize( $cats );

	return groups_update_groupmeta( $group_id, 'group_blog_cats', $cats );
}

/**
 * Get a post by slug name
 *
 * @param string $slug slug.
 *
 * @return WP_Post
 */
function bcg_get_post_by_slug( $slug ) {
	global $wpdb;

	$query = "SELECT * FROM $wpdb->posts WHERE post_name = %s AND post_type = %s LIMIT 1";
	$post  = $wpdb->get_row( $wpdb->prepare( $query, $slug, bcg_get_post_type() ) );

	return $post;
}

/**
 * Get allowed post status.
 *
 * @param int $user_id user id.
 *
 * @return string
 */
function bcg_get_group_post_status( $user_id ) {

	$authority_to_publish = bcg_get_option( 'publish_cap' );
	$group_id             = bp_get_current_group_id();
	$post_status          = 'draft';

	if ( bcg_can_user_publish_post( get_current_user_id() ) ) {
		$post_status = 'publish';
	}

	return $post_status;
}

/**
 * Check if  post is published
 *
 * @param int $post_id post id.
 *
 * @return bool
 */
function bcg_is_post_published( $post_id ) {
	return get_post_field( 'post_status', $post_id ) == 'publish';
}

/**
 * Check if user can publish post?
 *
 * @param int $user_id user id.
 *
 * @return bool
 */
function bcg_can_user_publish_post( $user_id ) {

	$can_publish = false;

	if ( ! $user_id ) {
		return $can_publish;
	}

	$publish_cap = bcg_get_option( 'publish_cap' );
	$group_id    = bp_get_current_group_id();

	if ( is_super_admin() ) {
		$can_publish = true;
	} elseif ( $publish_cap == 'admin' && groups_is_user_admin( $user_id, $group_id ) ) {
		$can_publish = true;
	} elseif ( $publish_cap == 'members' && groups_is_user_member( $user_id, $group_id ) ) {
		$can_publish = true;
	}

	return $can_publish;

}

/**
 * Should we limit by number of posts?
 *
 * @return bool
 */
function bcg_limit_no_of_posts() {
	return apply_filters( 'bcg_limit_no_of_posts', bcg_get_option( 'limit_no_of_posts' ) );
}

/**
 * Get allowed number of posts.
 *
 * @param int $user_id user id.
 *
 * @return int
 */
function bcg_get_allowed_no_of_posts( $user_id = null ) {

	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	// filter on this hook to change the no. of posts allowed.
	// by default no. posts allowed.
	return apply_filters( 'bcg_allowed_posts_count', bcg_get_option( 'max_allowed_posts' ), $user_id );
}

/**
 * Get total number of published posts.
 *
 * @param int $user_id user id.
 * //may be a bad implementation here
 *
 * @return int
 */
function bcg_get_total_published_posts( $user_id = 0 ) {

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	// Needs revisit.
	global $wpdb;

	$count = $wpdb->get_var( $wpdb->prepare( "SELECT count('*') FROM {$wpdb->posts} WHERE  post_author=%d AND post_type=%s AND post_status='publish'", $user_id, bcg_get_post_type() ) );

	return intval( $count );
}

/**
 * Get remaining number of posts.
 *
 * @param int $user_id user id.
 *
 * @return int
 */
function bcg_get_remaining_posts( $user_id = null ) {

	$total_allowed = bcg_get_allowed_no_of_posts( $user_id );

	return intval( $total_allowed - bcg_get_total_published_posts( $user_id ) );
}

/**
 * Get BCG landing page url
 *
 * @param int $group_id group id.
 *
 * @return string
 */
function bcg_get_home_url( $group_id = null ) {

	if ( ! empty( $group_id ) ) {
		$group = new BP_Groups_Group( $group_id );
	} else {
		$group = groups_get_current_group();
	}

	return apply_filters( 'bcg_home_url', bp_get_group_permalink( $group ) . BCG_SLUG );
}

/**
 * Get publish/unpublish link.
 *
 * @param int    $post_id post id.
 * @param string $label_ac Publish label.
 * @param string $label_de unpublish label.
 *
 * @return string
 */
function bcg_get_post_publish_unpublish_link( $post_id = 0, $label_ac = 'Publish', $label_de = 'Unpublish' ) {

	if ( ! $post_id ) {
		return '';
	}

	if ( ! bcg_user_can_publish( get_current_user_id() ) ) {
		return '';
	}

	$post    = get_post( $post_id );
	$user_id = get_current_user_id();
	$url     = '';

	if ( ! ( is_super_admin() || $post->post_author == $user_id || groups_is_user_admin( $user_id, bp_get_current_group_id() ) ) ) {
		return '';
	}

	// check if post is published.
	$url = bcg_get_post_publish_unpublish_url( $post_id );

	if ( ! bcg_get_option( 'allow_unpublishing' ) ) {
		return '';
	}

	if ( bcg_is_post_published( $post_id ) ) {
		$link = "<a href='{$url}'>{$label_de}</a>";
	} else {
		$link = "<a href='{$url}'>{$label_ac}</a>";
	}

	return $link;

}

/**
 * Get publish/unpublish url.
 *
 * @param int $post_id post id.
 *
 * @return string
 */
function bcg_get_post_publish_unpublish_url( $post_id = 0 ) {

	if ( ! $post_id ) {
		return '';
	}

	$post = get_post( $post_id );
	$url  = '';

	if ( bcg_user_can_publish( get_current_user_id(), $post_id ) ) {
		// check if post is published.
		$url = bcg_get_home_url();

		if ( bcg_is_post_published( $post_id ) ) {
			$url = $url . '/unpublish/' . $post_id . '/';
		} else {
			$url = $url . '/publish/' . $post_id . '/';
		}
	}

	return $url;
}

/**
 * Get Edit post url.
 *
 * @param int $post_id post id.
 *
 * @return string
 */
function bcg_get_edit_url( $post_id = 0 ) {

	$user_id  = get_current_user_id();
	$group_id = bp_get_current_group_id();

	if ( ! $user_id && ! $group_id ) {
		return '';
	}

	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}
	// check if current user can edit the post.
	$post = get_post( $post_id );
	// if the author of the post is same as the loggedin user or the logged in user is admin.
	if ( $post->post_type != bcg_get_post_type() ) {
		return '';
	}

	if ( $post->post_author != $user_id && ! is_super_admin() && ! groups_is_user_admin( $user_id, $group_id ) ) {
		return '';
	}

	$action_name = 'edit';

	if ( current_user_can( bcg_get_option( 'dashboard_edit_cap' ) ) ) {
		return get_edit_post_link( $post );
	}

	$url = bcg_get_home_url();

	// if we are here, we can allow user to edit the post.
	return $url . "/{$action_name}/" . $post->ID . '/';
}

/**
 * Get Edit post link.
 *
 * @param int    $id post id.
 * @param string $label edit label.
 *
 * @return string
 */
function bcg_get_edit_link( $id = 0, $label = 'Edit' ) {

	if ( ! is_super_admin() && ! bcg_get_option( 'allow_edit' ) ) {
		return '';
	}

	$url = bcg_get_edit_url( $id );

	if ( ! $url ) {
		return '';
	}

	return "<a href='{$url}'>{$label}</a>";
}

/**
 * Get delete link
 *
 * @param int    $id post id.
 * @param string $label delete label.
 *
 * @return string|void
 */
function bcg_get_delete_link( $id = 0, $label = 'Delete' ) {

	$group_id = bp_get_current_group_id();

	if ( ! bcg_user_can_delete( $id, get_current_user_id(), $group_id ) ) {
		return;
	}
	$post        = get_post( $id );
	$action_name = 'delete';
	$url         = bcg_get_home_url();
	$url         = $url . "/{$action_name}/" . $post->ID . '/';

	return "<a href='{$url}' class='confirm' >{$label}</a>";
}
