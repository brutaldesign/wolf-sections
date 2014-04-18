<?php
/**
 * The Template for displaying single section preview
 */
get_header();
if ( function_exists( 'wolf_post_before' ) ) wolf_post_before();

if ( function_exists( 'wolf_single_section' ) ) echo wolf_single_section();

if ( function_exists( 'wolf_post_after'  ) ) wolf_post_after();
get_footer(); 
?>