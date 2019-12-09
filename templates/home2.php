<?php
?>
<h2><?php _e( 'Some Title', 'wir' ) ?></h2>

<p>This screen is to show the Locations of the List</p>
<?php

$group = groups_get_current_group();
$args = array(
	'post_type' => 'location',
	'posts_per_page'	=> -1,
	'tax_query' => array(
		array(
			'taxonomy' => 'list',
			'field'    => 'slug',
			'terms'    => 'wir_' . $group->id,
		),
	),
);
$query = new WP_Query( $args );

?>

<div class="groups mygroups">
	<?php
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$query = new WP_Query( array(
		'post_type' => 'location',
		'posts_per_page'	=> 50,
		'tax_query' => array(
			array(
				'taxonomy' => 'list',
				'field'    => 'slug',
				'terms'    => 'list_' . $group->id,
			),
		),
	) );

	if ( $query->have_posts() ) : ?>

		<ul id="groups-list" class="item-list" aria-live="assertive" aria-atomic="true" aria-relevant="all">

			<?php 
			while ( $query->have_posts() ) : $query->the_post(); ?>

				<li class="bp-single-group private is-admin is-member group-has-avatar">
					<div class="item-avatar">
											
					<?php /*
						<a href="<?php the_permalink() ?>"><img src="/../wp-content/plugins/buddypress/bp-core/images/mystery-group-50.png" class="avatar group-1-avatar avatar-50 photo" width="50" height="50" alt="Some image"></a>
					*/ ?>
						<?php
						( has_post_thumbnail() ) ? the_post_thumbnail( array(100, 100) ) : '';
						?>
					</div>
					<div class="item">
					<?php /*
						echo '<div class="item-title"><a href="';
						the_permalink(); 
						echo '" rel="bookmark" title="Permanent Link to';
						the_title_attribute();
						echo '">';
						the_title();
						echo '</a></div>';
					*/
					?>
						<div class="item-title"><?php the_title(); ?></div>
						<div class="item-meta"><span class="activity"><?php echo get_the_date(); ?></span></div>
						<div class="item-desc"><p>Usually a description</p></div>
					</div>
					<div class="action">
						<div class="meta">Meta</div>
					</div>
					<div class="clear"></div>
				</li>


		 	<?php endwhile; ?>

	 	</ul>
		<div class="nav-previous alignleft"><?php next_posts_link( '« Older', $query->max_num_pages ); ?></div>
		<div class="nav-next alignright"><?php previous_posts_link( 'Newer »' ); ?></div>
		<?php wp_reset_postdata();
		else : ?>
			<div id="message" class="info"><p><?php _e( 'You have not added any Locations', 'wir' ); ?></p></div>
		<?php endif; ?>
</div>
<code>This file is located in templates/home.php</code>
