<?php
/**
 * WIR Favorite Locations.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Favorite Locations.
 *
 * @since 0.0.1
 */
class WIR_Favorite_Locations {
	public static $meta_key = '_wir_favorite_locations';

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
	public static function hooks(){
		add_action( 'wp_ajax_wir_floc_hook', 		array( __CLASS__, 'set_favorite_group' ) );
		add_action( 'wp_ajax_nopriv_wir_floc_hook', array( __CLASS__, 'set_favorite_group' ) );
		add_shortcode( 'wir_favorite_locations', 	array( __CLASS__, 'output' ) );
	}
	
	public static function favorites_before_member_groups_content() {
	//	echo self::output();
	}

	public static function favorite_button(){
		echo self::get_favorite_button();
	}
	
	public static function get_favorite_button(){
		global $groups_template;

		$group =& $groups_template->group;

		// List creation was not completed or status is unknown
		if ( empty( $group->status ) ) return false;

		// Already a member
		if ( ! empty( $group->is_member ) ||  $group->status=='public') {
			
			global $post;


			if ( is_user_logged_in() ) {
				// Setup button attributes
				// Don't show button if previously banned
				if ( bp_group_is_user_banned( $group ) ) return false;

				$ufl = self::get_user_favorite_locations( bp_loggedin_user_id() );
				$uflfg = self::get_user_favorite_locations_for_group( bp_loggedin_user_id(), $group->id );

			//	if ( ! empty( $user_favorite_groups ) && in_array( $group->id, $user_favorite_groups ) ):
				if ( ! empty( $uflfg ) && in_array( $post->ID, $uflfg ) ):
					// Setup button attributes
					$button = array(
						'id'                => 'favorite_location',
						'component'         => 'groups',
						'must_be_logged_in' => true,
						'block_self'        => false,
						'link_text'         => '',
						'parent_attr' 		=> array( 
							'class' => '',
							'id'	=> $post->ID . '-' . $group->id
						),
						'button_attr' => array(
							'href' 	=> wp_nonce_url( bp_get_group_permalink( $group ) . 'unfavorite-location', 'wir_link_nonce' ),
							'class' => 'unfavorite-location wir_floc dashicons dashicons-star-filled',
							'title' => __( 'Unfavorite Location', 'wir' ),
						),
					);
				else:
					// Setup button attributes
					$button = array(
						'id'                => 'favorite_location',
						'component'         => 'groups',
						'must_be_logged_in' => true,
						'block_self'        => false,
						'link_text'         => '',
						'parent_attr' 		=> array( 
							'class' => '',
							'id'	=> $post->ID . '-' . $group->id
						),
						'button_attr' => array(
							'href' 	=> wp_nonce_url( bp_get_group_permalink( $group ) . 'favorite-location', 'wir_link_nonce' ),
							'class' => 'favorite-location wir_floc dashicons dashicons-star-empty',
							'title' => __( 'Favorite Location', 'wir' ),
						),
					);

				endif;
			} else {

				$button = WIR_Core::login_button( '', 'dashicons dashicons-star-empty' );
			}

		} else {
			return false;
		}

		/**
		 * Filters the HTML button for favoriting a group.
		 *
		 * @since SP Favorite Groups (1.0.0)
		 *
		 * @param string $button HTML button for favoriting a group.
		 */
		return bp_get_button( $button );
	}
	
	public static function set_favorite_group() {

		check_ajax_referer( 'wir_a', 'nonce' );

	//	if( ! wp_verify_nonce( $_REQUEST['nonce'], 'wir_a' ) ){
	//		wp_send_json_error();
	//	}
		$new_nonce = wp_create_nonce( 'wir_a' );

		$x = $_POST['serialized'];
		$group_id 	= (int)sanitize_text_field( $x['gid'] );
		$type 		=      sanitize_text_field( $x['type'] );
		$lnonce 	=      sanitize_text_field( $x['l_nonce'] );
		$post_id  	= (int)sanitize_text_field( $x['pid'] );

		if( ! wp_verify_nonce( $lnonce, 'wir_link_nonce' ) ){
			wp_send_json_error();
		}

		if ( $type=='favorite' ){
			self::groups_favorite_location( $group_id, $post_id );
		} else {
			self::groups_unfavorite_location( $group_id, $post_id );
		}
		wp_send_json_success( array(
			'script_response'	=> 'AJAX Request Recieved' . $type,
			'nonce' 			=> $new_nonce,
			'b_nonce' 			=> $lnonce,
			'type' 				=> $type,
			'post_id' 			=> $post_id,
		) );
	}
	
	public static function groups_favorite_location( $group_id, $post_id, $user_id = 0 ) {
		if ( ! $user_id )
			$user_id = bp_loggedin_user_id();

		$ufl = self::get_user_favorite_locations( $user_id );

		if ( empty ( $ufl["$group_id"] ) ) {
			$ufl["$group_id"] = array();
		}

		$ufl["$group_id"][] = $post_id;
		update_user_meta( $user_id, self::$meta_key, $ufl );
	}
	
	public static function groups_unfavorite_location( $group_id, $post_id, $user_id = 0 ) {
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		$ufl = self::get_user_favorite_locations( $user_id );

		if ( empty ( $ufl["$group_id"] ) )
			return;

		$ufl["$group_id"] = array_diff( $ufl["$group_id"], array( $post_id ) );
		update_user_meta( $user_id, self::$meta_key, $ufl );
	}

	public static function get_user_favorite_locations( $user_id ) {
		$ufl = get_user_meta( $user_id, self::$meta_key, true );
		$ufl = ( ! is_array( $ufl ) ? array() : $ufl );
		return $ufl;
	}

	public static function get_user_favorite_locations_for_group( $user_id, $group_id ) {
		$ufl = self::get_user_favorite_locations( $user_id );
	//	$uflfg["$group_id"] = array();
		$ufl = ( ! is_array( $ufl ) ? array() : $ufl );
		if ( empty ( $ufl["$group_id"] ) )
			$ufl["$group_id"] = array();

		return $ufl["$group_id"];
	}
	
	public static function output( $atts = array() ){

		if ( empty( $atts['user_id'] ) ){
			$user_id = bp_loggedin_user_id();
		} else {
			$user_id = $atts['user_id'];
		}

		$user_groups = self::get_user_favorite_locations( $user_id );

		if( empty( $user_groups ) ) {
			return '<div class="sp-fav-groups-none">'.__('No favorite groups found', 'wir').'</div>';
		}

		$include = implode( ',', $user_groups );
		ob_start();
//		print_r($include);
		$args = '';
		$args = array(
			'include' => $include
		);
		if ( bp_has_groups( bp_ajax_querystring( 'groups' ).'&include='.$include.'&user_id=false' ) ) :	?>

			<div id="buddypress">
			<h3>Favorites</h3>
				<ul id="groups-list wir-favorite-locations-list" class="item-list">

					<?php while ( bp_groups() ) : bp_the_group(); ?>
						<?php// wir_load_template( 'favorite-locations.php' ); ?>
					<?php endwhile; ?>

				</ul>
			</div>

		<?php else: ?>
			<div class="sp-fav-groups-none"><?php _e( 'No favorite groups found', 'wir' ); ?></div>
		<?php endif;

		$output_string = ob_get_contents();
		ob_end_clean();
		return apply_filters( 'favorite_groups_html', $output_string, $user_groups );
	}
}
