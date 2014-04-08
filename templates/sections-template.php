<?php
/**
 * Template Name: Sections
 */
get_header( 'sections' );
if ( function_exists( 'wolf_post_before' ) ) wolf_post_before();

/* The loop */
while ( have_posts() ) : the_post();

	if ( function_exists( 'wolf_sections' ) ) wolf_sections( get_the_ID() );

endwhile;

if ( function_exists( 'wolf_post_after' ) ) wolf_post_after();
get_footer( 'sections' ); 
?>