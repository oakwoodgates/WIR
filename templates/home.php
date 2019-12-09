<div id="subnav" class="item-list-tabs no-ajax">
<h2>hey</h2>
	<ul>
		<?php wir_get_options_menu();?>
	</ul>
</div>
	<?php
		if ( wir_is_single_post() ) {
			wir_load_template( 'single-post.php' );
		} elseif ( wir_is_post_create() ) {
			wir_load_template( 'create.php' );
		} else {
			wir_load_template( 'blog.php');
		}
