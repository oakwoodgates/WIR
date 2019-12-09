
<?php 
	$q = new WP_Query( wir_get_query() );
	global $post;
	if ( $q->have_posts() ) : 
?>
	<?php do_action( 'bp_before_group_blog_post_content' ) ?>

	<?php wir_loop_start();//please do not remove it ?>
	<?php while( $q->have_posts() ):$q->the_post();?>
	<div class="post" id="post-<?php the_ID(); ?>">
        <div class="author-box">
            <?php echo get_avatar( get_the_author_meta( 'user_email' ), '50' ); ?>
            <p><?php printf( __( 'by %s', 'wir' ), bp_core_get_userlink( $post->post_author ) ) ?></p>
        </div>
        <div class="post-content">
	        <?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( get_the_ID() ) ):?>

		        <div class="post-featured-image">
			        <?php  the_post_thumbnail();?>
		        </div>

	        <?php endif;?>

            <h2 class="posttitle"><a href="<?php echo wir_get_post_permalink($post);?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'wir' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
            <p class="date"><?php the_time() ?> <em><?php _e( 'in', 'wir' ) ?> <?php the_category(', ') ?> <?php printf( __( 'by %s', 'wir' ), bp_core_get_userlink( $post->post_author ) ) ?></em></p>
            <div class="entry">
                <?php the_content( __( 'Read the rest of this entry &rarr;', 'wir' ) ); ?>

                <?php wp_link_pages(array('before' => __( '<p><strong>Pages:</strong> ', 'wir' ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
            </div>

            <p class="postmetadata"><span class="tags"><?php the_tags( __( 'Tags: ', 'wir' ), ', ', '<br />'); ?></span> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', 'wir' ), __( '1 Comment &#187;', 'wir' ), __( '% Comments &#187;', 'wir' ) ); ?></span></p>
        </div>

    </div>
    <?php comments_template(); ?>
<?php endwhile;?>
<?php do_action( 'bp_after_group_blog_content' ) ; ?>
<?php wir_loop_end();//please do not remove it ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'This List has no Locations.', 'wir' ); ?></p>
	</div>

<?php endif; ?>
