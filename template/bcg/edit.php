<?php
/**
 * Blog Categories for group, post create/edit template.
 */
?>
<?php if ( function_exists( 'bp_get_simple_blog_post_form' ) ) : ?>
	<?php
	$form = bp_get_simple_blog_post_form( 'bcg_form' );

	$form->show();
	?>

<?php else : ?>
	<?php _e( 'Please Install <a href="https://buddydev.com/plugins/bp-simple-front-end-post/"> BP Simple Front End Post Plugin to make the editing functionality work.', 'blog-categories-for-groups' );?>
<?php endif; ?>
