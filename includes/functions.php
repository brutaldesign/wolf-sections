<?php
/**
 * Check if we are on page with sections
 */
function wolf_is_sections_page() {

	return is_page_template( 'sections-template.php' ) || is_page_template( 'template-sections.php' ) || is_page_template( 'page-templates/sections.php' );

}