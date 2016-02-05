<?php

/**
 * Load a template
 * @param type $template
 */
function bcg_load_template( $template ) {

    if ( file_exists( STYLESHEETPATH . '/bcg/' . $template ) ) {
   		include STYLESHEETPATH . '/bcg/' . $template ;
	} elseif ( file_exists( TEMPLATEPATH . '/bcg/' . $template ) ) {
		include TEMPLATEPATH . '/bcg/' . $template ;
	} else {
        include BCG_PLUGIN_DIR . 'template/bcg/' . $template;
	}	
}

//get the appropriate query for various screens
function bcg_get_query (){
	
	$bp = buddypress();
	
	$cats = bcg_get_categories( $bp->groups->current_group->id );

	$qs = array(
		'post_type'		=> bcg_get_post_type(),
		'post_status'	=> 'publish'
	);
	
	if ( empty( $cats ) ) {
		$qs ['name'] = -1; //we know it will not find anything
	}

	if ( bcg_is_single_post() ) {
		$slug = $bp->action_variables[0];
		
		$qs['name'] = $slug;
		//tax query
		$qs['tax_query'] = array(
			array(
				'taxonomy'	=> bcg_get_taxonomy(),
				'terms'		=> $cats,
				'field'		=> 'id',
				'operator'	=> 'IN',
			)
		);
	}
	
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	
	if ( bcg_is_category() ) {
		
		$qs['tax_query'] = array(
			array(
				'taxonomy'	=> bcg_get_taxonomy(),
				'terms'		=> (int) bp_action_variable( 1 ),
				'field'		=> 'id',
				'operator'	=> 'IN',
			)
		);
	} else {
		
		$qs['tax_query'] = array(
			array(
				'taxonomy'	=> bcg_get_taxonomy(),
				'terms'		=> $cats,
				'field'		=> 'id',
				'operator'	=> 'IN'
			)
		);
	}
	
	$qs ['paged'] = $paged;


	return apply_filters( "bcg_get_query", $qs );
}
