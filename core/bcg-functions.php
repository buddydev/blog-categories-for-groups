<?php
/**
 * Are we dealing with blog categories pages?
 * @return type 
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

	if ( bcg_is_component() && ! empty( $bp->action_variables[0] ) && ( ! in_array( $bp->action_variables[0], array( 'create', 'page', 'edit', bcg_get_taxonomy() ) ) ) ) {
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
 * Is it single category view
 * 
 * @return boolean
 */
function bcg_is_category () {
	$bp = buddypress();

	if ( bcg_is_component() && !empty( $bp->action_variables[1] ) && $bp->action_variables[0] == bcg_get_taxonomy() ) {
		return true;
	}
}


/**
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
		$group_id = $bp->groups->current_group->id;
	}
	
	return apply_filters( 'bcg_is_disabled_for_group', bcg_is_disabled( $group_id ) );
}

function bcg_is_disabled( $group_id ) {
	
	if ( empty( $group_id ) ) {
		return false; //if grou id is empty, it is active
	}
	
	$is_disabled = groups_get_groupmeta( $group_id, 'bcg_is_active' );
	
	return apply_filters( 'bcg_is_disabled', intval( $is_disabled ), $group_id );
}

function bcg_get_taxonomy() {

	return apply_filters( 'bcg_get_taxonomy', 'category' );
}

function bcg_get_post_type() {

	return apply_filters( 'bcg_get_post_type', 'post' );
}

function bcg_get_all_terms() {

	$cats = get_terms( bcg_get_taxonomy(), array( 'fields' => 'all', 'get' => 'all' ) );
	return $cats;
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


