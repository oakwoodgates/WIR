	<?php $bp = buddypress();

// start favorite locations
$fq = new WP_Query( wir_get_favorite_locations_query() );?>
<?php if ($fq->have_posts() ) : ?>

	<?php
	wir_loop_start();//please do not remove it
	?>
	<h3>Favorites</h3>
	<ul id="groups-list" class="item-list" aria-live="assertive" aria-atomic="true" aria-relevant="all">
<?php
	while( $fq->have_posts() ):$fq->the_post();
		$id = get_the_ID();
		$class = 'loc-' . $id; ?>
		<li class="bp-single-group group-has-avatar <?php echo $class; ?>">
			<div class="item-avatar">

			<?php /*
				<a href="<?php the_permalink() ?>"><img src="/../wp-content/plugins/buddypress/bp-core/images/mystery-group-50.png" class="avatar group-1-avatar avatar-50 photo" width="50" height="50" alt="Some image"></a>
			*/ ?>
				<?php /*
				if ( get_post_meta( $id, 'wir_location_image', true ) ) {
					echo '<img src="' . get_post_meta( $id, 'wir_location_image', true ) . '" width="100"/>';
				} else {
					$avatar = buddypress()->plugin_url . "bp-core/images/default-avatar.png";
					//echo '<div style="width:100px;height:100px"></div>';
					echo '<img src="' . $avatar . '" width="100">';
				}
			//	( has_post_thumbnail() ) ? the_post_thumbnail( array( 100, 100 ) ) : '';
				*/
				if ( $place_id = get_post_meta( $id, 'wir_place_id', true ) ) {
					$m = get_post_meta( $id, 'wir_location_image', true );
					if ( strpos($m, 'https://app' ) === 0 ) {
					   echo '<img src="'.$m.'" width="100" />';
					} else {
						$k = 'AIzaSyB5X2n2seaXMr3wozD7B9ZWe14625ItY_o';
						$a = 'https://maps.googleapis.com/maps/api/place/details/json?fields=photo&placeid='.$place_id.'&key='.$k;
						$b = wp_remote_get( $a );
						if ( is_array( $b ) ) {
						  $c = $b['body']; // use the content
							$d = json_decode( $c, true );
							$p = $d['result']['photos'][0]['photo_reference'];
							echo '<img src="https://maps.googleapis.com/maps/api/place/photo?maxwidth=100&photoreference='.$p.'&key='.$k.'" />';
						}
					}
				} else {
					if ( get_post_meta( $id, 'wir_location_image', true ) ) {
						echo '<img src="' . get_post_meta( $id, 'wir_location_image', true ) . '" width="100"/>';
					} else {
						$avatar = buddypress()->plugin_url . "bp-core/images/default-avatar.png";
						//echo '<div style="width:100px;height:100px"></div>';
						echo '<img src="' . $avatar . '" width="100">';
					}
				}
				?>
			</div>
			<div class="item">
				<div class="item-title"><?php echo get_post_meta( $id, 'wir_location_title', true ); ?></div>
				<div class="item-meta">

					<span class="activity"><?php echo get_post_meta( $id, 'wir_location_address', true ); ?></span>

					<?php if ( get_post_meta( $id, 'wir_location_website', true ) ) : ?>
						<br/>
						<span class="activity"><a href="<?php echo get_post_meta( $id, 'wir_location_website', true ); ?>" target="blank"><?php echo get_post_meta( $id, 'wir_location_website', true ); ?></a></span>
					<?php endif; ?>
				</div>
				<div class="item-desc"><?php echo get_post_meta( $id, 'wir_location_content', true ); ?></div>
			</div>
			<div class="action">
				<div class="meta">
					<?php // echo wir_get_post_publish_unpublish_link( $id );?>
					<?php echo wir_get_edit_link( $id ) . wir_get_delete_link( $id ); ?>
					<?php WIR_Favorite_Locations::favorite_button(); ?>
					<?php WIR_Clone_Location::popup_clone_button(); ?>
				</div>
			</div>
			<div class="clear"></div>
		</li>

	<?php endwhile;?>
	</ul>

	<?php
        wir_loop_end();//please do not remove it
	?>
<?php endif;
// end favorite locations ?>

	<?php
// start locations
$q = new WP_Query( wir_get_query() );?>
	<?php if ($q->have_posts() ) : ?>
	<?php do_action( 'bp_before_group_blog_content' ) ?>
	<div class="pagination no-ajax">
		<div id="posts-count" class="pag-count">
			<?php wir_posts_pagination_count( $q ) ?>
		</div>

		<div id="posts-pagination" class="pagination-links">
			<?php wir_pagination( $q ) ?>
		</div>
	</div>

	<?php do_action( 'bp_before_group_blog_list' ) ?>
	<h3>Noteworthy</h3>
	<ul id="groups-list" class="item-list" aria-live="assertive" aria-atomic="true" aria-relevant="all">

<?php
	wir_loop_start();//please do not remove it
	while( $q->have_posts() ):$q->the_post();

		$id = get_the_ID();
		$class = 'loc-' . $id;
		?>
		<li class="bp-single-group group-has-avatar <?php echo $class; ?>">
			<div class="item-avatar">

			<?php /*
				<a href="<?php the_permalink() ?>"><img src="/../wp-content/plugins/buddypress/bp-core/images/mystery-group-50.png" class="avatar group-1-avatar avatar-50 photo" width="50" height="50" alt="Some image"></a>
			*/ ?>
				<?php

				if ( $place_id = get_post_meta( $id, 'wir_place_id', true ) ) {
					$m = get_post_meta( $id, 'wir_location_image', true );
					if ( strpos($m, 'https://app' ) === 0 ) {
					   echo '<img src="'.$m.'" width="100" />';
					} else {
						$k = 'AIzaSyB5X2n2seaXMr3wozD7B9ZWe14625ItY_o';
						$a = 'https://maps.googleapis.com/maps/api/place/details/json?fields=photo&placeid='.$place_id.'&key='.$k;
						$b = wp_remote_get( $a );
						if ( is_array( $b ) ) {
						  $c = $b['body']; // use the content
							$d = json_decode( $c, true );
							$p = $d['result']['photos'][0]['photo_reference'];
							echo '<img src="https://maps.googleapis.com/maps/api/place/photo?maxwidth=100&photoreference='.$p.'&key='.$k.'" />';
						}
					}
				} else {
					if ( get_post_meta( $id, 'wir_location_image', true ) ) {
						echo '<img src="' . get_post_meta( $id, 'wir_location_image', true ) . '" width="100"/>';
					} else {
						$avatar = buddypress()->plugin_url . "bp-core/images/default-avatar.png";
						//echo '<div style="width:100px;height:100px"></div>';
						echo '<img src="' . $avatar . '" width="100">';
					}
				}
			//	( has_post_thumbnail() ) ? the_post_thumbnail( array( 100, 100 ) ) : '';
				?>
			</div>
			<div class="item">
				<div class="item-title"><?php echo get_post_meta( $id, 'wir_location_title', true ); ?></div>
				<div class="item-meta">

					<span class="activity"><?php echo get_post_meta( $id, 'wir_location_address', true ); ?></span>

					<?php if ( get_post_meta( $id, 'wir_location_website', true ) ) : ?>
						<br/>
						<span class="activity"><a href="<?php echo get_post_meta( $id, 'wir_location_website', true ); ?>" target="blank"><?php echo get_post_meta( $id, 'wir_location_website', true ); ?></a></span>
					<?php endif; ?>
				</div>
				<div class="item-desc"><?php echo get_post_meta( $id, 'wir_location_content', true ); ?></div>
			</div>
			<div class="action">
				<div class="meta">
					<?php // echo wir_get_post_publish_unpublish_link( $id );?>
					<?php echo wir_get_edit_link( $id ) . wir_get_delete_link( $id ); ?>
					<?php WIR_Favorite_Locations::favorite_button(); ?>
					<?php WIR_Clone_Location::popup_clone_button(); ?>

				</div>
			</div>
			<div class="clear"></div>
		</li>

	<?php endwhile;?>
	</ul>

	<?php
        do_action( 'bp_after_group_blog_content' ) ;
        wir_loop_end();//please do not remove it
	?>
	<div class="pagination no-ajax">
		<div id="posts-count" class="pag-count">
			<?php wir_posts_pagination_count( $q ) ?>
		</div>

		<div id="posts-pagination" class="pagination-links">
			<?php wir_pagination( $q ) ?>
		</div>
	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'This List has no Locations.', 'wir' ); ?></p>
	</div>

<?php endif;
// end locations ?>
