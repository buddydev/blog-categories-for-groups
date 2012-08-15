<?php
/*
 * Template Tags for Blog categories
 *
 */

//if inside the post loop
function in_bcg_loop(){
    global $bp;

    return isset($bp->bcg)? $bp->bcg->in_the_loop:false;
}
//use it to mark t5he start of bcg post loop
function bcg_loop_start(){
    global $bp;
     $bp->bcg=new stdClass();
    $bp->bcg->in_the_loop=true;
}

//use it to mark the end of bcg loop
function bcg_loop_end(){
    global $bp;
    
    $bp->bcg->in_the_loop=false;
}


/* fixing permalinks for posts/categories inside the bcg loop*/

//fix post permalink, should we ?
add_filter("post_link","bcg_fix_permalink",10,3);
function bcg_fix_permalink($post_link, $id, $leavename){
    if(!is_bcg_pages()||!in_bcg_loop())
        return $post_link;

    $post_link=bcg_get_post_permalink(get_post($id));
    return $post_link;
}
//on Blog category pages fix the category link to point to internal, may cause troubles in some case
add_filter( 'category_link', "bcg_fix_category_permalink",10,2 );
function bcg_fix_category_permalink($catlink, $category_id){
     if(!is_bcg_pages ()||!in_bcg_loop())
         return $catlink;
    $permalink=bcg_get_home_url();
    $cat=get_category($category_id);
    //think about the cat permalink, do we need it or not?

    return $permalink."/category/".$category_id;//no need for category_name
}


//get post permalink which leads to group blog single post page
function bcg_get_post_permalink($post){
    global $bp;
      return bp_get_group_permalink($bp->groups->current_group).BCG_SLUG."/".$post->post_name;
}
function bcg_pagination($q) {

		$posts_per_page = intval(get_query_var('posts_per_page'));
		$paged = intval(get_query_var('paged'));
		$numposts = $q->found_posts;
                $max_page = $q->max_num_pages;
		if(empty($paged) || $paged == 0) {
			$paged = 1;
		}

     $pag_links = paginate_links( array(
			'base' => add_query_arg( array( 'paged' => '%#%', 'num' => $posts_per_page ) ),
			'format' => '',
			'total' => ceil($numposts / $posts_per_page),
			'current' => $paged,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'mid_size' => 1
		));
echo $pag_links;
}
//viewing x of z posts
function bcg_posts_pagination_count($q){

		$posts_per_page = intval(get_query_var('posts_per_page'));
		$paged = intval(get_query_var('paged'));
		$numposts = $q->found_posts;
                $max_page = $q->max_num_pages;
		if(empty($paged) || $paged == 0) {
			$paged = 1;
		}

   $start_num = intval( $posts_per_page*($paged-1) ) + 1;
   $from_num = bp_core_number_format( $start_num );
   $to_num = bp_core_number_format( ( $start_num + ( $posts_per_page - 1 ) > $numposts ) ? $numposts : $start_num + ( $posts_per_page - 1 ) );
    $total = bp_core_number_format( $numposts );

	printf( __( 'Viewing posts %1$s to %2$s (of %3$s posts)', 'bcg' ), $from_num, $to_num, $total )."&nbsp;";
        if(bcg_is_category())
           printf(__("In the category %s ","bcg"), "<span class='bcg-cat-name'>". get_cat_name ($q->query_vars['cat'])."</span>");?>
	<span class="ajax-loader"></span><?php
}

function bcg_is_single_post(){
    global $bp;
    if ( $bp->current_component == $bp->groups->slug && $bp->current_action==BCG_SLUG &&!empty($bp->action_variables[0])&&(!in_array($bp->action_variables[0],array('create','category' ))))
         return true;

}
//is bcg_home
function bcg_is_home(){
    global $bp;
    if ( $bp->current_component == $bp->groups->slug && $bp->current_action==BCG_SLUG &&empty($bp->action_variables[0]) )
         return true;

}
function is_bcg_pages(){
     global $bp;
    if ( $bp->current_component == $bp->groups->slug && $bp->current_action==BCG_SLUG  )
         return true;
}
function bcg_is_post_create(){
    global $bp;
    if ( $bp->current_component == $bp->groups->slug && $bp->current_action==BCG_SLUG &&!empty($bp->action_variables[0])&&$bp->action_variables[0]=='create' )
         return true;

}

function bcg_is_category(){
   global $bp;
    if ( $bp->current_component == $bp->groups->slug && $bp->current_action==BCG_SLUG &&!empty($bp->action_variables[1])&&$bp->action_variables[0]=='category' )
         return true;
}
?>
