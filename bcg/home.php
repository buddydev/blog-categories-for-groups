<?php get_header() ?>

	<div id="content">
		<div class="padder">
			<?php if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group(); ?>

			<?php do_action( 'bp_before_group_home_content' ) ?>

			<div id="item-header">
				<?php locate_template( array( 'groups/single/group-header.php' ), true ) ?>
			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav">
					<ul>
						<?php bp_get_options_nav() ?>
						<?php do_action( 'bp_group_options_nav' ) ?>
					</ul>
				</div>
			</div><!-- #item-nav -->
			<div id="item-body">
			<div id="subnav" class="item-list-tabs no-ajax">
			<ul>
                <?php bcg_get_options_menu();?>
			</ul>
			</div>
			<?php
                if( bcg_is_single_post() )
                    bcg_load_template( 'bcg/single-post.php' );
                else if( bcg_is_post_create() )
                    bcg_load_template( 'bcg/create.php' );
                else
                    bcg_load_template( 'bcg/blog.php');
			?>
			</div>
			
			
			<?php do_action( 'bp_after_group_blog_home_content' ) ?>

			<?php endwhile; endif; ?>
		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar.php' ), true ) ?>

<?php get_footer() ?>