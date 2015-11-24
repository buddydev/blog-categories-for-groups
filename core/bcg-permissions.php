<?php

/**
 * Can the current user post to group blog
 * @global type $bp
 * @return type 
 */
function bcg_current_user_can_post () {
	
	$user_id = bp_loggedin_user_id();
	$group_id = bp_get_current_group_id();
	
	$can_post = is_user_logged_in() && ( groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id ) );

	return apply_filters( 'bcg_current_user_can_post', $can_post, $group_id, $user_id );
}

/**
 * Can user edit the post
 * 
 * @return bool 
 */
function bcg_user_can_edit ( $post_id, $user_id = false ) {
	//if user is logged in and the post id is given only then we should proceed
	if ( ! $post_id || ! is_user_logged_in() ) {
		return false;
	}
	
	if ( is_super_admin() ) {
		return true;
	}
	
	//in case you don't want to allow edit
	
	if ( ! apply_filters( 'bcg_allow_edit', 1 ) ) {
		return ;
	}
	
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	
	$post = get_post( $post_id );

	if ( $post->post_author == $user_id ) {
		return true;
	}
	//check moderator etc
	return false;
	//check if it is the 
}

/**
 * Can the user delete this post
 * 
 * @param type $post_id
 * @param type $user_id
 * @return bool
 */
function bcg_user_can_delete ( $post_id, $user_id = false ) {
	
	if ( ! $post_id && in_the_loop() ) {
		$post_id = get_the_ID();
	}	

	if ( ! $post_id || ! is_user_logged_in() ) {
		return false;
	}
	
	if ( is_super_admin() ) {
		return true;
		
	}
	
	if ( ! apply_filters( 'bcg_allow_delete', 1 ) ) {
		return ;
	}
	
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	
	$post = get_post( $post_id );
	
	if ( $post->post_author == $user_id ) {
		return true;
	}
	//check for moderator etc
	return false;
}

