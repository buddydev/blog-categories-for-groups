<?php

/**
 * Get an option value by name
 *
 * @param $option_name
 *
 * @return string
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
 * @return mixed
 */
function bcg_get_options() {
	$default = array(

		'post_type'				=> 'post',
		'post_status'			=> 'publish',
		'comment_status'		=> 'open',
		'show_comment_option'	=> 1,
		'custom_field_title'	=> '',
		'enable_taxonomy'		=> 1,
		'allowed_taxonomies'	=> 1,
		'enable_category'		=> 1,
		'enable_tags'			=> 1,
		'show_posts_on_profile' => 0,
		'limit_no_of_posts'		=> 0,
		'max_allowed_posts'		=> 20,
		'publish_cap'			=> 'read',
		'allow_unpublishing'	=> 1,//subscriber //see https://codex.wordpress.org/Roles_and_Capabilities
		'post_cap'				=> 'read',
		'allow_edit'			=> 1,
		'allow_delete'			=> 1,
		'allow_upload'			=> 1,
		//'enabled_tags'			=> 1,
		'taxonomies'		=> array( 'category' ),
		'allow_upload'		=> false,
		'max_upload_count'	=> 2,
		'post_update_redirect'	=> 'archive'
	);

	return bp_get_option( 'bcg-settings', $default );
}

/**
 * Are we dealing with blog categories pages?
 * @return boolean
 */
function bcg_is_component () {
	
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

	if ( bcg_is_component() && ! empty( $bp->action_variables[0] ) && ! in_array( $bp->action_variables[0],  array_merge( array( 'create', 'page', 'edit') , bcg_get_taxonomies() ) )  ) {
		return true;
	}
	return false;
}
/**
 * Is it post create csreen
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
 * for back compatibility we are keeping this function
 *
 * @return boolean
 */
function bcg_is_category () {

	return bcg_is_term();
}


/**
 * Check if blogging is disabled for the current group
 *
 * @global type $bp
 * @return type 
 */
function bcg_is_disabled_for_group() {
	
	$bp = buddypress();
	
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
 * @param $group_id
 *
 * @return bool|mixed|void
 */
function bcg_is_disabled( $group_id ) {
	
	if ( empty( $group_id ) ) {
		return false; //if grou id is empty, it is active
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

	$post_type  = ( bcg_get_option('post_type') ) ? bcg_get_option('post_type') : 'post';
	return apply_filters( 'bcg_get_post_type', $post_type );
}

/**
 * Get all allowed taxonomies names as array
 *
 * @return mixed|void
 */
function bcg_get_taxonomies() {

	$taxonomy = ( bcg_get_option( 'allowed_taxonomies' ) ) ? bcg_get_option( 'allowed_taxonomies' ) :  'category';
	return apply_filters( 'bcg_get_taxonomies', (array) $taxonomy );
}


//todo remove
function bcg_get_all_terms() {

	$taxonomy =   bcg_get_taxonomies();
	$cats = get_terms( $taxonomy, array( 'fields' => 'all', 'get' => 'all' ) );
	return $cats;
}

function bcg_get_associated_terms( $group_id ) {

}
function bcg_get_terms( $group_id, $taxonomy = 'category' ) {

}

//call me business function
function bcg_get_categories( $group_id ) {
	
	$cats = groups_get_groupmeta( $group_id, 'group_blog_cats' );
	return maybe_unserialize( $cats );
}

//update table
function bcg_update_categories( $group_id, $cats ) {
	
	$cats = maybe_serialize( $cats );
	
	return groups_update_groupmeta( $group_id, 'group_blog_cats', $cats );
}


/**
 * Get a post by slug name
 *
 * @param $slug
 *
 * @return WP_Post
 */
function bcg_get_post_by_slug( $slug ) {
	global $wpdb;
	
	$query = "SELECT * FROM $wpdb->posts WHERE post_name = %s AND post_type = %s LIMIT 1";
	$post = $wpdb->get_row( $wpdb->prepare( $query, $slug, bcg_get_post_type() ) );
	
	return $post;
}


function bcg_get_group_post_status( $user_id ) {

	$authority_to_publish   = bcg_get_option( 'publish_cap' );
	$group_id               = bp_get_current_group_id();
	$post_status            = 'draft';

	if ( bcg_can_user_publish_post( get_current_user_id() ) ) {
		$post_status = 'publish';
	}

	return $post_status;
}

/**
 * Check if  post is published
 *
 * @param $post_id
 *
 * @return bool
 */
function bcg_is_post_published( $post_id ) {

	return get_post_field( 'post_status', $post_id ) == 'publish';
}

function bcg_can_user_publish_post( $user_id ) {

	$can_publish = false;

	if ( ! $user_id ) {
		return $can_publish;
	}

	$publish_cap = bcg_get_option( 'publish_cap' );
	$group_id = bp_get_current_group_id();

	if ( is_super_admin() ) {
		$can_publish = true;
	} elseif ( $publish_cap == 'admin' && groups_is_user_admin( $user_id, $group_id ) ) {
		$can_publish = true;
	} elseif (  $publish_cap == 'members' && groups_is_user_member( $user_id, $group_id ) ){
		$can_publish = true;
	}

	return $can_publish;

}

function bcg_limit_no_of_posts() {

	return apply_filters( 'bcg_limit_no_of_posts', bcg_get_option( 'limit_no_of_posts' ) );
}

function bcg_get_allowed_no_of_posts( $user_id = false ) {

	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id ();
	}
	//filter on this hook to change the no. of posts allowed
	return apply_filters( 'bcg_allowed_posts_count', bcg_get_option( 'max_allowed_posts' ), $user_id );//by default no. posts allowed
}

/**
 * @param bool $user_id
 * //may be a bad implementation here
 * @return int
 */
function bcg_get_total_published_posts( $user_id = false ) {

	if ( ! $user_id ) {
		$user_id = get_current_user_id ();
	}
	//Needs revisist
	global $wpdb;

	$count = $wpdb->get_var( $wpdb->prepare( "SELECT count('*') FROM {$wpdb->posts} WHERE  post_author=%d AND post_type=%s AND post_status='publish'", $user_id,  bcg_get_posttype() ) );

	return intval( $count);
}
function bcg_get_remaining_posts( $user_id = false ) {

	$total_allowed = bcg_get_allowed_no_of_posts( $user_id );

	return intval( $total_allowed - bcg_get_total_published_posts( $user_id ) );
}

/**
 * Get BCG landing page url
 *
 * @param type $group_id
 * @return type
 */
function bcg_get_home_url ( $group_id = null ) {

	if ( ! empty( $group_id ) ) {
		$group = new BP_Groups_Group( $group_id );
	} else {
		$group = groups_get_current_group();
	}

	return apply_filters( 'bcg_home_url', bp_get_group_permalink( $group ) . BCG_SLUG );
}
/**
 * @param bool $post_id
 * @param string $label_ac
 * @param string $label_de
 *
 * @return string|void
 */
function bcg_get_post_publish_unpublish_link( $post_id = false, $label_ac = 'Publish', $label_de = 'Unpublish' ) {

	if ( ! $post_id ) {
		return;
	}

	if ( ! bcg_user_can_publish( get_current_user_id() ) ) {
		return ;
	}

	$post = get_post( $post_id );
	$user_id = get_current_user_id();
	$url = '';

	if ( ! ( is_super_admin() || $post->post_author == $user_id || groups_is_user_admin( $user_id, bp_get_current_group_id() ) ) ) {
		return;
	}

	//check if post is published
	$url = bcg_get_post_publish_unpublish_url( $post_id );

	if( ! bcg_get_option( 'allow_unpublishing' ) ){
		return;
	}

	if ( bcg_is_post_published( $post_id ) ) {
		$link = "<a href='{$url}'>{$label_de}</a>";
	} else {
		$link = "<a href='{$url}'>{$label_ac}</a>";
	}

	return $link;

}

function bcg_get_post_publish_unpublish_url( $post_id = false ) {

	if ( ! $post_id ) {
		return;
	}

	$post = get_post( $post_id );
	$url = '';

	if ( bcg_user_can_publish( get_current_user_id(), $post_id ) ) {
		//check if post is published
		$url = bcg_get_home_url();

		if ( bcg_is_post_published( $post_id ) ) {
			$url = $url . '/unpublish/' . $post_id . '/';
		} else {
			$url = $url . '/publish/' . $post_id . '/';
		}
	}

	return $url;

}


function bcg_get_edit_url( $post_id = false ) {

	$user_id = get_current_user_id();
	$group_id = bp_get_current_group_id();

	if ( ! $user_id && ! $group_id ) {
		return;
	}

	if ( empty( $post_id ) ) {

		$post_id = get_the_ID();

	}
	//cheeck if current user can edit the post
	$post = get_post( $post_id );
	//if the author of the post is same as the loggedin user or the logged in user is admin

	if ( $post->post_type != bcg_get_post_type() ) {

		return false;
	}


	if ( $post->post_author != $user_id && ! is_super_admin() && ! groups_is_user_admin( $user_id, $group_id ) ) {
		return ;
	}

	$action_name = 'edit';

	if ( current_user_can( bcg_get_option( 'dashboard_edit_cap' ) ) ) {
		return get_edit_post_link ( $post );
	}

	$url = bcg_get_home_url();
	//if we are here, we can allow user to edit the post
	return $url . "/{$action_name}/" . $post->ID . '/';
}


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
 * @param int $id
 * @param string $label
 *
 * @return string|void
 */
function bcg_get_delete_link( $id = 0, $label = 'Delete' ) {

	$group_id = bp_get_current_group_id();

	if ( ! bcg_user_can_delete( $id,  get_current_user_id(), $group_id ) ) {
		return;
	}

	$bp             = buddypress();
	$post           = get_post( $id );
	$action_name    = 'delete';
	$url            = bcg_get_home_url();
	$url            = $url . "/{$action_name}/" . $post->ID . '/';

	return "<a href='{$url}' class='confirm' >{$label}</a>";

}
