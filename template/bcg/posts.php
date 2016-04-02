	<?php
	/*
	This page is used for group blog home page/categories archives*/
	?>
	<?php $q = new WP_Query( bcg_get_query() );?>
	<?php if ($q->have_posts() ) : ?>
	<?php do_action( 'bp_before_group_blog_content' ) ?>
	<div class="pagination no-ajax">
		<div id="posts-count" class="pag-count">
			<?php bcg_posts_pagination_count( $q ) ?>
		</div>

		<div id="posts-pagination" class="pagination-links">
			<?php bcg_pagination( $q ) ?>
		</div>
	</div>

	<?php do_action( 'bp_before_group_blog_list' ) ?>
<?php
	global $post;
	bcg_loop_start();//please do not remove it
	while( $q->have_posts() ):$q->the_post();
 ?>
	<div class="post" id="post-<?php the_ID(); ?>">
        <div class="author-box">
            <?php echo get_avatar( get_the_author_meta( 'user_email' ), '50' ); ?>
            <p><?php printf( __( 'by %s', 'blog-categories-for-groups' ), bp_core_get_userlink( $post->post_author ) ) ?></p>
        </div>
        <div class="post-content">
	        <?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( get_the_ID() ) ):?>

		        <div class="post-featured-image">
			        <?php  the_post_thumbnail();?>
		        </div>

	        <?php endif;?>
            <h2 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'blog-categories-for-groups' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

            <p class="date"><?php the_time() ?> <em><?php _e( 'in', 'blog-categories-for-groups' ) ?> <?php the_category(', ') ?> <?php printf( __( 'by %s', 'blog-categories-for-groups' ), bp_core_get_userlink( $post->post_author ) ) ?></em></p>

            <div class="entry">
                <?php the_excerpt( ); ?>
            </div>
	        <div class="post-actions">
		        <?php $post_id = get_the_ID(); ?>
		        <?php echo bcg_get_post_publish_unpublish_link( $post_id );?>
		        <?php echo bcg_get_edit_link( $post_id ); ?>
		        <?php echo bcg_get_delete_link( $post_id ); ?>
	        </div>
	        <p class="postmetadata"><span class="tags"><?php the_tags( __( 'Tags: ', 'blog-categories-for-groups' ), ', ', '<br />'); ?></span> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', 'blog-categories-for-groups' ), __( '1 Comment &#187;', 'blog-categories-for-groups' ), __( '% Comments &#187;', 'blog-categories-for-groups' ) ); ?></span></p>
        </div>
    </div>
	<?php endwhile;?>
	<?php 
        do_action( 'bp_after_group_blog_content' ) ;
        bcg_loop_end();//please do not remove it
	?>
	<div class="pagination no-ajax">
		<div id="posts-count" class="pag-count">
			<?php bcg_posts_pagination_count( $q ) ?>
		</div>

		<div id="posts-pagination" class="pagination-links">
			<?php bcg_pagination( $q ) ?>
		</div>
	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'This group has no Blog posts.', 'blog-categories-for-groups' ); ?></p>
	</div>

<?php endif; ?>
