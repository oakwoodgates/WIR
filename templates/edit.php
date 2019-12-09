<?php
global $bp;
$action = bp_action_variable(0);
$user_id = get_current_user_id();
$post_id = bp_action_variable(1);
$group_id = bp_get_current_group_id();
$id = 0;

if( ! $post_id || ! is_numeric( $post_id ) || ! wir_user_can_edit( $post_id, $user_id, $group_id  ) ) {
//	return $id;
	$bp->template_message      = 'You do not have permission to edit this Location';
	$bp->template_message_type = 'error';
//	bp_core_add_message( 'You do not have permission to edit this Location', 'error' );
	do_action( 'template_notices' );
	return;
}

if ( wir_is_component() && ( 'edit' == $action ) && $post_id ) {
	echo wir()->core->form_edit_location();
}
