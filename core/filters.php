<?php
// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Get editable post id.
 *
 * @param int $id post id.
 *
 * @return int
 */
function bcg_get_editable_post_id( $id ) {

	$action   = bp_action_variable( 0 );
	$user_id  = get_current_user_id();
	$post_id  = bp_action_variable( 1 );
	$group_id = bp_get_current_group_id();

	if ( ! $post_id || ! is_numeric( $post_id ) || ! bcg_user_can_edit( $post_id, $user_id, $group_id ) ) {
		return $id;
	}

	if ( bcg_is_component() && ( $action == 'edit' ) && $post_id ) {
		$id = $post_id;
	}

	// intval or absint?
	return intval( $id );
}

add_filter( 'bpsp_editable_post_id', 'bcg_get_editable_post_id' );

/**
 * Modify recorded post types
 *
 * @param array $post_types post_types Array.
 *
 * @return array
 */
function bcg_modify_recorded_post_types( $post_types ) {
	$bcg_post_type = bcg_get_post_type();

	if ( in_array( $bcg_post_type, $post_types ) ) {
		return $post_types;
	}

	$post_types[] = $bcg_post_type;

	return $post_types;
}
add_filter( 'bp_blogs_record_post_post_types', 'bcg_modify_recorded_post_types' );
