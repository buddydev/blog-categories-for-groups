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
	
	$terms = bcg_get_categories( $bp->groups->current_group->id );
	
	$qs = array(
		'post_type'		=> bcg_get_post_type(),
		'post_status'	=> 'publish'
	);

	if( is_super_admin() ||  groups_is_user_admin( get_current_user_id(), $bp->groups->current_group->id ) ) {
		$qs['post_status'] = 'any';
	}
	if ( empty( $terms ) ) {
		$qs ['name'] = -1; //we know it will not find anything
	}

	if ( bcg_is_single_post() ) {
		$slug = $bp->action_variables[0];
		
		$qs['name'] = $slug;
              
	} elseif( bcg_is_category() ) {
		
		$qs['tax_query'] = bcg_build_tax_query( bp_action_variable(1), (array) bp_action_variable(0) );
	} else {
		
		$qs['tax_query'] = bcg_build_tax_query( $terms, bcg_get_taxonomies()  );
	}
        
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	
	$qs ['paged'] = $paged;

	return apply_filters( "bcg_get_query", $qs );
}

function bcg_build_tax_query( $terms, $taxonomies = array() ) {
  
    
    $query = array();
    
    foreach( $taxonomies as $tax  ) {
        
        $query[] = array(
				'taxonomy'	=> $tax,
				'terms'		=> $terms,
				'field'		=> 'id',
				'operator'	=> 'IN'
			);
        
    }
    
    if( count( $query ) > 1 ) {
        $query['relation'] = 'OR';
    }
    
    return $query;
}
