<?php
/*
 * Template Tags for Blog categories
 *
 */

//if inside the post loop
function in_wir_loop() {
	
	$bp = buddypress();
	
	return isset( $bp->wir ) ? $bp->wir->in_the_loop : false;
}

//use it to mark t5he start of wir post loop
function wir_loop_start() {
	$bp = buddypress();

	$bp->wir = new stdClass();
	$bp->wir->in_the_loop = true;
}

//use it to mark the end of wir loop
function wir_loop_end() {
	$bp = buddypress();

	$bp->wir->in_the_loop = false;
	wp_reset_postdata();
}

//get post permalink which leads to group blog single post page
function wir_get_post_permalink( $post ) {

	return bp_get_group_permalink( groups_get_current_group() ) . WIR_SLUG . '/' . $post->post_name;
}

/**
 * Generate Pagination Link for posts
 * @param type $q 
 */
function wir_pagination( $q ) {

	$posts_per_page = intval( get_query_var( 'posts_per_page' ) );
	$paged = intval( get_query_var( 'paged' ) );
	
	$numposts = $q->found_posts;
	$max_page = $q->max_num_pages;
	
	if ( empty( $paged ) || $paged == 0 ) {
		$paged = 1;
	}

	$pag_links = paginate_links( array(
		'base'		=> add_query_arg( array( 'paged' => '%#%', 'num' => $posts_per_page ) ),
		'format'	=> '',
		'total'		=> ceil( $numposts / $posts_per_page ),
		'current'	=> $paged,
		'prev_text'	=> '&larr;',
		'next_text'	=> '&rarr;',
		'mid_size'	=> 1
	) );
	
	echo $pag_links;
}

//viewing x of z posts
function wir_posts_pagination_count( $q ) {

	$posts_per_page = intval( get_query_var( 'posts_per_page' ) );
	$paged = intval( get_query_var( 'paged' ) );
	
	$numposts = $q->found_posts;
	$max_page = $q->max_num_pages;
	
	if ( empty( $paged ) || $paged == 0 ) {
		$paged = 1;
	}

	$start_num = intval( $posts_per_page * ( $paged - 1 ) ) + 1;
	$from_num = bp_core_number_format( $start_num );
	$to_num = bp_core_number_format( ( $start_num + ( $posts_per_page - 1 ) > $numposts ) ? $numposts : $start_num + ( $posts_per_page - 1 )  );
	
	$total = bp_core_number_format( $numposts );

	//$taxonomy = get_taxonomy( wir_get_taxonomies() );
	$post_type_object = get_post_type_object( wir_get_post_type() );

	printf( __( 'Viewing %1s %2$s to %3$s (of %4$s )', 'wir' ), $post_type_object->labels->name, $from_num, $to_num, $total ) . "&nbsp;";
	//$term = get_term_name ( $q->query_vars['cat'] );
	//if( wir_is_category() )
	// printf( __( "In the %s %s ","wir" ), $taxonomy->label, "<span class='wir-cat-name'>". $term_name ."</span>" );
	?>
	<span class="ajax-loader"></span><?php
}


//sub menu
function wir_get_options_menu() {
	?>
	<li <?php if ( wir_is_home() ): ?> class="current"<?php endif; ?>><a href="<?php echo wir_get_home_url(); ?>"><?php _e( 'Locations', 'wir' ); ?></a></li>
	<?php if ( wir_current_user_can_post() ): ?>
		<li <?php if ( wir_is_post_create() ): ?> class="current"<?php endif; ?>><a href="<?php echo wir_get_home_url(); ?>/create"><?php _e( 'Add Location', 'wir' ); ?></a></li>
		<li <?php if ( wir_is_bulk_create() ): ?> class="current"<?php endif; ?>><a href="<?php echo wir_get_home_url(); ?>/bulk"><?php _e( 'Upload List', 'wir' ); ?></a></li>
		<li><a href="<?php echo site_url(); ?>/lists/create/step/group-details/"><?php _e( 'Create New List', 'wir' ); ?></a></li>
	<?php endif; ?>
	<?php
}

//post form if one quick pot is installed
function wir_show_post_form( $group_id ) {
	
	$bp = buddypress();

	$cat_selected = 'list_' . $group_id; //selected cats
	
	if ( empty( $cat_selected ) ) {
		_e( 'Something went wrong.', 'wir' );
		return;
	}

	$all_cats = (array) wir_get_all_terms();
	$all_cats = wp_list_pluck( $all_cats, 'term_id' );
	
	$cats = array_diff( $all_cats, $cat_selected );

	//for form
	$url = bp_get_group_permalink( new BP_Groups_Group( $group_id ) ) . WIR_SLUG . '/create/';
	
	if ( function_exists( 'bp_get_simple_blog_post_form' ) ) {

		$form = bp_get_simple_blog_post_form( 'wir_form' );
		
		if ( $form ) {
			$form->show();
		}
	}

	do_action( 'wir_post_form', $cats, $url ); //pass the categories as array and the url of the current page
}
			

function wir_list_slider_func() {
	$group_ids =  groups_get_user_groups( 11 ); 
	$j = '';	
 	if ( ! empty( $group_ids["groups"] ) ) : ?>
		<div class="wir_list_slider_wrapper">
			<h3>Experience When in Roam With Some of Our Featured Lists</h3>
			<div class="wir_list_slider_class">
			<?php 
				foreach( $group_ids["groups"] as $data ) { 
					$group = groups_get_group( array( 'group_id' => $data) );	

					echo '<div><div><a class="slide_img_link" href="' . home_url( '/lists/') . $group->slug . '">' . bp_core_fetch_avatar( array( 'item_id' => $group->id, 'object' => 'group', 'type' => 'full' ) ) . '</a></div>';
					echo '<div><a class="slide_img_name" href="' . home_url( '/lists/') . $group->slug . '">' . $group->name . '</a></div></div>';
				//	echo bp_get_group_avatar( array( 'id' => $id ) );
				//	print_r($group);
				}
			?>
			</div>
		</div>
 	<?php endif;

}
add_shortcode( 'wir_list_slider', 'wir_list_slider_func' );
