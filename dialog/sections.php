<?php
include_once 'wp.php'; 
?>
<div id="wolf-dialog-sections-inner">
	<?php 
	$args = array(
		'post_type' => 'section',
		'posts_per_page' => -1
	);
	
	$loop = new WP_Query( $args );
	
	if ( $loop->have_posts() ) : ?>
		<h3><?php _e( 'Select a section', 'wolf' ); ?></h3>
		<div id="wolf-sections-select-container">
			<select name="section" id="wolf-section-select">
			<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
				<option value="<?php the_ID(); ?>" data-title="<?php the_title(); ?>"><?php the_title(); ?></option>
			<?php endwhile; wp_reset_postdata(); ?>
			</select>
		</div>
	<?php else : ?>
		<p><?php printf( __( 'You don\'t have any section created yet. <a href="%s">Create one?</a>', 'wolf' ), esc_url( admin_url( 'post-new.php?post_type=section' ) ) ); ?></p>
	<?php endif; ?>
</div>