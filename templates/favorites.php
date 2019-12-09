<?php
?>
<li <?php bp_group_class(); ?>>
	<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
		<div class="item-avatar">
			<a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar( 'type=thumb&width=50&height=50' ); ?></a>
		</div>
	<?php endif; ?>

	<div class="item">
		<div class="item-title"><a href="<?php bp_group_permalink(); ?>"><?php bp_group_name(); ?></a></div>
		<div class="item-meta"><span class="activity"><?php printf( __( 'active %s', 'wir' ), bp_get_group_last_active() ); ?></span></div>

		<div class="item-desc"><?php bp_group_description_excerpt(); ?></div>

		<?php

		/**
		 * Fires inside the listing of an individual group listing item.
		 *
		 * @since SP Favorite Groups (1.0.0)
		 */
		do_action( 'bp_directory_groups_item' ); ?>

	</div>
	<div class="action">

		<?php

		/**
		 * Fires inside the action section of an individual group listing item.
		 *
		 * @since SP Favorite Groups (1.0.0)
		 */
		do_action( 'bp_directory_groups_actions' ); ?>

		<div class="meta">
			<?php bp_group_type(); ?> / <?php bp_group_member_count(); ?>
		</div>
	</div>
	<div class="clear"></div>
</li>