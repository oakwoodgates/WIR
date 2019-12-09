<?php

/**
 * Get an option value by name
 *
 * @param $option_name
 *
 * @return string
 */
function wir_get_option( $option_name ) {

	$settings = wir_get_options();

	if ( isset( $settings[ $option_name ] ) ) {
		return $settings[ $option_name ];
	}

	return '';

}

/**
 * Get all options for the WIR
 * @return mixed
 */
function wir_get_options() {
	$default = array(

		'post_type'				=> 'post',
		'post_status'			=> 'publish',
	//	'comment_status'		=> 'open',
	//	'show_comment_option'	=> 1,
		'custom_field_title'	=> '',
		'enable_taxonomy'		=> 1,
		'allowed_taxonomies'	=> 1,
		'enable_category'		=> 1,
		'enable_tags'			=> 1,
		'show_posts_on_profile' => 0,
		'limit_no_of_posts'		=> 0,
		'max_allowed_posts'		=> 20,
		'publish_cap'			=> 'read',
		'allow_unpublishing'	=> 1,//subscriber //see https://codex.wordpress.org/Roles_and_Capabilities
		'post_cap'				=> 'read',
		'allow_edit'			=> 1,
		'allow_delete'			=> 1,
		'allow_upload'			=> 1,
		//'enabled_tags'			=> 1,
		'taxonomies'		=> array( 'category' ),
		'allow_upload'		=> false,
		'max_upload_count'	=> 2,
		'post_update_redirect'	=> 'archive'
	);

	return bp_get_option( 'wir-settings', $default );
}

/**
 * Are we dealing with blog categories pages?
 * @return boolean
 */
function wir_is_component () {
	
	$bp = buddypress();

	if ( bp_is_current_component( $bp->groups->slug ) && bp_is_current_action( WIR_SLUG ) ) {
		return true;
	}

	return false;
}
/**
 * Are we looking at the blog categories landing page
 * 
 * @return boolean
 */
function wir_is_home() {
	
	$bp = buddypress();

	if ( wir_is_component() && empty( $bp->action_variables[0] ) ) {
		return true;
	}
	
	return false;
	
}
/**
 * Is it single post?
 * 
 * @return boolean
 */
function wir_is_single_post() {
	$bp = buddypress();

	if ( wir_is_component() && ! empty( $bp->action_variables[0] ) && ! in_array( $bp->action_variables[0],  array_merge( array( 'create', 'page', 'edit') , wir_get_taxonomies() ) )  ) {
		return true;
	}
	return false;
}
/**
 * Is it post create csreen
 * 
 * @return boolean
 */

function wir_is_post_create() {
	$bp = buddypress();

	if ( wir_is_component() && ! empty( $bp->action_variables[0] ) && $bp->action_variables[0] == 'create' ) {
		return true;
	}
	
	return false;
}

/**
 * Is it bulk create csreen
 * 
 * @return boolean
 */

function wir_is_bulk_create() {
	$bp = buddypress();

	if ( wir_is_component() && ! empty( $bp->action_variables[0] ) && $bp->action_variables[0] == 'bulk' ) {
		return true;
	}
	
	return false;
}

/**
 * Check if we are on the single term screen
 *
 * @return boolean
 */
function wir_is_term() {
	$bp = buddypress();

	if ( wir_is_component() && ! empty( $bp->action_variables[1] ) && in_array( $bp->action_variables[0], wir_get_taxonomies() ) ) {
		return true;
	}

	return false;
}
/**
 * Is it single category view
 *
 * for back compatibility we are keeping this function
 *
 * @return boolean
 */
function wir_is_category () {

	return wir_is_term();
}

/**
 * Get associated post type
 *
 * @return string
 */
function wir_get_post_type() {

	$post_type  = 'location';
	return apply_filters( 'wir_get_post_type', $post_type );
}

/**
 * Get all allowed taxonomies names as array
 *
 * @return mixed|void
 */
function wir_get_taxonomies() {

	$taxonomy = 'list';
	return apply_filters( 'wir_get_taxonomies', (array) $taxonomy );
}


//todo remove
function wir_get_all_terms() {

	$taxonomy =   wir_get_taxonomies();
	$cats = get_terms( $taxonomy, array( 'fields' => 'all', 'get' => 'all' ) );
	return $cats;
}

function wir_get_associated_terms( $group_id ) {

}
function wir_get_terms( $group_id, $taxonomy = 'list' ) {

}

//call me business function
function wir_get_categories( $group_id ) {
	
	$cats = groups_get_groupmeta( $group_id, 'group_blog_cats' );
	return maybe_unserialize( $cats );
}

/**
 * Get a post by slug name
 *
 * @param $slug
 *
 * @return WP_Post
 */
function wir_get_post_by_slug( $slug ) {
	global $wpdb;
	
	$query = "SELECT * FROM $wpdb->posts WHERE post_name = %s AND post_type = %s LIMIT 1";
	$post = $wpdb->get_row( $wpdb->prepare( $query, $slug, wir_get_post_type() ) );
	
	return $post;
}


function wir_get_group_post_status( $user_id ) {

	$authority_to_publish   = wir_get_option( 'publish_cap' );
	$group_id               = bp_get_current_group_id();
	$post_status            = 'draft';

	if ( wir_can_user_publish_post( get_current_user_id() ) ) {
		$post_status = 'publish';
	}

	return $post_status;
}

/**
 * Check if  post is published
 *
 * @param $post_id
 *
 * @return bool
 */
function wir_is_post_published( $post_id ) {

	return get_post_field( 'post_status', $post_id ) == 'publish';
}

function wir_can_user_publish_post( $user_id ) {

	$can_publish = false;

	if ( ! $user_id ) {
		return $can_publish;
	}

	$group_id = bp_get_current_group_id();

	if ( is_super_admin() || groups_is_user_admin( $user_id, $group_id ) ) {
		$can_publish = true;
	} 

	return $can_publish;

}

/**
 * Get WIR landing page url
 *
 * @param type $group_id
 * @return type
 */
function wir_get_home_url ( $group_id = null ) {

	if ( ! empty( $group_id ) ) {
		$group = new BP_Groups_Group( $group_id );
	} else {
		$group = groups_get_current_group();
	}

	return apply_filters( 'wir_home_url', bp_get_group_permalink( $group ) . WIR_SLUG );
}
/**
 * @param bool $post_id
 * @param string $label_ac
 * @param string $label_de
 *
 * @return string|void
 */
function wir_get_post_publish_unpublish_link( $post_id = false, $label_ac = 'Publish', $label_de = 'Unpublish' ) {

	if ( ! $post_id ) {
		return;
	}

	if ( ! wir_user_can_publish( get_current_user_id() ) ) {
		return ;
	}

	$post = get_post( $post_id );
	$user_id = get_current_user_id();
	$url = '';

	if ( ! ( is_super_admin() || $post->post_author == $user_id || groups_is_user_admin( $user_id, bp_get_current_group_id() ) ) ) {
		return;
	}

	//check if post is published
	$url = wir_get_post_publish_unpublish_url( $post_id );

	if( ! wir_get_option( 'allow_unpublishing' ) ){
		return;
	}

	if ( wir_is_post_published( $post_id ) ) {
		$link = "<a href='{$url}'>{$label_de}</a>";
	} else {
		$link = "<a href='{$url}'>{$label_ac}</a>";
	}

	return $link;

}

function wir_get_post_publish_unpublish_url( $post_id = false ) {

	if ( ! $post_id ) {
		return;
	}

	$post = get_post( $post_id );
	$url = '';

	if ( wir_user_can_publish( get_current_user_id(), $post_id ) ) {
		//check if post is published
		$url = wir_get_home_url();

		if ( wir_is_post_published( $post_id ) ) {
			$url = $url . '/unpublish/' . $post_id . '/';
		} else {
			$url = $url . '/publish/' . $post_id . '/';
		}
	}

	return $url;

}


function wir_get_edit_url( $post_id = false ) {

	$user_id = get_current_user_id();
	$group_id = bp_get_current_group_id();

	if ( ! $user_id && ! $group_id ) {
		return;
	}

	if ( empty( $post_id ) ) {

		$post_id = get_the_ID();

	}
	//cheeck if current user can edit the post
	$post = get_post( $post_id );
	//if the author of the post is same as the loggedin user or the logged in user is admin

	if ( $post->post_type != wir_get_post_type() ) {

		return false;
	}


	if ( $post->post_author != $user_id && ! is_super_admin() && ! groups_is_user_admin( $user_id, $group_id ) ) {
		return ;
	}

	$action_name = 'edit';

	if ( current_user_can( 'activate_plugins' ) ) {
		return get_edit_post_link ( $post );
	}

	$url = wir_get_home_url();
	//if we are here, we can allow user to edit the post
	return $url . "/{$action_name}/" . $post->ID . '/';
}


function wir_get_edit_link( $id = 0, $label = 'Edit' ) {


	if ( ! is_super_admin() && ! wir_get_option( 'allow_edit' ) ) {
		return '';
	}

	$url = wir_get_edit_url( $id );

	if ( ! $url ) {
		return '';
	}

	return "<a href='{$url}'>{$label}</a>";
}

/**
 * Get delete link
 * @param int $id
 * @param string $label
 *
 * @return string|void
 */
function wir_get_delete_link( $id = 0, $label = 'Delete' ) {

	$group_id = bp_get_current_group_id();

	if ( ! wir_user_can_delete( $id,  get_current_user_id(), $group_id ) ) {
		return;
	}

	$bp             = buddypress();
	$post           = get_post( $id );
	$action_name    = 'delete';
	$url            = wir_get_home_url();
	$url            = $url . "/{$action_name}/" . $post->ID . '/';

	return "<a href='{$url}' class='confirm' >{$label}</a>";

}

function wir_idk() {

}
