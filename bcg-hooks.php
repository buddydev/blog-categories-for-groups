<?php

/**
 * Update and save group preference
 */
add_action('groups_group_settings_edited','bcg_save_group_prefs');
add_action('groups_create_group','bcg_save_group_prefs');
add_action('groups_update_group','bcg_save_group_prefs');

function bcg_save_group_prefs($group_id){
      $disable=$_POST['group-disable-bcg'];
      groups_update_groupmeta($group_id, 'bcg_is_active', $disable);//save preference
}

/*put a settings for allowing disallowing the bcg*/
//Replaced by enable checkbox and settings
// add_action('bp_before_group_settings_admin','bcg_group_disable_form');
// add_action('bp_before_group_settings_creation_step','bcg_group_disable_form');
//check if the group yt is enabled
function bcg_group_disable_form(){?>

    <div class="checkbox">
        <label><input type="checkbox" name="group-disable-bcg" id="group-disable-bcg" value="1" <?php if(bcg_is_disabled_for_group()):?> checked="checked"<?php endif;?>/> <?php _e( 'Disable group blog.', 'bcg' ) ?></label>
    </div>
<?php

}

//comment posting a lil bit better
add_action('comment_form','bcg_fix_comment_form' );

function bcg_fix_comment_form($post_id){
    if(!bcg_is_single_post())
        return;
    $post=get_post($post_id);
    $permalink=  bcg_get_post_permalink($post);
 ?>
    <input type='hidden' name='redirect_to' value="<?php echo esc_url($permalink);?>" />
 <?php
}


/* fixing permalinks for posts/categories inside the bcg loop*/


//fix post permalink, should we ?
add_filter('post_link','bcg_fix_permalink',10,3);
function bcg_fix_permalink($post_link, $id, $leavename){
    if(!is_bcg_pages()||!in_bcg_loop())
        return $post_link;

    $post_link=bcg_get_post_permalink(get_post($id));
    return $post_link;
}

//on Blog category pages fix the category link to point to internal, may cause troubles in some case
add_filter( 'category_link', 'bcg_fix_category_permalink',10,2 );
function bcg_fix_category_permalink($catlink, $category_id){
     if(!is_bcg_pages ()||!in_bcg_loop())
         return $catlink;
     
    $permalink=bcg_get_home_url();
    $cat=get_category($category_id);
    //think about the cat permalink, do we need it or not?

    return $permalink.'/category/'.$category_id;//no need for category_name
}