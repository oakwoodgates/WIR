<?php
/**
 * WIR Clone List.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Clone List.
 *
 * @since 0.0.1
 */
class WIR_Clone_List {
	/**
	 * Singleton instance of plugin.
	 *
	 * @var    WIR
	 * @since  0.0.1
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.1
	 * @return  A single instance of this class.
	 */

	public function __construct() {
		if ( null === self::$single_instance ) {
			self::$single_instance = true;
			self::hooks();
		}

		return self::$single_instance;
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.1
	 */
	public function hooks() {
		add_action( 'bp_group_header_actions', 		array( __CLASS__, 'clone_list_button' ), 6 );
		add_action( 'bp_directory_groups_actions', 	array( __CLASS__, 'clone_list_button' ), 11 );
		add_action( 'wp_ajax_wir_clst_hook', 		array( __CLASS__, 'maybe_clone_list' ) );
		add_action( 'wp_ajax_nopriv_wir_clst_hook', array( __CLASS__, 'mabye_clone_list' ) );
	}

	public static function maybe_clone_list() {
		check_ajax_referer( 'wir_a', 'nonce' );

		$new_nonce = wp_create_nonce( 'wir_a' );

		$x = $_POST['serialized'];
		$group_id 	= (int)sanitize_text_field( $x['gid'] );
		$lnonce 	=      sanitize_text_field( $x['l_nonce'] );

		if( ! wp_verify_nonce( $lnonce, 'wir_link_nonce' ) ){
			wp_send_json_error();
		}

		$redirect = self::clone_list( $group_id );

		wp_send_json_success( array(
			'response'	=> 'List Copied',
			'nonce' 	=> $new_nonce,
			'b_nonce'	=> $lnonce,
			'redirect' 	=> $redirect,
			'group_id' 	=> $group_id,
		) );
	}

	public static function clone_list( $group_id = false ) {
		$old_group = groups_get_group( $group_id );
		$old_cat_slug = 'list_' . $group_id;
	//	$old_cat = get_category_by_slug( $old_cat_slug );
		$old_cat = get_term_by( 'name', $old_cat_slug, 'list' );
	//	return $old_cat;
	//	return $old_cat->term_id;

		$uid = bp_loggedin_user_id();

		$group_args = array();
		$group_args['name'] = $old_group->name . ' [copy]';
		$group_args['description'] = $old_group->description;
		$group_args['creator_id'] = $uid;
		$group_args['status'] = 'public';

		$new_group_id = groups_create_group( $group_args );
		$new_group = groups_get_group( $new_group_id );
//					return 'maybe_posts';


		$term 		= 'list_' . $new_group_id;
		$taxonomy 	= 'list';

		wp_insert_term( $term, $taxonomy, $args = array() );

	//	$old_group_posts = get_posts( array( 'category' => $old_cat->term_id ) );
		$old_group_posts = get_posts( 
			array(
				'post_type' => 'location',
				'posts_per_page'=>-1, 
				'numberposts'=>-1,
				'tax_query' => array(
        			array(
						'taxonomy' => 'list',
						'field'    => 'slug',
						'terms'    => $old_cat_slug
       				)
    			),
			)
		);
//return $old_group_posts;
		$x = 0;
		
		foreach ( $old_group_posts as $maybe_post ) {			
		//	return 'maybe_post';
			// reset vars
			$data = $post_data = $post_id = '';
			$data = array();
			$post_id = $maybe_post->ID;


			if( ! empty( get_post_meta( $post_id, 'wir_location_title', true ) ) ) 
				$data['wir_location_title'] = get_post_meta( $post_id, 'wir_location_title', true );

			if( ! empty( get_post_meta( $post_id, 'wir_location_content', true ) ) ) 
				$data['wir_location_content'] = get_post_meta( $post_id, 'wir_location_content', true );

			if( ! empty( get_post_meta( $post_id, 'wir_location_address', true ) ) ) 
				$data['wir_location_address'] = get_post_meta( $post_id, 'wir_location_address', true );

			if( ! empty( get_post_meta( $post_id, 'wir_location_website', true ) ) ) 
				$data['wir_location_website'] = get_post_meta( $post_id, 'wir_location_website', true );

			if( ! empty( get_post_meta( $post_id, 'wir_location_image', true ) ) ) 
				$data['wir_location_image'] = get_post_meta( $post_id, 'wir_location_image', true );

			if ( $f = get_post_meta( $post_id, 'wir_location_image_id', true ) )
				$data['wir_location_image_id'] = $f;


			// default post data
			$post_data = array(
				'post_title'	=> $data['wir_location_title'],// temporary, will be replaced with post ID once we have it
				'post_author'	=> $uid,
			);
		//	return $maybe_post;
			// create the post
			$post_id = WIR_Core::create_location_post( $post_data, $data, $new_group_id );
			// update count for response
			if( $post_id )
				$x = $x + 1;
			
		}



		$s = ( $x > 1 ) ? 's' : '';

		$message = 'List creation and copy of ' . $x . ' location' . $s . ' successful';
		$response = 'success';

		$link = home_url( '/lists/' . $new_group->slug );	

	//	return wp_redirect( esc_url( $link ) );
		bp_core_add_message( $message, $response );

		return $link;
	}

	public static function clone_list_button( $group = false ){
		echo self::get_clone_list_button( $group );
	}
	
	public static function get_clone_list_button( $group = false ) {
		global $groups_template;
		// Set group to current loop group if none passed
		if ( empty( $group ) )
			$group =& $groups_template->group;

		// List creation was not completed or status is unknown
		if ( empty( $group->status ) ) return false;

		// Already a member
		if ( ! empty( $group->is_member ) || 'public' == $group->status ) {
			if ( is_user_logged_in() ) {
				// Setup button attributes
				// Don't show button if previously banned
				if ( bp_group_is_user_banned( $group ) ) return false;

				$button = array(
					'id'                => 'clone_list',
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'block_self'        => false,
					'link_text'         => __( 'Copy List', 'wir' ),
					'parent_attr' 		=> array( 
						'class' => '',
						'id'	=> 'groupbutton-' . $group->id
					),
					'button_attr' => array(
						'href' 	=> wp_nonce_url( bp_get_group_permalink( $group ) . 'clone-list', 'wir_link_nonce' ),
						'class' => 'clone-list wir_clist',
						'title' => __( 'Copy List', 'wir' ),
					),
				);
			} else {
				$button = WIR_Core::login_button( 'Copy List', 'wir_clist' );
			}

		} else {
			return false;
		}

		return bp_get_button( $button );

	}
}
