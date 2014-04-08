<?php
/**
 * Display saved section list in the editor panel if post meta exists
 */
function wolf_get_section_list() {

	extract( $_POST );

	if ( isset( $_POST['post_id'] ) ) {
		
		$post_id = intval( $_POST['post_id'] );

		$meta = get_post_meta( $post_id, '_wolf_sections_list', true );

		if ( $meta ) {

			foreach ( $meta as $section_id ) {
				
				if ( 'publish' == get_post_status ( $section_id ) ) {
					echo '<div class="wolf-section-block">' . get_the_title( $section_id ) . '<span title="' . __( 'Remove', 'wolf' ) . '" class="wolf-section-remove"></span>
					<input type="hidden" name="wolf-sections[]" value="' . $section_id . '">
					</div>';
				}	

			}

		} else {

			echo 'none';

		}
	
	}
	exit;

}
add_action('wp_ajax_wolf_get_section_list', 'wolf_get_section_list');