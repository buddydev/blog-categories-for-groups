<?php
/*
 * Template Tags for Blog categories
 *
 */

//if inside the post loop
function in_bcg_loop() {
	
	$bp = buddypress();
	
	return isset( $bp->bcg ) ? $bp->bcg->in_the_loop : false;
}

//use it to mark t5he start of bcg post loop
function bcg_loop_start() {
	$bp = buddypress();

	$bp->bcg = new stdClass();
	$bp->bcg->in_the_loop = true;
}

//use it to mark the end of bcg loop
function bcg_loop_end() {
	$bp = buddypress();

	$bp->bcg->in_the_loop = false;
}

//get post permalink which leads to group blog single post page
function bcg_get_post_permalink( $post ) {

	return bp_get_group_permalink( groups_get_current_group() ) . BCG_SLUG . '/' . $post->post_name;
}

/**
 * Generate Pagination Link for posts
 * @param type $q 
 */
function bcg_pagination( $q ) {

	$posts_per_page = intval( get_query_var( 'posts_per_page' ) );
	$paged = intval( get_query_var( 'paged' ) );
	
	$numposts = $q->found_posts;
	$max_page = $q->max_num_pages;
	
	if ( empty( $paged ) || $paged == 0 ) {
		$paged = 1;
	}

	$pag_links = paginate_links( array(
		'base'		=> add_query_arg( array( 'paged' => '%#%', 'num' => $posts_per_page ) ),
		'format'	=> '',
		'total'		=> ceil( $numposts / $posts_per_page ),
		'current'	=> $paged,
		'prev_text'	=> '&larr;',
		'next_text'	=> '&rarr;',
		'mid_size'	=> 1
	) );
	
	echo $pag_links;
}

//viewing x of z posts
function bcg_posts_pagination_count( $q ) {

	$posts_per_page = intval( get_query_var( 'posts_per_page' ) );
	$paged = intval( get_query_var( 'paged' ) );
	
	$numposts = $q->found_posts;
	$max_page = $q->max_num_pages;
	
	if ( empty( $paged ) || $paged == 0 ) {
		$paged = 1;
	}

	$start_num = intval( $posts_per_page * ( $paged - 1 ) ) + 1;
	$from_num = bp_core_number_format( $start_num );
	$to_num = bp_core_number_format( ( $start_num + ( $posts_per_page - 1 ) > $numposts ) ? $numposts : $start_num + ( $posts_per_page - 1 )  );
	
	$total = bp_core_number_format( $numposts );

	//$taxonomy = get_taxonomy( bcg_get_taxonomies() );
	$post_type_object = get_post_type_object( bcg_get_post_type() );

	printf( __( 'Viewing %1s %2$s to %3$s (of %4$s )', 'blog-categories-for-groups' ), $post_type_object->labels->name, $from_num, $to_num, $total ) . "&nbsp;";
	//$term = get_term_name ( $q->query_vars['cat'] );
	//if( bcg_is_category() )
	// printf( __( "In the %s %s ","bcg" ), $taxonomy->label, "<span class='bcg-cat-name'>". $term_name ."</span>" );
	?>
	<span class="ajax-loader"></span><?php
}


//sub menu
function bcg_get_options_menu() {
	?>
	<li <?php if ( bcg_is_home() ): ?> class="current"<?php endif; ?>><a href="<?php echo bcg_get_home_url(); ?>"><?php _e( "Posts", "blog-categories-for-groups" ); ?></a></li>
	<?php if ( bcg_current_user_can_post() ): ?>
		<li <?php if ( bcg_is_post_create() ): ?> class="current"<?php endif; ?>><a href="<?php echo bcg_get_home_url(); ?>/create"><?php _e( "Create New Post", "blog-categories-for-groups" ); ?></a></li>
	<?php endif; ?>
	<?php
}

//form for showing category lists
function bcg_admin_form() {
	
	$group_id = bp_get_group_id();

	$selected_cats = bcg_get_categories( $group_id );

	$allowed_taxonomies = bcg_get_taxonomies();
	$terms = bcg_get_all_terms();

	echo "<p>" . sprintf( __( "Select Terms", "blog-categories-for-groups" ) ) . "</p>";
	foreach ( $allowed_taxonomies as $taxonomy ) {

		$tax = get_taxonomy( $taxonomy );
		_bcg_list_tax_terms( $tax, $terms, $selected_cats );

	}


	?>
	<div class="clear"></div>

	<?php
}

function _bcg_list_tax_terms( $tax, $terms, $selected_terms ) {


	echo "<div class='bcg-editable-terms-list clearfix'>";
	echo "<label class='bcg-taxonomy-name'>{$tax->labels->singular_name}</label>";
	foreach ( $terms as $term ) {//show the form
		//the back compat is killing the quality
		if ( $tax->name != $term->taxonomy ) {
			continue;
		}

		$checked = 0;

		if ( ! empty( $selected_terms ) && in_array( $term->term_id, $selected_terms ) ) {
			$checked = true;
		}
		?>
		<label  style="padding:5px;display:block;float:left;">
			<input type="checkbox" name="blog_cats[]"  value="<?php echo $term->term_id; ?>" <?php if ( $checked ) echo "checked='checked'"; ?> />
			<?php echo $term->name; ?>
		</label>

		<?php
	}
	echo "<div style='clear:both;'></div>";
	echo "</div>";
}
//post form if one quick pot is installed
function bcg_show_post_form( $group_id ) {
	
	$bp = buddypress();

	$cat_selected = bcg_get_categories( $group_id ); //selected cats
	
	if ( empty( $cat_selected ) ) {
		_e( 'This group has no associated categories. To post to Group blog, you need to associate some categoris to it.', 'blog-categories-for-groups' );
		return;
	}

	$all_cats = (array) bcg_get_all_terms();
	$all_cats = wp_list_pluck( $all_cats, 'term_id' );
	
	$cats = array_diff( $all_cats, $cat_selected );

	//for form
	$url = bp_get_group_permalink( new BP_Groups_Group( $group_id ) ) . BCG_SLUG . "/create/";
	
	if ( function_exists( 'bp_get_simple_blog_post_form' ) ) {

		$form = bp_get_simple_blog_post_form( 'bcg_form' );
		
		if ( $form ) {
			$form->show();
		}
	}

	do_action( 'bcg_post_form', $cats, $url ); //pass the categories as array and the url of the current page
}

