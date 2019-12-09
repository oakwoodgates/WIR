<?php

/**
 * Load a template
 * @param type $template
 */
function wir_load_template( $template ) {
	
    if ( file_exists( STYLESHEETPATH . '/wir/' . $template ) ) {
   		include STYLESHEETPATH . '/wir/' . $template ;
	} elseif ( file_exists( TEMPLATEPATH . '/wir/' . $template ) ) {
		include TEMPLATEPATH . '/wir/' . $template ;
	} else {
        include WIR_PLUGIN_DIR . 'templates/' . $template;
	}	
}

//get the appropriate query for various screens
function wir_get_query (){
	
	$bp = buddypress();
	
	$qs = array(
		'post_type'		=> wir_get_post_type(),
		'post_status'	=> 'publish',
		'posts_per_page'	=> 20,
		'post__not_in' => WIR_Favorite_Locations::get_user_favorite_locations_for_group( get_current_user_id(), $bp->groups->current_group->id ),
		'tax_query' => array(
			array(
				'taxonomy' => 'list',
				'field'    => 'slug',
				'terms'    => 'list_' . $bp->groups->current_group->id,
			),
		),
	);

	if( is_super_admin() ||  groups_is_user_admin( get_current_user_id(), $bp->groups->current_group->id ) ) {
		$qs['post_status'] = 'any';
	}


	if ( wir_is_single_post() ) {
		$slug = $bp->action_variables[0];
		
		$qs['name'] = $slug;
              
	} 
        
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	
	$qs ['paged'] = $paged;

	return apply_filters( "wir_get_query", $qs );
}

function wir_get_favorite_locations_query() {
	$bp = buddypress();
	
	if ( empty( WIR_Favorite_Locations::get_user_favorite_locations_for_group( get_current_user_id(), $bp->groups->current_group->id ) ) )
		return;

	$qs = array(
		'post_type'		=> wir_get_post_type(),
		'post_status'	=> 'publish',
		'posts_per_page'	=> -1,
		'post__in' => WIR_Favorite_Locations::get_user_favorite_locations_for_group( get_current_user_id(), $bp->groups->current_group->id ),
	);

	if( is_super_admin() ||  groups_is_user_admin( get_current_user_id(), $bp->groups->current_group->id ) ) {
		$qs['post_status'] = 'any';
	}

	return $qs;
}
