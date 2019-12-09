<?php
/**
 * Update and save group preference
 */
add_action( 'groups_group_settings_edited', 'wir_save_group_prefs' );
add_action( 'groups_create_group', 'wir_save_group_prefs' );
add_action( 'groups_update_group', 'wir_save_group_prefs' );

function wir_save_group_prefs( $group_id ) {
//	$disable = isset( $_POST['group-disable-wir'] ) ? 1: 0;
//	groups_update_groupmeta( $group_id, 'wir_is_active', $disable ); //save preference
}

/* put a settings for allowing disallowing the wir */
add_action( 'bp_before_group_settings_admin', 'wir_group_disable_form' );
add_action( 'bp_before_group_settings_creation_step', 'wir_group_disable_form' );
function wir_group_disable_form () {

}

//comment posting a lil bit better
add_action( 'comment_form', 'wir_fix_comment_form' );

function wir_fix_comment_form ( $post_id ) {
	
	if ( ! wir_is_single_post() ) {
		return;
	}
	
	$post = get_post( $post_id );
	$permalink = wir_get_post_permalink( $post );
	?>
	<input type='hidden' name='redirect_to' value="<?php echo esc_url( $permalink ); ?>" />
	<?php
}
//fix to disable/reenable buddypress comment open/close filter
function wir_disable_bp_comment_filter() {
    
    if( has_filter( 'comments_open', 'bp_comments_open' ) ) {
        remove_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}	
}
add_action( 'bp_before_group_blog_post_content', 'wir_disable_bp_comment_filter' );

function wir_enable_bp_comment_filter() {
    
    if( function_exists( 'bp_comments_open' ) ) {
		add_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}	
}

add_action( 'bp_after_group_blog_content', 'wir_enable_bp_comment_filter' );

/* fixing permalinks for posts/categories inside the wir loop */


//fix post permalink, should we ?
if ( wir_get_post_type() == 'post' ) {
	add_filter( 'post_link', 'wir_fix_permalink', 10, 3 );
} else {
	add_filter( 'post_type_link', 'wir_fix_permalink', 10, 3 );
}

function wir_fix_permalink( $post_link, $id, $leavename ) {
	
	if ( ! wir_is_component() || ! in_wir_loop() ) {
		return $post_link;
	}

	$post_link = wir_get_post_permalink( get_post( $id ) );
	return $post_link;
}

//on Blog category pages fix the category link to point to internal, may cause troubles in some case
add_filter( 'category_link', 'wir_fix_category_permalink', 10, 2 );

function wir_fix_category_permalink ( $catlink, $category_id ) {
	
	if ( ! wir_is_component() || ! in_wir_loop() ) {
		return $catlink;
	}

	$term = get_term($category_id);
	$allowed_taxonomies = wir_get_taxonomies();

	if ( ! in_array( $term->taxonomy, $allowed_taxonomies ) ) {
		return $catlink;
	}
	//it is our taxonomy


	$permalink = trailingslashit( wir_get_home_url() );
	//$cat       =  get_category( $category_id );
	//think about the cat permalink, do we need it or not?

	///we need to work on this
	return $permalink . $term->taxonomy . '/' . $category_id; //no need for category_name
}

add_filter( 'bp_activity_get_activity_id', 'wir_update_group_post_activity', 0, 2);
function wir_update_group_post_activity( $id, $args ) {


	if ( $args['component'] == 'blogs' && $args['type'] == 'new_blog_post' ) {

		unset( $args['item_id']);
		//now set component to groups
		$args['component'] = buddypress()->groups->id;

		$new_id = bp_activity_get_activity_id( $args );

		if( $new_id ) {
			$id = $new_id;
		}


	}

	return $id;
}

add_action( 'bp_activity_post_type_unpublished', 'wir_delete_group_post_activity', 0 , 3 );
function wir_delete_group_post_activity( $delete_activity_args, $post, $deleted ) {

	if( $delete_activity_args['component'] == 'blogs' && $delete_activity_args['type'] == "new_blog_post") {

		unset( $delete_activity_args['item_id'] );
		$delete_activity_args['component'] = buddypress()->groups->id;
		$deleted = bp_activity_delete( $delete_activity_args );

	}
	return $deleted;

}

function wir_format_activity_action( $action, $activity  ) {

	$user_link = bp_core_get_userlink( $activity->user_id );

	//$user_name = bp_core_get_user_displayname( $activity->user_id );

	if ( isset( $activity->post_url ) ) {
		$post_url = $activity->post_url;
	}

	$post_title = bp_activity_get_meta( $activity->id, 'post_title' );

	if ( empty( $post_title ) ) {
		// Defaults to no title.
		$post_title = esc_html__( '(no title)', 'wir' );

	}else {
		$post_title = esc_html__( $post_title, 'wir' );
	}

	$group = groups_get_group( array( 'group_id' => $activity->item_id ) );
	$group_permalink = bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug;

	$post_link  = '<a href="' . esc_url( $post_url ) . '">' . $post_title . '</a>';
	$group_link =  '<a href="' . esc_url( $group_permalink ) . '">' . esc_html( $group->name ) . '</a>';

	$action = sprintf( __( '%1$s wrote a new post in %2$s, %3$s', 'wir' ), $user_link, $group_link, $post_link );

	return $action;
}

add_action( 'groups_register_activity_actions', 'wir_register_group_activity_action' );
function wir_register_group_activity_action() {

	$bp = buddypress();
	bp_activity_set_action(
		$bp->groups->id,
		'new_blog_post',
		__( 'Group details edited', 'wir' ),
		'wir_format_activity_action',
		__( 'Group Updates', 'wir' ),
		array( 'activity', 'group', 'member', 'member_groups' )
	);

}

add_action( 'bp_group_header_actions', 'bp_group_join_button_offline' );
function bp_group_join_button_offline() {
	if ( ! is_user_logged_in() )
		echo bp_get_button( WIR_Core::login_button( 'Join List' ) );
}

function wir_add_slider_to_member() {
	echo wir_list_slider_func();
}
add_action( 'bp_member_header_actions', 'wir_add_slider_to_member', 10 );


function wir_homepage_redirect() {
	if ( is_user_logged_in() && is_front_page() ) {
		wp_redirect( bp_loggedin_user_domain() );
		exit();
	}

	if ( is_user_logged_in() && ( is_page( 'login' ) || is_page( 'your-profile' ) ) ) {
		wp_redirect( bp_loggedin_user_domain() );
		exit();
	}
}
add_action( 'template_redirect', 'wir_homepage_redirect', 99 );

function wir_remove_admin_bar() {
	if( !is_super_admin() ) 
		add_filter( 'show_admin_bar', '__return_false' );
}
add_action('wp', 'wir_remove_admin_bar');