<?php
/**
 * Plugin Name: Wolf Sections
 * Plugin URI: http://wpwolf.com/plugin/wolf-sections
 * Description: Create customizable sections with ease!
 * Version: 1.0.1
 * Author: WpWolf
 * Author URI: http://wpwolf.com
 * Requires at least: 3.5
 * Tested up to: 3.8.1
 *
 * Text Domain: wolf
 * Domain Path: /lang/
 *
 * @package Wolf_Sections
 * @author WpWolf
 *
 * Being a free product, this plugin is distributed as-is without official support. 
 * Verified customers however, who have purchased a premium theme
 * at http://themeforest.net/user/BrutalDesign/portfolio?ref=BrutalDesign
 * will have access to support for this plugin in the forums
 * http://help.wpwolf.com/
 *
 * Copyright (C) 2014 Constantin Saguin
 * This WordPress Plugin is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * See http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Wolf_Sections' ) ) {
	/**
	 * Main Wolf_Sections Class
	 *
	 * Contains the main functions for Wolf_Sections
	 *
	 * @class Wolf_Sections
	 * @version 1.0.1
	 * @since 1.0.1
	 * @package WolfSections
	 * @author WpWolf
	 */
	class Wolf_Sections {

		/**
		 * @var string
		 */
		public $version = '1.0.1';

		/**
		 * @var string
		 */
		private $update_url = 'http://plugins.wpwolf.com/update';

		/**
		 * @var string
		 */
		public $plugin_url;

		/**
		 * @var string
		 */
		public $plugin_path;

		/**
		 * @var string
		 */
		public $template_url;

		/**
		 * WolfSection Constructor.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Define version constant
			define( 'WOLF_SECTIONS_VERSION', $this->version );

			define( 'WOLF_SECTIONS_URL', $this->plugin_url() );

			// Installation
			register_activation_hook( __FILE__, array( $this, 'activate' ) );

			// Updates
			add_action( 'admin_init', array( $this, 'update' ), 5 );

			// Help menu
			add_action( 'admin_menu', array( $this, 'add_menu' ) );

			// check if templates already exists in the theme
			add_action( 'admin_notices', array( $this, 'check_template' ) );
			add_action( 'admin_notices', array( $this, 'create_template' ) );

			// Admin styles and scripts
			add_action( 'admin_print_styles', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			// Include required files
			$this->includes();
			
			add_action( 'init', array( $this, 'init' ), 0 );
		}

		/**
		 * activate function.
		 *
		 * @access public
		 * @return void
		 */
		public function activate() {

			// do stuff
			
		}

		/**
		 * plugin update notification.
		 *
		 * @access public
		 * @return void
		 */
		public function update() {
			
			$plugin_data     = get_plugin_data( __FILE__ );
			$current_version = $plugin_data['Version'];
			$plugin_slug     = plugin_basename( dirname( __FILE__ ) );
			$plugin_path     = plugin_basename( __FILE__ );
			$remote_path     = $this->update_url . '/' . $plugin_slug;
			
			if ( ! class_exists( 'Wolf_WP_Update' ) )
				include_once( 'classes/class-wp-update.php' );
			
			$wolf_plugin_update = new Wolf_WP_Update( $current_version, $remote_path, $plugin_path );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @access public
		 * @return void
		 */
		public function includes() {

			if ( is_admin() )
				include_once( 'includes/ajax-functions.php' );

			// Metabox class
			include_once( 'classes/class-metabox.php' );

			// Functions
			include_once( 'includes/functions.php' );
		}

		/**
		 * Init WolfSection when WordPress Initialises.
		 *
		 * @access public
		 * @return void
		 */
		public function init() {

			// Set up localisation
			$this->load_plugin_textdomain();


			// Classes/actions loaded for the frontend and for ajax requests
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

				// Styles and script
				add_action( 'wp_print_styles', array( $this, 'frontend_styles' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
				add_action( 'wp_head', array( $this, 'output_section_inline_styles' ) );

				// Add Section Shortcode
				add_shortcode( 'wolf_section', array( $this, 'shortcode' ) );

				// Add body class
				add_filter( 'body_class', array( $this, 'body_classes' ) );

			}

			// save post hooks
			add_action( 'save_post', array( $this, 'save_post' ) );

			// register post type
			$this->register_post_type();

			// add metaboxes
			$this->add_metaboxes();
		}

		/**
		 * Output classes on the body tag.
		 *
		 * @access public
		 * @param mixed $classes
		 * @return array
		 */
		public function body_classes( $classes ) {

			if ( wolf_is_sections_page() ) {

				$classes[] = 'wolf-sections-body';

			}
			return $classes;
		}

		/**
		 * Check sections template
		 *
		 * Check it the theme has a sections-template.php file 
		 * You use a raw "copy template to theme" instead of a template redirect
		 * So the user can set several section pages
		 *
		 * @access public
		 * @return void
		 */
		public function check_template() {
			
			$output = '';
			$theme_dir = get_template_directory();

			if (
				! isset( $_GET['wolf_sections_create_template'] ) 
				&& ! is_file( $theme_dir . '/sections-template.php' )
			) {

				$message = '<strong>Wolf Sections</strong> ' . sprintf( __( 'says : <em>Almost done! you need to create a template for your sections.</em> <a class="button" href="%s">Create a template</a>', 'wolf' ), esc_url( admin_url( '?wolf_sections_create_template=true' ) ) );

				$output = '<div class="updated"><p>';

				$output .= $message;

				$output .= '</p></div>';

				echo $output;

			}

			return false;
		}

		/**
		 * Create sections template
		 *
		 * @access public
		 * @return void
		 */
		public function create_template() {

			if ( 
				isset( $_GET['wolf_sections_create_template'] ) 
				&& $_GET['wolf_sections_create_template'] ==  'true' 
			) {
				$output = '';
				$template = $this->plugin_path() . '/templates/sections-template.php';
				$theme_dir = get_template_directory();

				if ( copy( $template, $theme_dir . '/sections-template.php' ) ) {
					
					$message = __( 'Your sections template has been succesfully created. To create a sections page, add a new empty page using the "sections" template.', 'wolf' );

					$output = '<div class="updated"><p>';

					$output .= $message;

					$output .= '</p></div>';

					echo $output;


				} else {

					$message = sprintf( __( 'Error creating the %1$s template. Copy the file %2$s into your %3$s theme folder to fix this.', 'wolf' ),
						'sections',
						'<code>' . str_replace( network_site_url(), '', $this->plugin_url() . '/templates/' . 'sections-template.php' ) . '</code>', 
						'<code>' . str_replace( network_site_url(), '', get_stylesheet_directory_uri() ).'</code>' );

					$output = '<div class="error"><p>';

					$output .= $message;

					$output .= '</p></div>';

					echo $output;

				}

			}
			return false;
		}

		/**
		 * Register/queue admin styles.
		 *
		 * @access public
		 * @return void
		 */
		public function admin_styles() {
			
			wp_register_style( 'wolf-section-panel-style', $this->plugin_url() . '/assets/css/panel.min.css', false, $this->version, 'all' );
			wp_enqueue_style( 'wolf-section-panel-style' );		
		}

		/**
		 * Register/queue admin scripts.
		 *
		 * @access public
		 * @return void
		 */
		public function admin_scripts() {
			
			wp_register_script( 'wolf-section-panel-script', $this->plugin_url() . '/assets/js/min/jquery.panel.min.js', 'jquery', $this->version, true );
			
			if ( isset( $_GET['post'] ) && 'page' == get_post_type( $_GET['post'] )  ) {

				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'tipsy', $this->plugin_url() . '/assets/js/min/tipsy.min.js', 'jquery', true, $this->version );
				wp_enqueue_script( 'cookie', $this->plugin_url() . '/assets/js/min/memo.min.js', 'jquery', true, $this->version );
				wp_enqueue_script( 'wolf-section-panel-script' );
				wp_localize_script( 'wolf-section-panel-script', 'WolfSectionsAjax', array(
						'adminUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
						'pluginUrl' => $this->plugin_url(),
						'SectionTabTitle' => __( 'Sections', 'wolf' ),
						'addSectionMessage' => __( 'Add a section', 'wolf' ),
						'removeSectionMessage' => __( 'Remove', 'wolf' ),
						'currentPostId' => ( $_GET['post'] ) ? $_GET['post'] : null
					)
				);
			}
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {

			$domain = 'wolf';
			$locale = apply_filters( 'wolf', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Init WolfSection style option metaboxes.
		 *
		 * @access public
		 * @return void
		 */
		public function add_metaboxes() {

			$metabox = array(

				'Styling Options' => array(

					'title' => __( 'Styling Options', 'wolf' ),
					'page' => array( 'section' ),
					'metafields' => array(

						array(
							'label'	=> __( 'Section Font Color', 'wolf' ),
							'id'	=> '_wolf_section_font_color',
							'type'	=> 'select',
							'options' => array(
								'dark' => __( 'Dark', 'wolf' ),
								'light' => __( 'Light', 'wolf' ),
							)
						),

						array(
							'label'	=> __( 'Section Background', 'wolf' ),
							'id'	=> '_wolf_section_bg',
							'type'	=> 'background',
							'parallax' => true
						),

						array(
							'label'	=> __( 'Video Background', 'wolf' ),
							'id'	=> '_wolf_section_video_bg',
							'type'	=> 'file',
						),

						array(
							'label'	=> __( 'Video Opacity (in percent)', 'wolf' ),
							'id'	=> '_wolf_section_video_bg_opacity',
							'type'	=> 'int',
						),

						array(
							'label'	=> __( 'Full Width', 'wolf' ),
							'id'	=> '_wolf_section_full',
							'type'	=> 'checkbox',
						),

						array(
							'label'	=> __( 'Padding Top', 'wolf' ),
							'id'	=> '_wolf_section_padding_top',
							'desc'	=> __( 'e.g : 50px', 'wolf' ),
							'type'	=> 'text'
						),

						array(
							'label'	=> __( 'Padding Bottom', 'wolf' ),
							'id'	=> '_wolf_section_padding_bottom',
							'desc'	=> __( 'e.g : 50px', 'wolf' ),
							'type'	=> 'text'
						),

						array(
							'label'	=> __( 'Custom CSS (will be applied on this section only)', 'wolf' ),
							'id'	=> '_wolf_section_custom_css',
							'desc'	=> __( 'e.g : h1{ color:red }', 'wolf' ),
							'type'	=> 'textarea',
						),
					)
				),
			);

			if ( class_exists( 'Wolf_Sections_Metabox' ) ) {
				$wolf_do_sections_metabox = new Wolf_Sections_Metabox( $metabox );
			}
		}

		/**
		 * Init WolfSection section post type.
		 *
		 * @access public
		 * @return void
		 */
		public function register_post_type() {

			$labels = array( 
				'name' => __( 'Sections', 'wolf' ),
				'singular_name' => __( 'Section', 'wolf' ),
				'add_new' => __( 'Add New', 'wolf' ),
				'add_new_item' => __( 'Add New Section', 'wolf' ),
				'all_items'  =>  __( 'All Sections', 'wolf' ),
				'edit_item' => __( 'Edit Section', 'wolf' ),
				'new_item' => __( 'New Section', 'wolf' ),
				'view_item' => __( 'View Section', 'wolf' ),
				'search_items' => __( 'Search Sections', 'wolf' ),
				'not_found' => __( 'No sections found', 'wolf' ),
				'not_found_in_trash' => __( 'No sections found in Trash', 'wolf' ),
				'parent_item_colon' => '',
				'menu_name' => __( 'Sections', 'wolf' ),
			);

			$args = array( 
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => false,
				'rewrite' => array( 'slug' => 'section' ),
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => false,
				'menu_position' => 5,
				'taxonomies' => array(),
				'supports' => array( 'title', 'editor' ),
				'exclude_from_search' => false,
				'menu_icon' => 'dashicons-format-aside',
			);

			register_post_type( 'section', $args );
		}

		/**
		 * Register/queue frontend styles.
		 *
		 * @access public
		 * @return void
		 */
		public function frontend_styles() {

			wp_register_style( 'wolf-sections', $this->plugin_url() . '/assets/css/sections.min.css', array(), $this->version, 'all' );

			if ( wolf_is_sections_page() )
				wp_enqueue_style( 'wolf-sections' );
		}

		/**
		 * Register/queue frontend styles.
		 *
		 * @access public
		 * @return void
		 */
		public function frontend_scripts() {

			wp_register_script( 'parallax', $this->plugin_url() . '/assets/js/min/jquery.parallax.min.js', 'jquery', '1.1.3', true );
			wp_register_script( 'wolf-sections-js', $this->plugin_url() . '/assets/js/min/jquery.sections.min.js', 'jquery', $this->version, true );

			if ( wolf_is_sections_page() ) {
				wp_enqueue_script( 'parallax' );
				wp_enqueue_script( 'wolf-sections-js' );
			}
		}

		/**
		 * Compact CSS
		 *
		 * @access public
		 * @return void
		 */
		public function compact_css( $css  ) {

			return preg_replace( '/\s+/', ' ', $css );

		}
		
		/**
		 * Parse CSS rule to an array
		 *
		 * Used for the custom CSS field.
		 * We will add the section id before every CSS rule
		 *
		 * @access public
		 * @return array
		 */
		public function parse_css( $css = null ) {

			if ( ! $css )
				return;

			$results = array();

			preg_match_all( '/(.+?)\s?\{\s?(.+?)\s?\}/', $css, $matches );
				
			if ( $matches && isset( $matches[0] ) ) {
				foreach( $matches[0] as $i => $original) {
					$results[] = $original;
				}
			}

			return $results;

		}

		/**
		 * Output section inline CSS and JS
		 *
		 * @access public
		 * @param string $content
		 * @return string
		 */
		public function output_section_inline_styles( $post_id ) {

			if ( ! is_page() )
				return;

			global $post;

			$inline_css = '';
			$inline_js = '';
			$post_id = $post_id ? $post_id :  $post->ID;


			if ( $this->get_sections_css( $post_id ) ) {
				$inline_css .= '<style type="text/css">'."\n";
				$inline_css .= '/* Sections CSS */'."\n";
				$inline_css .= $this->compact_css( $this->get_sections_css( $post_id ) ) ."\n";
				$inline_css .= '</style>'."\n";
			}

			if ( $this->get_sections_parallax( $post_id ) ) {
				$inline_js .= '<script type="text/javascript">'."\n";
				$inline_js .= '/* Sections JS */'."\n";
				$inline_js .= 'jQuery(function($){' . $this->get_sections_parallax( $post_id ) . '});' ."\n";
				$inline_js .= '</script>'."\n";
			}

			echo $inline_css;
			echo $inline_js;
		}

		/**
		 * Get section parallax options
		 *
		 * @access public
		 * @param string $content
		 * @return string
		 */
		public function get_sections_parallax( $post_id ) {

			if ( ! $post_id )
				return;

			$js = '';
			$sections = get_post_meta( $post_id, '_wolf_sections_list', true );
			$selectors = '';

			if ( $sections && $sections != array() ) {

				foreach ( $sections as $section_id ) {

					$meta_id = '_wolf_section_bg';
					$selector = '#wolf-section-' . $section_id;
					$parallax = get_post_meta( $section_id, $meta_id . '_parallax', true );
					$bg_img = get_post_meta( $section_id, $meta_id . '_img', true );

					if ( $parallax && $bg_img ) {
						
						$selectors .= $selector . ',';
					}
				}

				$selectors = substr( $selectors, 0, -1 );
				$js .= "jQuery('$selectors').addClass('wolf-section-parallax');";
			}

			return $js;
		}

		/**
		 * Get section style options
		 *
		 * @access public
		 * @param int $post_id
		 * @return string
		 */
		public function get_sections_css( $post_id ) {

			if ( ! $post_id )
				return;

			$css = '';
			$sections = get_post_meta( $post_id, '_wolf_sections_list', true );

			if ( $sections && $sections != array() ) {

				foreach ( $sections as $section_id ) {

					$meta_id = '_wolf_section_bg';
					$selector = '#wolf-section-' . $section_id;

					$url = null;
					$img = get_post_meta( $section_id, $meta_id . '_img', true );
					$color = get_post_meta( $section_id, $meta_id . '_color', true );
					$repeat = get_post_meta( $section_id, $meta_id . '_repeat', true );
					$position = get_post_meta( $section_id, $meta_id . '_position', true );
					$attachment = get_post_meta( $section_id, $meta_id . '_attachment', true );
					$size = get_post_meta( $section_id, $meta_id . '_size', true );
					$parallax = get_post_meta( $section_id, $meta_id . '_parallax', true );
					
					if ( $img )
						$url = 'url("'. $img .'")';

					if ( $color || $img ) { 
						
						if ( $parallax ) {

							$css .= "$selector {background : $color $url $repeat fixed}";
							$css .= "$selector {background-position : 50% 0}";

						} else {
							$css .= "$selector {background : $color $url $position $repeat $attachment}";
						}
						
						if ( $size == 'cover' ) {

								$css .= "$selector {
									-webkit-background-size: 100%; 
									-o-background-size: 100%; 
									-moz-background-size: 100%; 
									background-size: 100%;
									-webkit-background-size: cover; 
									-o-background-size: cover; 
									background-size: cover;
								}";
							}

						if ( $size == 'resize' ) {

							$css .= "$selector {
								-webkit-background-size: 100%; 
								-o-background-size: 100%; 
								-moz-background-size: 100%; 
								background-size: 100%;
							}";
						}
								
					}

					$padding_top = get_post_meta( $section_id, '_wolf_section_padding_top', true );
					$padding_bottom = get_post_meta( $section_id, '_wolf_section_padding_bottom', true );

					if ( $padding_top )
						$css .= "$selector .wolf-section-inner { padding-top:$padding_top }";

					if ( $padding_bottom )
						$css .= "$selector .wolf-section-inner { padding-bottom:$padding_bottom }";

					$custom_css = get_post_meta( $section_id, '_wolf_section_custom_css', true );


					if ( '' != $custom_css ) {
						$parsed_css =  $this->parse_css( $custom_css );
						foreach ( $parsed_css as $rule ) {
					 		$css .= $selector . ' ' . $rule ;
					 	}
					}
				}

			}

			return $css;

		}

		/**
		 * Save Post Meta
		 *
		 * @access public
		 * @param int $post_id
		 */
		public function save_post( $post_id ) {

			global $post;

			// var_dump( $_POST['wolf-sections'] );
			// die();

			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $post_id;
			
			// check permissions
			if ( isset( $_POST['post_type'] ) && is_object( $post ) ) {
				
				$current_post_type = get_post_type( $post->ID );
				
				if ( 'page' == $current_post_type ) {

					if ( ! current_user_can('edit_page', $post_id ) ) {
						return $post_id;
						
					} elseif ( ! current_user_can('edit_post', $post_id ) ) {
						return $post_id;
					}

					if ( isset( $_POST['wolf-sections'] ) ) {
						
						if ( ! get_post_meta( $post_id, '_wolf_sections_list', true ) )

							add_post_meta( $post_id, '_wolf_sections_list', $_POST['wolf-sections'] );
						else
							update_post_meta( $post_id, '_wolf_sections_list', $_POST['wolf-sections'] );

					}
				}
			}
		}

		/**
		 * Format section content output
		 *
		 * We want our section content to be displayed as a post content
		 *
		 * @access public
		 * @param string $content
		 * @return string
		 */
		public function custom_content_output( $content ) {

			$array = array(
				'<p>[' => '[',
				']</p>' => ']',
				']<br />' => ']'
			);
			$content = strtr( $content, $array );

			return apply_filters( 'the_content', $content );
		}

		/**
		 * Output Section
		 *
		 * @access public
		 * @param int $post_id
		 * @return string
		 */
		public function output_sections( $post_id = null ) {

			if ( ! is_page() )
				return;

			$output = '';
			$post_id = ( $post_id ) ? $post_id :  get_the_ID();
			$sections = get_post_meta( $post_id, '_wolf_sections_list', true );

			if ( $sections ) {

				$output .= '<div id="wolf-sections-container">';

				foreach ( $sections as $section_id ) {

					if ( 'publish' == get_post_status ( $section_id ) ) {
					
						$video_bg = get_post_meta( $section_id, '_wolf_section_video_bg', true );
						
						$video_opacity = absint( get_post_meta( $section_id, '_wolf_section_video_bg_opacity', true ) ) / 100;
						$font_color_class = get_post_meta( $section_id, '_wolf_section_font_color', true );
						$full_width_class = get_post_meta( $section_id, '_wolf_section_full', true ) ? ' wolf-section-full-width' : '';
						$video_bg_class = $video_bg ? ' wolf-section-video-bg' : '';

						$output .= '<section class="wolf-section wolf-section-' . $font_color_class . '-font' . $full_width_class . $video_bg_class . '" id="wolf-section-' . $section_id . '">';

						$output .= '<div class="wolf-section-inner">';

						if ( $video_bg ) {
							$video_opacity_style = ( $video_opacity > 0 ) ?  ' style="opacity:' . $video_opacity . ';"' : '';
							$output .= '<div class="wolf-section-video-container">';
							$output .= '<video' . $video_opacity_style . ' class="wolf-section-video" preload="auto" autoplay="true" loop="loop" muted="muted" volume="0">';
							$output .= '<source src="' . esc_url( $video_bg ) . '" type="video/mp4">';
							$output .= '</video>';
							$output .= '</div>';
						}
						
						$output .= '<div class="wolf-section-wrap">';

						$output .= $this->custom_content_output( get_post_field( 'post_content', $section_id ) );
						$output .= '</div>';
						if ( is_user_logged_in() ) {
							$output .= '<a class="wolf-edit-section" href="' . get_edit_post_link( $section_id ) . '">' . __( 'Edit Section', 'wolf' ) . '</a>';
						}
						$output .= '</div></section><!-- section#wolf-section-' . $section_id . ' -->';

					}
				}

				$output .= '</div>';
			}

			return $output;
		}
		
		/**
		 * Add sub menu with the help
		 *
		 * @access public
		 * @return void
		 */
		public function add_menu() {

			add_submenu_page( 'edit.php?post_type=section', __( 'Help', 'wolf' ), __( 'Help', 'wolf' ), 'edit_plugins', 'wolf-sections-help', array( $this, 'help_page' ) );
		}

		/**
		 * Displays help page
		 *
		 * @access public
		 * @return void
		 */
		public function help_page() {
			?>
			<div class="wrap">
				<h2><?php _e( 'Sections Help', 'wolf' ) ?></h2>
				<p><?php printf( __( 'To get started, <a href="%s">create your first section</a>', 'wolf' ), esc_url( admin_url( 'post-new.php?post_type=section' ) ) ); ?></p>
				<p><?php _e( 'To create a sections page, <strong>create a new page using the "Sections" page template</strong>.', 'wolf' ); ?></p>
				<p><?php _e( 'To insert sections in this page simply switch your text editor to the "Sections" panel, and click on the "plus" button to insert a section.', 'wolf' ); ?></p>
				<p><?php _e( 'You can re-order your sections by "drag n\' drop"', 'wolf' ); ?></p>
				<p><img src="<?php echo esc_url( $this->plugin_url . '/assets/images/admin/help.jpg' ); ?>" alt="sections-help-screenshots"></p>
			</div>
			<?php
		}

		/**
		 * Get the plugin url.
		 *
		 * @access public
		 * @return string
		 */
		public function plugin_url() {
			if ( $this->plugin_url ) return $this->plugin_url;
			return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @access public
		 * @return string
		 */
		public function plugin_path() {
			if ( $this->plugin_path ) return $this->plugin_path;
			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
	} // end class

	/**
	 * Init Wolf_Sections class
	 */
	$GLOBALS['wolf_sections'] = new Wolf_Sections();

	if ( ! function_exists( 'wolf_sections' ) ) {

		function wolf_sections( $post_id ) {

			global $wolf_sections;
			echo $wolf_sections->output_sections( $post_id );

		}
	}

} // class_exists check