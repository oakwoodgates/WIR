<?php

/**
 * Can the current user post to group blog
 * @global type $bp
 * @return type 
 */
function wir_current_user_can_post () {
	
	$user_id = bp_loggedin_user_id();
	$group_id = bp_get_current_group_id();
	$can_post = false;

	if ( is_user_logged_in() && ( groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id ) ) ) {
		$can_post = true;
	}

	return apply_filters( 'wir_current_user_can_post', $can_post, $group_id, $user_id );
}

function wir_user_can_publish ( $user_id, $post_id = false ) {

	//super admins can always post
	if ( is_super_admin() ) {
		return true;
	}

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$can_publish = false;
	//by default, everyone can publish, we assume
	if ( is_user_logged_in() ) {
		$can_publish = wir_can_user_publish_post( $user_id );
	}

	return apply_filters( 'wir_user_can_publish', $can_publish, $user_id );
}

/**
 * Can user edit the post
 * 
 * @return bool 
 */
function wir_user_can_edit ( $post_id, $user_id = false, $group_id ) {
	//if user is logged in and the post id is given only then we should proceed
	if ( ! $post_id || ! is_user_logged_in() || ! $group_id ) {
		return false;
	}

	if ( is_super_admin() )
		return true;
	
	if ( ! $user_id ) 
		$user_id = get_current_user_id();
	
	$post = get_post( $post_id );
	$terms = get_the_terms( $post, 'list' );

	// no url hacking
	if ( (string) $terms['0']->name != 'list_' . $group_id ) 
		return false;

	if ( $post->post_author == $user_id || groups_is_user_admin( $user_id, $group_id ) ) {
		return true;
	}
	//check moderator etc
	return false;
	//check if it is the 
}

function wir_user_can_delete ( $post_id, $user_id = false, $group_id ) {
	
	if ( ! $post_id && in_the_loop() ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id || ! is_user_logged_in() || ! $group_id ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	} 

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$post = get_post( $post_id );

	if ( $post->post_author == $user_id || groups_is_user_admin( $user_id, $group_id )  ) {
		return true;
	}

	return false;
}
