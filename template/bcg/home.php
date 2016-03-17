<div id="subnav" class="item-list-tabs no-ajax">
	<ul>
		<?php bcg_get_options_menu();?>
	</ul>
</div>
	<?php
		if ( bcg_is_single_post() ) {
			bcg_load_template( 'bcg/single-post.php' );
		} elseif ( bcg_is_post_create() ) {
			bcg_load_template( 'bcg/create.php' );
		} else {
			bcg_load_template( 'bcg/blog.php');
		}
