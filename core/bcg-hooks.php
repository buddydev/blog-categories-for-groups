<?php
/**
 * Update and save group preference
 */
add_action( 'groups_group_settings_edited', 'bcg_save_group_prefs' );
add_action( 'groups_create_group', 'bcg_save_group_prefs' );
add_action( 'groups_update_group', 'bcg_save_group_prefs' );

function bcg_save_group_prefs( $group_id ) {
	
	$disable = isset( $_POST['group-disable-bcg'] ) ? 1: 0;
	groups_update_groupmeta( $group_id, 'bcg_is_active', $disable ); //save preference
}

/* put a settings for allowing disallowing the bcg */
add_action( 'bp_before_group_settings_admin', 'bcg_group_disable_form' );
add_action( 'bp_before_group_settings_creation_step', 'bcg_group_disable_form' );

//check if the group yt is enabled
function bcg_group_disable_form () {
	?>
	<div class="checkbox">
		<label><input type="checkbox" name="group-disable-bcg" id="group-disable-bcg" value="1" <?php if ( bcg_is_disabled_for_group() ): ?> checked="checked"<?php endif; ?>/> <?php _e( 'Disable Blog Categories', 'blog-categories-for-groups' ) ?></label>
	</div>
	<?php
}

//comment posting a lil bit better
add_action( 'comment_form', 'bcg_fix_comment_form' );

function bcg_fix_comment_form ( $post_id ) {
	
	if ( ! bcg_is_single_post() ) {
		return;
	}
	
	$post = get_post( $post_id );
	$permalink = bcg_get_post_permalink( $post );
	?>
	<input type='hidden' name='redirect_to' value="<?php echo esc_url( $permalink ); ?>" />
	<?php
}
//fix to disable/reenable buddypress comment open/close filter
function bcg_disable_bp_comment_filter() {
    
    if( has_filter( 'comments_open', 'bp_comments_open' ) ) {
        remove_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}	
}
add_action( 'bp_before_group_blog_post_content', 'bcg_disable_bp_comment_filter' );

function bcg_enable_bp_comment_filter() {
    
    if( function_exists( 'bp_comments_open' ) ) {
		add_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}	
}

add_action( 'bp_after_group_blog_content', 'bcg_enable_bp_comment_filter' );

/* fixing permalinks for posts/categories inside the bcg loop */


//fix post permalink, should we ?
if ( bcg_get_post_type() == 'post' ) {
	add_filter( 'post_link', 'bcg_fix_permalink', 10, 3 );
} else {
	add_filter( 'post_type_link', 'bcg_fix_permalink', 10, 3 );
}

function bcg_fix_permalink( $post_link, $id, $leavename ) {
	
	if ( ! bcg_is_component() || ! in_bcg_loop() ) {
		return $post_link;
	}

	$post_link = bcg_get_post_permalink( get_post( $id ) );
	return $post_link;
}

//on Blog category pages fix the category link to point to internal, may cause troubles in some case
add_filter( 'category_link', 'bcg_fix_category_permalink', 10, 2 );

function bcg_fix_category_permalink ( $catlink, $category_id ) {
	
	if ( ! bcg_is_component() || ! in_bcg_loop() ) {
		return $catlink;
	}

	$term = get_term($category_id);
	$allowed_taxonomies = bcg_get_taxonomies();

	if ( ! in_array( $term->taxonomy, $allowed_taxonomies ) ) {
		return $catlink;
	}
	//it is our taxonomy


	$permalink = trailingslashit( bcg_get_home_url() );
	//$cat       =  get_category( $category_id );
	//think about the cat permalink, do we need it or not?

	///we need to work on this
	return $permalink . $term->taxonomy . '/' . $category_id; //no need for category_name
}

add_filter( 'wp_title', 'bcg_fix_page_title', 200, 3 );
//for title fix
function bcg_fix_page_title( $title, $sep, $seplocation ) {
	
	if ( ! bcg_is_single_post() ) {
		return $title;
	}
	
	$post = bcg_get_post_by_slug( bp_action_variable(0) );
	
	$post_title =  $post->post_title;
	
	if ( 'right' == $seplocation ) { // sep on right, so reverse the order
		$title       =  $post_title . " $sep " . $title;
	} else {
		$title =  $title . " $sep " . $post_title;
	}
	
	return $title;

}


add_filter( 'bp_activity_get_activity_id', 'bcg_update_group_post_activity', 0, 2);
function bcg_update_group_post_activity( $id, $args ) {


	if ( $args['component'] == 'blogs' && $args['type'] == 'new_blog_post' ) {

		unset( $args['item_id']);
		//now set component to groups
		$args['component'] = buddypress()->groups->id;

		$new_id = bp_activity_get_activity_id( $args );

		if( $new_id ) {
			$id = $new_id;
		}


	}

	return $id;
}

add_action( 'bp_activity_post_type_unpublished', 'bcg_delete_group_post_activity', 0 , 3 );
function bcg_delete_group_post_activity( $delete_activity_args, $post, $deleted ) {

	if( $delete_activity_args['component'] == 'blogs' && $delete_activity_args['type'] == "new_blog_post") {

		unset( $delete_activity_args['item_id'] );
		$delete_activity_args['component'] = buddypress()->groups->id;
		$deleted = bp_activity_delete( $delete_activity_args );

	}
	return $deleted;

}

function bcg_format_activity_action( $action, $activity  ) {

	$user_link = bp_core_get_userlink( $activity->user_id );

	//$user_name = bp_core_get_user_displayname( $activity->user_id );

	if ( isset( $activity->post_url ) ) {
		$post_url = $activity->post_url;
	}

	$post_title = bp_activity_get_meta( $activity->id, 'post_title' );

	if ( empty( $post_title ) ) {
		// Defaults to no title.
		$post_title = esc_html__( '(no title)', 'blog-categories-for-groups' );

	}else {
		$post_title = esc_html__( $post_title, 'blog-categories-for-groups' );
	}

	$group = groups_get_group( array( 'group_id' => $activity->item_id ) );
	$group_permalink = bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug;

	$post_link  = '<a href="' . esc_url( $post_url ) . '">' . $post_title . '</a>';
	$group_link =  '<a href="' . esc_url( $group_permalink ) . '">' . esc_html( $group->name ) . '</a>';

	$action = sprintf( __( '%1$s wrote a new post in %2$s, %3$s', 'blog-categories-for-groups' ), $user_link, $group_link, $post_link );

	return $action;
}

add_action( 'groups_register_activity_actions', 'bcg_register_group_activity_action' );
function bcg_register_group_activity_action() {

	$bp = buddypress();
	bp_activity_set_action(
		$bp->groups->id,
		'new_blog_post',
		__( 'Group details edited', 'blog-categories-for-groups' ),
		'bcg_format_activity_action',
		__( 'Group Updates', 'blog-categories-for-groups' ),
		array( 'activity', 'group', 'member', 'member_groups' )
	);

}