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


//get post permalink which leads to group blog single post page
function bcg_get_post_permalink($post){
    
     return bp_get_group_permalink(groups_get_current_group()).BCG_SLUG."/".$post->post_name;
}
/**
 * Generate Pagination Link for posts
 * @param type $q 
 */
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
/**
 * Are we dealing with blog categories pages?
 * @return type 
 */
function bcg_is_component(){
    global $bp;
    if (bp_is_current_component($bp->groups->slug) && bp_is_current_action(BCG_SLUG ))
        return true;
    
    return false;
}
function bcg_is_single_post(){
    global $bp;
    if (bcg_is_component() &&!empty($bp->action_variables[0])&&(!in_array($bp->action_variables[0],array('create','category' ))))
         return true;

}
//is bcg_home
function bcg_is_home(){
    global $bp;
    if (bcg_is_component() &&empty($bp->action_variables[0]) )
         return true;

}
function is_bcg_pages(){
   return bcg_is_component();
}
function bcg_is_post_create(){
    global $bp;
    if (bcg_is_component() &&!empty($bp->action_variables[0])&&$bp->action_variables[0]=='create' )
         return true;

}

function bcg_is_category(){
    global $bp;
    if ( bcg_is_component() &&!empty($bp->action_variables[1])&&$bp->action_variables[0]=='category' )
         return true;
}
//sub menu
function bcg_get_options_menu(){?>
    <li <?php if(bcg_is_home ()):?> class="current"<?php endif;?>><a href="<?php echo bcg_get_home_url();?>"><?php _e("Posts","bcg");?></a></li>
    <?php if(bcg_current_user_can_post()):?>
        <li <?php if(bcg_is_post_create()):?> class="current"<?php endif;?>><a href="<?php echo bcg_get_home_url();?>/create"><?php _e("Create New Post","bcg");?></a></li>
  <?php endif;?>
 <?php
}


//form for showing category lists
function bcg_admin_form(){
    $group_id = bp_get_group_id();
    $bcg_is_active_setting = groups_get_groupmeta( $group_id, 'bcg_is_enabled' );
    $bcg_tab_label = groups_get_groupmeta( $group_id, 'bcg_tab_label' );
    if ( empty( $bcg_tab_label ) ) 
      $bcg_tab_label = __( 'Blog', 'bcg' );


    echo "<p>".__("Blog Categories for Groups allows your group to have a voice in this site&rsquo;s blog. If enabled, your group will be able to submit posts for publication within a specific category. Posts in this category (or categories) will also be displayed within your group&rsquo;s space.","bcg")."</p>";
    ?>
    <p>
       <label for="bcg_is_enabled"> <input type="checkbox" name="bcg_is_enabled" id="bcg_is_enabled" value="1" <?php checked( $bcg_is_active_setting, true ) ?> /> <?php _e( 'Enable Blog Categories for Groups for this group', 'bcg' ) ?></label>
    </p>
    <div id="bcg_settings_details">
    <?php
    echo "<p><label for='bcg_tab_label'>".__("Change the BuddyPress group tab label from 'Blog' to whatever you'd like.","bcg")."</label>";
    ?>
       <input type="text" name="bcg_tab_label" id="bcg_tab_label" value="<?php echo esc_html( $bcg_tab_label ); ?>" />
     </p>
    <?php

    $selected_cats = bcg_get_categories($group_id);
    echo "<p>".__("Select a category to associate its posts with this group.","bcg")."</p>";

    $cat_ids=get_all_category_ids();
    if(is_array($cat_ids)){ ////it is sure but do not take risk
      foreach($cat_ids as $cat_id){ //show the form
          $checked=0;
        	if(!empty($selected_cats)&&in_array($cat_id,$selected_cats))
        			$checked=true;
        	?>
        	<label  style="padding:5px;display:block;float:left;">
                <input type="checkbox" name="blog_cats[]" id="<?php $opt_id;?>" value="<?php echo $cat_id;?>" <?php if($checked) echo "checked='checked'" ;?>/>
                <?php echo get_cat_name($cat_id);?>
        	</label>
<?php
       } //Ends foreach
} else {
      ?>
    <div class="error">
        <p><?php _e("Please create the categories before trying to attach them to a group.","bcg");?></p>
    </div>
    <?php
     }
?>  </div> <!-- End #bcg_settings_details -->
    <div class="clear"></div>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        //match visibility on page load
        if ( jQuery('#bcg_is_enabled').is(':checked') ) {
              jQuery('#bcg_settings_details').show();
          } else {
              jQuery('#bcg_settings_details').hide();
          }
        //update visibility on change
        jQuery('#bcg_is_enabled').click(function() {
          if ( jQuery(this).is(':checked') ) {
              jQuery('#bcg_settings_details').show();
          } else {
              jQuery('#bcg_settings_details').hide();
          }
        });      
      });
    </script>

<?php
}




//post form if one quick pot is installed
function bcg_get_post_form($group_id){
    global $bp;
    $cat_selected=bcg_get_categories($group_id);//selected cats
    if(empty($cat_selected)){
             _e('This group has no categories associated with it. To post to group blog, first associate one or more categories with it.','bcg');
            return;
        }

    $all_cats=get_all_category_ids();
    $cats=array_diff($all_cats,$cat_selected);
    

    //for form
    $url=bp_get_group_permalink(new BP_Groups_Group($group_id)).BCG_SLUG."/create/";
    if(function_exists('bp_get_simple_blog_post_form')){
        
       $form=bp_get_simple_blog_post_form('bcg_form');
        if($form)
            $form->show();
        
    }
  
    do_action('bcg_post_form',$cats,$url);//pass the categories as array and the url of the current page
    
}

