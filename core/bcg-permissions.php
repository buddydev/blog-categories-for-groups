<?php

/**
 * Can the current user post to group blog
 * @global type $bp
 * @return type 
 */
function bcg_current_user_can_post () {
	
	$user_id = bp_loggedin_user_id();
	$group_id = bp_get_current_group_id();
	$can_post = false;

	if ( is_user_logged_in() && ( bcg_get_option( 'post_cap' ) == 'admin' && groups_is_user_admin( $user_id, $group_id ) ) ) {
		$can_post = true;
	}

	if( is_user_logged_in() && ( bcg_get_option('post_cap') == 'members' && groups_is_user_member( $user_id, $group_id ) ) ){
		$can_post = true;
	}

	//$can_post = is_user_logged_in() && ( groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id ) );

	return apply_filters( 'bcg_current_user_can_post', $can_post, $group_id, $user_id );
}

function bcg_user_can_publish ( $user_id, $post_id = false ) {

	//super admins can always post
	if ( is_super_admin() ) {
		return true;
	}

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$can_publish = false;
	//by default, everyone can publish, we assume
	if ( is_user_logged_in() ) {
		$can_publish = bcg_can_user_publish_post( $user_id );
	}
	
	//has the admin set a limit on no. of posts?
	if ( is_user_logged_in() && bcg_limit_no_of_posts() ) {
		//let us find the user id
		//find remaining posts count
		$remaining_posts = bcg_get_remaining_posts( $user_id );

		if ( $remaining_posts > 0 ) {
			$can_publish = 1;
		}
	}

	return apply_filters( 'bcg_user_can_publish', $can_publish, $user_id );
}

/**
 * Can user edit the post
 * 
 * @return bool 
 */
function bcg_user_can_edit ( $post_id, $user_id = false, $group_id ) {
	//if user is logged in and the post id is given only then we should proceed
	if ( ! $post_id || ! is_user_logged_in() || ! $group_id ) {
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

	if ( $post->post_author == $user_id || groups_is_user_admin( $user_id, $group_id ) ) {
		return true;
	}
	//check moderator etc
	return false;
	//check if it is the 
}

function bcg_user_can_delete ( $post_id, $user_id = false, $group_id ) {
	
	if ( ! $post_id && in_the_loop() ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id || ! is_user_logged_in() || ! $group_id ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;

	} elseif ( ! bcg_get_option( 'allow_delete') ) {
		//if deleting post is disabled
		return false;
	}

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$post = get_post( $post_id );

	if ( $post->post_author == $user_id || groups_is_user_admin( $user_id, $group_id )  ) {
		return true;
	}

	return false;
}
