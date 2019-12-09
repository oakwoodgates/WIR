<?php
/**
 * WIR Favorites.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Favorites.
 *
 * @since 0.0.1
 */
class WIR_Favorites {
	public static $meta_key = '_wir_favorite_groups';

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
		if ( bp_is_active( 'groups' ) ) {
			add_action( 'bp_group_header_actions', 			array( __CLASS__, 'favorite_group_button' ), 6 );
			add_action( 'bp_directory_groups_actions', 		array( __CLASS__, 'favorite_group_button' ), 11 );
			add_action( 'bp_before_member_groups_content',  array( __CLASS__, 'favorites_before_member_groups_content'));
			add_action( 'wp_enqueue_scripts', 				array( __CLASS__, 'my_enqueue' ) );
			add_action( 'wp_ajax_spfavortie_group', 		array( __CLASS__, 'set_favorite_group' ) );
			add_action( 'wp_ajax_nopriv_spfavortie_group', 	array( __CLASS__, 'set_favorite_group' ) );
			add_shortcode( 'wir_favorite_groups', 			array( __CLASS__, 'output' ) );
		}
		if ( bp_is_active( 'groups' ) ) {
			add_filter( 'bp_get_activity_show_filters', 	array( __CLASS__, 'favorite_group_filter' ), 12, 1 );
			add_filter( 'bp_ajax_querystring', 				array( __CLASS__, 'favorite_groups_activity_filter' ), 12, 2 );
		}
		
	}
	
	public static function favorites_before_member_groups_content() {
		echo self::output();
	}

	static function my_enqueue($hook) {
		wp_enqueue_script( 'ajax-script', plugins_url( '/../assets/wir.js', __FILE__ ), array('jquery') );
		wp_enqueue_script( 'ajax-script', plugins_url( '/../assets/jquery.geocomplete.js', __FILE__ ), array('jquery') );
		wp_localize_script( 'ajax-script', 'spfavortie_group',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
	public static function favorite_group_button( $group = false ){
		echo self::get_favorite_group_button($group);
	}
	
	public static function get_favorite_group_button( $group = false ){
		global $groups_template;

		// Set group to current loop group if none passed
		if ( empty( $group ) ) {
			$group =& $groups_template->group;
		}

		// Don't show button if not logged in
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Don't show button if previously banned
		if ( bp_group_is_user_banned( $group ) ) {
			return false;
		}

		// Group creation was not completed or status is unknown
		if ( empty( $group->status ) ) {
			return false;
		}

		// Already a member
		if ( ! empty( $group->is_member ) ||  $group->status=='public') {
			
			$user_favorite_groups = get_user_meta( bp_loggedin_user_id(), self::$meta_key, true );
			
			if ( ! empty( $user_favorite_groups ) && in_array( $group->id, $user_favorite_groups ) ):
				// Setup button attributes
				$button = array(
					'id'                => 'favorite_group',
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'block_self'        => false,
					'wrapper_class'     => 'group-button ' . $group->status,
					'wrapper_id'        => 'groupbutton-' . $group->id,
					'link_href'         => wp_nonce_url( bp_get_group_permalink( $group ) . 'unfavorite-group', 'groups_unfavorite_group' ),
					'link_text'         => '',
					'link_title'        => __( 'Unfavorite Group', 'wir' ),
					'link_class'        => 'group-button unfavorite-group wirf dashicons dashicons-star-filled',
				);
			else:
				// Setup button attributes
				$button = array(
					'id'                => 'favorite_group',
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'block_self'        => false,
					'wrapper_class'     => 'group-button ' . $group->status,
					'wrapper_id'        => 'groupbutton-' . $group->id,
					'link_href'         => wp_nonce_url( bp_get_group_permalink( $group ) . 'favorite-group', 'groups_favorite_group' ),
					'link_text'         => '',
					'link_title'        => __( 'Favorite', 'wir' ),
					'link_class'        => 'group-button favorite-group wirf dashicons dashicons-star-empty',
				);

			endif;
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

		$type = sanitize_text_field( $_POST['type'] );
		$group_id = (int)$_POST['gid'];

		if ( $type=='favorite' ){
			self::groups_favorite_group( $group_id );
		} else {
			self::groups_unfavorite_group( $group_id );
		}

		exit;
	}
	
	public static function groups_favorite_group( $group_id, $user_id = 0 ) {
		if ( ! $user_id )
			$user_id = bp_loggedin_user_id();
		$user_groups = self::get_user_favorite_groups( $user_id );
		$user_groups[] = $group_id;
		update_user_meta( $user_id, self::$meta_key, $user_groups );
	}
	
	public static function groups_unfavorite_group( $group_id, $user_id = 0 ) {
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		$user_groups = self::get_user_favorite_groups( $user_id );

		$user_groups = array_diff( $user_groups, array( $group_id ) );
		update_user_meta( $user_id, self::$meta_key, $user_groups );
	}

	public static function get_user_favorite_groups( $user_id ) {
		$groups = get_user_meta( $user_id, self::$meta_key, true );
		$groups = ( ! is_array( $groups ) ? array() : $groups );
		return $groups;
	}
	
	public static function output( $atts = array() ){

		if ( empty( $atts['user_id'] ) ){
			$user_id = bp_loggedin_user_id();
		} else {
			$user_id = $atts['user_id'];
		}

		$user_groups = self::get_user_favorite_groups( $user_id );

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
				<ul id="groups-list" class="item-list">

					<?php while ( bp_groups() ) : bp_the_group(); ?>
						<?php wir_load_template( 'favorites.php' ); ?>
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
	
	public static function favorite_group_filter( $output ){
		global $bp;
		if( $bp->current_component == 'activity' ){
			$output .= '<option value="favorite_group">' . __( 'Favorite Groups', 'wir' ) . '</option>' . "\n";
		}
		return $output;
	}
	public static function favorite_groups_activity_filter( $qs = false, $object = false ) {

		if ( $object != 'activity' )
			return $qs;
	
		$args = wp_parse_args( $qs );

		if( empty( $args['type'] ) || $args['type'] != 'favorite_group' ) 
			return $qs;
		
		$user_id = bp_loggedin_user_id();
		$user_groups = self::get_user_favorite_groups( $user_id );
		if(empty($user_groups))
			return $qs;
		$include = implode( ',', $user_groups );
		if ( ! empty( $user_groups ) ):
			$args['primary_id'] = $user_groups;
			$args['object'] = 'groups';
			unset( $args['type'] );
			unset( $args['action'] );
		endif;
		
		$qs = build_query( $args );
		return $qs;
	}
}
