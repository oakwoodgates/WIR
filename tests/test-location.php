<?php
/**
 * WIR Location Tests.
 *
 * @since   0.0.1
 * @package WIR
 */
class WIR_Location_Test extends WP_UnitTestCase {

	/**
	 * Test if our class exists.
	 *
	 * @since  0.0.1
	 */
	function test_class_exists() {
		$this->assertTrue( class_exists( 'WIR_Location') );
	}

	/**
	 * Test that we can access our class through our helper function.
	 *
	 * @since  0.0.1
	 */
	function test_class_access() {
		$this->assertInstanceOf( 'WIR_Location', wir()->location' );
	}

	/**
	 * Test to make sure the CPT now exists.
	 *
	 * @since  0.0.1
	 */
	function test_cpt_exists() {
		$this->assertTrue( post_type_exists( 'wir-location' ) );
	}

	/**
	 * Replace this with some actual testing code.
	 *
	 * @since  0.0.1
	 */
	function test_sample() {
		$this->assertTrue( true );
	}
}
