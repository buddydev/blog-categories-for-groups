<?php
/*
 * Plugin Name: Blog Categories for Groups
 * Author: Brajesh Singh
 * Plugin URI:http://buddydev.com/plugins/blog-categories-for-groups/
 * Author URI:http://buddydev.com/members/sbrajesh
 * Description: Allow Group admins;/mods to associate blog categories with groups
 * Version: 1.0.3
 * Tested with wp 3.1+buddypress 1.2.8
 * License: GPL
 * Date: March 18, 2011
 */

if(!defined('BCG_SLUG'))
    define('BCG_SLUG',"blog");

$bcg_dir =str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
define("BCG_DIR_NAME",$bcg_dir);//the directory name of blog-category for groups
define("BCG_PLUGIN_DIR",WP_PLUGIN_DIR."/".BCG_DIR_NAME);
define("BCG_PLUGIN_URL",WP_PLUGIN_URL."/".BCG_DIR_NAME);


define("BCG_PLUGIN_NAME","bcg");//for localization
function bcg_load_extension(){
	include_once(BCG_PLUGIN_DIR."bcg-admin.php");
	include_once(BCG_PLUGIN_DIR."template-tags.php");

}
add_action("bp_init","bcg_load_extension");


//load javascript for comment reply

add_action("wp_print_scripts","bcg_enqueue_script");

function bcg_enqueue_script(){
if(bcg_is_single_post ())
    wp_enqueue_script ("comment-reply");

   }


//allow translations
function bcg_load_textdomain() {
        $locale = apply_filters( 'bcg_load_textdomain_get_locale', get_locale() );
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%s/languages/%s-%s.mo',BCG_PLUGIN_DIR, BCG_PLUGIN_NAME, $locale );
		$mofile = apply_filters( 'bcg_load_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( BCG_PLUGIN_NAME, $mofile );
		}
	}
}
add_action ( 'bp_init', 'bcg_load_textdomain', 2 );


//setup nav
function bcg_setup_nav($current_user_access){
    global $bp;
  
  if(!bp_is_group())
      return;
    if(bcg_is_disabled($bp->groups->current_group->id))
          return;
    $group_link = bp_get_group_permalink($bp->groups->current_group);
    bp_core_new_subnav_item( array( 'name' => __( 'Blog', 'bcg' ), 'slug' => BCG_SLUG, 'parent_url' => $group_link, 'parent_slug' => $bp->groups->current_group->slug, 'screen_function' => 'bcg_screen_group_blog', 'position' => 10,'user_has_access'=>$current_user_access, 'item_css_id' => 'blog' ) );

}
add_action("groups_setup_nav","bcg_setup_nav");


//load the blog home page for group
function bcg_screen_group_blog(){
    //load template
    bp_core_load_template( apply_filters( 'groups_template_group_blog', 'bcg/home' ) );
}

//for single post screen
function bcg_screen_group_blog_single_post(){
   global $bp;
    if(!bp_is_group())
      return;
   if(bcg_is_disabled($bp->groups->current_group->id))
           return;
   //if the group is private/hidden and user is not member, return
   if(($bp->groups->current_group->status=='private'||$bp->groups->current_group->status=='hidden')&&(!is_user_logged_in()||!groups_is_user_member( $bp->loggedin_user->id, $bp->groups->current_group->id )))
   return;//avoid prioivacy troubles

   if ( $bp->current_component == $bp->groups->slug && $bp->current_action==BCG_SLUG &&!empty($bp->action_variables[0]) ){

       $wpq=new WP_Query(bcg_get_query());
        if($wpq->have_posts()){
            //load template
         bp_core_load_template( apply_filters( 'groups_template_group_blog_single_post', 'bcg/home' ) );
        }
    else
        bp_core_add_message (__("Sorry, the post does not exists!","bcg"),"error");

   }
}
add_action("wp","bcg_screen_group_blog_single_post",5);


//form for showing category lists
function bcg_admin_form(){
    $group_id=bp_get_group_id();

   
    $selected_cats=bcg_get_categories($group_id);
    echo "<p>".__("Check a category to assopciate the posts in this category with this group.","bcg")."</p>";

    $cat_ids=get_all_category_ids();
if(is_array($cat_ids)){////it is sure but do not take risk
 foreach($cat_ids as $cat_id){//show the form
    	$checked=0;
	if(!empty($selected_cats)&&in_array($cat_id,$selected_cats))
			$checked=true;
	?>
	<label  style="padding:5px;display:block;float:left;">
	<input type="checkbox" name="blog_cats[]" id="<?php $opt_id;?>" value="<?php echo $cat_id;?>" <?php if($checked) echo "checked='checked'" ;?>/>
	<?php echo get_cat_name($cat_id);?>
	</label>

<?php
   }
}
  else{
      ?>

    <div class="error">
        <p><?php _e("Please create the categories first to attach them to a group.","bcg");?></p>
    </div>
<?php
     }
?>
    <div class="clear"></div>

<?php
}

//call me business function
function bcg_get_categories($group_id){
    $cats=groups_get_groupmeta($group_id,"group_blog_cats");
    return maybe_unserialize($cats);
}
//update table
function bcg_update_categories($group_id,$cats){
    $cats=maybe_serialize($cats);
    return  groups_update_groupmeta($group_id, "group_blog_cats", $cats);
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
//is single post screen



function bcg_is_disabled($group_id){
    if(empty($group_id))
        return false; //if grou id is empty, it is active
    $is_disabled=groups_get_groupmeta($group_id,"bcg_is_active");
    return apply_filters("bcg_is_disabled",intval($is_disabled),$group_id);
}



//post form if one quick pot is installed
function bcg_get_post_form($group_id){
    global $bp;
    $cat_selected=bcg_get_categories($group_id);//selected cats
    if(empty($cat_selected)){
             _e("This group has no associated categories. To post to Group blog, you need to associate some categoris to it.","bcg");
            return;
        }

    $all_cats=get_all_category_ids();
    $cats=array_diff($all_cats,$cat_selected);
    $cat_list=join(",",$cats);

    //for form
    $url=bp_get_group_permalink(new BP_Groups_Group($group_id)).BCG_SLUG."/create/";
    do_shortcode("[oqp_form taxonomies='category' taxonomies{category}{exclude}=".$cat_list." form_id=10101 form_url=$url ]");
}


//sub menu
function bcg_get_options_menu(){?>
                <li <?php if(bcg_is_home ()):?> class="current"<?php endif;?>><a href="<?php echo bcg_get_home_url();?>"><?php _e("Posts","bcg");?></a></li>
                <?php if(bcg_current_user_can_post()):?>
                <li <?php if(bcg_is_post_create()):?> class="current"<?php endif;?>><a href="<?php echo bcg_get_home_url();?>/create"><?php _e("Create New Post","bcg");?></a></li>
  <?php endif;?>
 <?php
}

function  bcg_get_home_url($group_id=null){
    global $bp;

if(!empty($group_id))
    $group=new BP_Groups_Group ($group_id);
else
    $group=$bp->groups->current_group;
return apply_filters("bcg_home_url",  bp_get_group_permalink($group).BCG_SLUG);
}

function bcg_current_user_can_post(){
global $bp;
$user_id=$bp->loggedin_user->id;
$group_id=$bp->groups->current_group->id;
    $can_post=is_user_logged_in()&&(groups_is_user_admin($user_id, $group_id)||groups_is_user_mod($user_id, $group_id));
  return apply_filters("bcg_current_user_can_post",$can_post,$group_id,$user_id);
}


//comment posting a lil bit better
add_action('comment_form',"bcg_fix_comment_form" );

function bcg_fix_comment_form($post_id){
    if(!bcg_is_single_post())
        return;
    $post=get_post($post_id);
    $permalink=  bcg_get_post_permalink($post);
 ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_url($permalink);?>" />

           <?php
}



//for the admin part
/*add_action("bp_init","bcg_register_extension",12);
function bcg_register_extension(){
 global $bp;
    if(bcg_is_disabled($bp->groups->current_group->id)||!bp_is_active('groups'))
          return;

}
*/

/*put a settings for allowing disallowing the bcg*/
add_action("bp_before_group_settings_admin","bcg_group_disable_form");
add_action("bp_before_group_settings_creation_step","bcg_group_disable_form");
//check if the group yt is enabled
function bcg_group_disable_form(){?>

    <div class="checkbox">
	<label><input type="checkbox" name="group-disable-bcg" id="group-disable-bcg" value="1" <?php if(bcg_is_disabled_for_group()):?> checked="checked"<?php endif;?>/> <?php _e( 'Disable Blog Categories', 'bcg' ) ?></label>
    </div>
<?php

}

add_action("groups_group_settings_edited","bcg_save_group_prefs");
add_action("groups_create_group","bcg_save_group_prefs");
add_action("groups_update_group","bcg_save_group_prefs");

function bcg_save_group_prefs($group_id){
      $disable=$_POST["group-disable-bcg"];
      groups_update_groupmeta($group_id, "bcg_is_active", $disable);//save preference
}

function bcg_is_disabled_for_group(){
    global $bp;
    if (bp_is_group_create())
        $group_id=$_COOKIE['bp_new_group_id'];
   else if(bp_is_group ())
        $group_id=$bp->groups->current_group->id;

    return apply_filters("bcg_is_disabled_for_group",bcg_is_disabled($group_id));
}
?>