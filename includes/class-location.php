<?php
/**
 * WIR Location.
 *
 * @since   0.0.1
 * @package WIR
 */

/**
 * WIR Location post type class.
 *
 * @since 0.0.1
 *
 * @see   https://github.com/WebDevStudios/CPT_Core
 */
class WIR_Location extends CPT_Core {
	/**
	 * Parent plugin class.
	 *
	 * @var WIR
	 * @since  0.0.1
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * Register Custom Post Types.
	 *
	 * See documentation in CPT_Core, and in wp-includes/post.php.
	 *
	 * @since  0.0.1
	 *
	 * @param  WIR $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Register this cpt.
		// First parameter should be an array with Singular, Plural, and Registered name.
		parent::__construct(
			array(
				esc_html__( 'Location', 'wir' ),
				esc_html__( 'Locations', 'wir' ),
				'location',
			),
			array(
				'supports' => array(
					'title',
					'author',
				),
				'menu_icon' => 'dashicons-admin-post', // https://developer.wordpress.org/resource/dashicons/
				'public'    => false,
			)
		);
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.1
	 */
	public function hooks() {
		add_action( 'cmb2_init', 	array( $this, 'form' ) );
		add_action( 'init', 		array( $this, 'create_list_tax' ) );

	}

	public function form() {
		$cmb = new_cmb2_box( array(
			'id'            => 'wir_location_metabox',
			'title'         => 'Location Meta Box',
			'object_types'  => array( 'location' ),
		) );

		self::fields( $cmb );
	}

	/**
	 * Add custom fields to the CPT.
	 *
	 * @since  0.0.1
	 */
	public static function fields( $cmb ) {

		// Set our prefix.
		$p = 'wir_location_';

		$cmb->add_field( array(
			'name'    => 'Title',
			'id'      => $p . 'title',
			'type'    => 'text_medium',
			'attributes'  => array(
				'required'    => 'required',
			),
		) );

		$cmb->add_field( array(
			'name'    => 'Description',
			'id'      => $p . 'content',
			'type'    => 'textarea_small',
		) );
		$cmb->add_field( array(
			'name'    => 'Neighborhood',
			'id'      => $p . 'address',
			'type'    => 'text_medium',
		) );

		$cmb->add_field( array(
			'name'    => 'Website',
			'id'      => $p . 'website',
			'type' => 'text_url',
			'protocols' => array( 'http', 'https' ), // Array of allowed protocols
		) );

		$cmb->add_field( array(
			'name'    => 'Image',
			'desc'    => 'Upload an image or enter url of an image',
			'id'      => $p . 'image',
			'type'    => 'file',
			'options' => array(
				'url' => true,
			),
			'text'    => array(
				'add_upload_file_text' => 'Upload Image' // Change upload button text. Default: "Add or Upload File"
			),
			'query_args' => array(
				'type' => array( 'image/jpeg', 'image/png', 'image/gif' ), // Make library only display PDFs.
			),
		) );

		$cmb->add_field( array(
			'id'   => 'wir_place_id',
			'type' => 'hidden',
		) );
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.0.1
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {
		$new_column = array();
		return array_merge( $new_column, $columns );
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 *
	 * @since  0.0.1
	 *
	 * @param array   $column   Column currently being rendered.
	 * @param integer $post_id  ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {
		}
	}

	public function create_list_tax() {
		register_taxonomy(
			'list',
			'location',
			array(
				'label' => __( 'List' ),
				'rewrite' => array( 'slug' => 'list' ),
				'hierarchical' => true,
			)
		);
	}
}
