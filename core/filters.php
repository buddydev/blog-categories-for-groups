<?php
function bcg_get_editable_post_id( $id ) {

	$action = bp_action_variable(0);
	$user_id = get_current_user_id();
	$post_id = bp_action_variable(1);
	$group_id = bp_get_current_group_id();

	if( ! $post_id || ! is_numeric( $post_id ) || ! bcg_user_can_edit( $post_id, $user_id, $group_id  ) ) {
		return $id;
	}

	if ( bcg_is_component() && ( $action == 'edit' ) && $post_id ) {
		$id = $post_id;
	}
	return intval ( $id );//intval or absint?

}
add_filter( 'bpsp_editable_post_id', 'bcg_get_editable_post_id' );
