<?php

function bcg_load_template($template){
    
    if(is_readable(STYLESHEETPATH.'/'.$template))
            $load=STYLESHEETPATH.'/'.$template;
    elseif(is_readable(TEMPLATEPATH.'/'.$template))
            $load=TEMPLATEPATH.'/'.$template;
    else
            $load=BCG_PLUGIN_DIR.$template;
    
        include_once $load;
}
/**
 *
 * @global type $bp
 * @return type 
 */
function bcg_is_disabled_for_group(){
    global $bp;
    $group_id=false;
    if (bp_is_group_create())
        $group_id=$_COOKIE['bp_new_group_id'];
   else if(bp_is_group ())
        $group_id=$bp->groups->current_group->id;

    return apply_filters('bcg_is_disabled_for_group',bcg_is_disabled($group_id));
}
function bcg_is_enabled_for_group(){
    global $bp;
    $group_id=false;
    if ( bp_is_group_create() )
        $group_id = $_COOKIE['bp_new_group_id'];
    else if( bp_is_group() )
        $group_id = $bp->groups->current_group->id;

    return apply_filters('bcg_is_enabled_for_group',bcg_is_enabled($group_id));
}

/**
 * Can the current user post to group blog
 * @global type $bp
 * @return type 
 */
function bcg_current_user_can_post(){
    // global $bp;
    $user_id =  bp_loggedin_user_id();
    $group_id =  bp_get_current_group_id();
    $level_to_post = groups_get_groupmeta( $group_id,'bcg_level_to_post' );
    $can_post = false;

    if ( $user_id ) {
        switch ($level_to_post) {
            case 'member':
                $can_post = ( groups_is_user_member($user_id, $group_id) );
                break;
            case 'mod':
                $can_post = ( groups_is_user_admin($user_id, $group_id) 
                            || groups_is_user_mod($user_id, $group_id) 
                            );
                break;
            case 'admin':
            default:
                $can_post = groups_is_user_admin($user_id, $group_id);
                break;
        }
    }
    
    return apply_filters('bcg_current_user_can_post',$can_post,$group_id,$user_id);
}

function  bcg_get_home_url($group_id=null){
    global $bp;

if(!empty($group_id))
    $group=new BP_Groups_Group ($group_id);
else
    $group=  groups_get_current_group();

return apply_filters('bcg_home_url',  bp_get_group_permalink($group).bcg_get_slug());
}

function bcg_is_disabled($group_id){
    if(empty($group_id))
        return false; //if grou id is empty, it is active
    $is_disabled=groups_get_groupmeta($group_id,"bcg_is_active");
    return apply_filters("bcg_is_disabled",intval($is_disabled),$group_id);
}
function bcg_is_enabled($group_id){
    //Value will be 1 if active
    $is_enabled = (bool) groups_get_groupmeta($group_id,"bcg_is_enabled");
    return apply_filters("bcg_is_enabled",$is_enabled,$group_id);
}
//call me business function
function bcg_get_categories($group_id){
    $cats=groups_get_groupmeta($group_id,'group_blog_cats');
    return maybe_unserialize($cats);
}
//update table
function bcg_update_categories($group_id,$cats){
    $success = false;
    //groups_update_groupmeta returns false if the old value matches the new value, so we'll need to check for that case
    //groups_get_groupmeta sometimes unserializes the data, but not always. No idea why.
    $old_setting = maybe_unserialize( groups_get_groupmeta( $group_id, "group_blog_cats" ) );
    $serialized_cats = maybe_serialize($cats);

    if ( empty($serialized_cats) && groups_delete_groupmeta( $group_id, "group_blog_cats" ) ) {
            $success = true;
    } else if ( $old_setting == $cats ) {
            // No need to resave settings if they're the same
            $success = true;
    } else if ( groups_update_groupmeta( $group_id, "group_blog_cats", $serialized_cats ) ) {
            $success = true;
    }
        
    return $success;
}

function bcg_update_groupmeta($group_id){
    $success = false;

    $input = array(
        'bcg_is_enabled',
        'bcg_tab_label',
        'bcg_level_to_post',
        'bcg_slug'       
        );

    foreach( $input as $field ) {
        //groups_update_groupmeta returns false if the old value matches the new value, so we'll need to check for that case
        $old_setting = groups_get_groupmeta( $group_id, $field );
        $new_setting = ( isset( $_POST[$field] ) ) ? $_POST[$field] : '' ;
        
        if ( empty($new_setting) && groups_delete_groupmeta( $group_id, $field ) ) {
            $success = true;
        } elseif ( $old_setting == $new_setting ) {
            // No need to resave settings if they're the same
            $success = true;
        } elseif ( groups_update_groupmeta( $group_id, $field, $new_setting ) ) {
            $success = true;
        }
        
    }

    return $success;
}

//get the appropriate query for various screens
function bcg_get_query(){
    global $bp;
   $cats=bcg_get_categories($bp->groups->current_group->id);

   if(!empty($cats))
        $cats_list=join(",",$cats);
   else return "name=-1";//we know it will not find anything
 if(bcg_is_single_post()){
        $slug=$bp->action_variables[0];
        return "name=".$slug."&cat=".$cats_list;
 }
 $paged=(get_query_var('paged')) ? get_query_var('paged') : 1;
if(bcg_is_category ()){
    $query="cat=".$bp->action_variables[1];
}//only posts from current category
else
    $query= "cat=".$cats_list;
return apply_filters("bcg_get_query",$query."&paged=".$paged);
}

function bcg_get_tab_label() {
    $label = groups_get_groupmeta( bp_get_current_group_id(), 'bcg_tab_label' );
    if ( empty($label) ) {
        $label = __('Blog Categories','bcg');
    }
    return apply_filters('bcg_get_tab_label', $label);
}

function bcg_get_slug() {
    $slug = groups_get_groupmeta( bp_get_current_group_id(), 'bcg_slug' );
    if ( empty($slug) ) {
        $slug = 'blog';
    }
    return apply_filters('bcg_get_slug', $slug);
}