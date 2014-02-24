<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTWPIdeasAdmin
 *
 * @author kaklo
 */
if ( ! class_exists( 'RTWPIdeasAdmin' ) ) {

	class RTWPIdeasAdmin {

		/**
		 * constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_wpidea_post_type' ) );
			$this -> init_attributes();
			//add_action( 'admin_menu', array( $this, 'register_pages' ) );
			//$this -> register_taxonomies();
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'custom_tab_wpideas_tab' ), 11 );
			add_action( 'woocommerce_product_write_panels', array( $this, 'custom_tab_wpideas_content' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_meta_custom_tab_wpideas' ) );
			add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_custom_tab_wpideas' ), 98 );
		}

		/**
		 * Init global variables
		 */
		function init_attributes() {
			
		}

		/**
		 * add attributes page link in menu bar
		 * 
		 * @global type $rtWPIdeasAttributes
		 */
		function register_pages() {
			global $rtWPIdeasAttributes;
			$attributes = rtwpideas_get_supported_attribute();
			if ( is_array( $attributes ) && ! empty( $attributes ) ) {
				foreach ( $attributes as $attribute ) {
					if ( $attribute !== 'post' ) {
						add_submenu_page( 'edit.php?post_type=' . $attribute, __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute . '-attributes', array( $rtWPIdeasAttributes, 'attributes_page' ) );
					} else {
						add_submenu_page( 'edit.php', __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute . '-attributes', array( $rtWPIdeasAttributes, 'attributes_page' ) );
					}
				}
			}
		}

		/**
		 * create a taxonomies
		 * 
		 * @global type $rtWPIdeasAttributesModel
		 * @global type $rtWPIdeasAttributes
		 */
		function register_taxonomies() {
			global $rtWPIdeasAttributesModel, $rtWPIdeasAttributes;
			$attributes = $rtWPIdeasAttributesModel -> get_all_attributes( $this -> post_type );
			if ( is_array( $attributes ) ) {
				foreach ( $attributes as $attr ) {
					if ( is_object( $attr ) ) {
						$rtWPIdeasAttributes -> register_taxonomy( $this -> post_type, $attr -> id );
					} else {
						$rtWPIdeasAttributes -> register_taxonomy( $this -> post_type, 0 );
					}
				}
			}
		}

		/**
		 * Register custom post type
		 */
		function register_wpidea_post_type() {
			$labels = array(
				'name' => __( 'Ideas', 'rtCamp' ),
				'singular_name' => __( 'Idea', 'rtCamp' ),
				'add_new' => __( 'Add New', 'rtCamp' ),
				'add_new_item' => __( 'Add New Idea', 'rtCamp' ),
				'edit_item' => __( 'Edit Idea', 'rtCamp' ),
				'new_item' => __( 'New Idea', 'rtCamp' ),
				'all_items' => __( 'All Ideas', 'rtCamp' ),
				'view_item' => __( 'View Ideas', 'rtCamp' ),
				'search_items' => __( 'Search Ideas', 'rtCamp' ),
				'not_found' => __( 'No Idea found', 'rtCamp' ),
				'not_found_in_trash' => __( 'No Idea found in Trash', 'rtCamp' ),
				'parent_item_colon' => __( '', 'rtCamp' ),
				'menu_name' => __( 'Ideas', 'rtCamp' ),
			);

			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => RT_WPIDEAS_SLUG,
					'with_front' => true,
				),
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'comments', 'page-attributes' ),
			);

			register_post_type( RT_WPIDEAS_SLUG, $args );
		}

		function custom_tab_wpideas_tab() {
			?>
			<li class="rtp-swt-custom-tabs custom-tab-wpideas"><a href="#custom-tab-wpideas"><?php _e( 'Ideas', 'wp-ideas' ); ?></a></li>
			<?php
		}

		function custom_tab_wpideas_content() {
			global $post;

			$custom_tab_options = array(
				'title' => get_post_meta( $post -> ID, '_wpideas_tab_title', true ),
				'content' => get_post_meta( $post -> ID, '_wpideas_tab_content', true ),
			);
			?>
			<div id="custom-tab-wpideas" class="panel woocommerce_options_panel">
				<div class="options_group">
					<?php woocommerce_wp_checkbox( array( 'id' => '_wpideas_tab_enabled', 'label' => __( 'Enable Ideas Tab?', 'wp-ideas' ) ) ); ?>

					<p class="form-field">
						<label><?php _e( 'Tab Title:', 'wp-ideas' ); ?></label>
						<input type="text" size="5" name="_wpideas_tab_title" value="<?php echo ( isset( $custom_tab_options[ 'title' ] ) && ! empty( $custom_tab_options[ 'title' ] ) ) ? $custom_tab_options[ 'title' ] : __( 'Ideas' ); ?>" placeholder="<?php _e( 'Ideas' ); ?>" />
					</p>

					<table class="form-table">
						<tr>
							<td>
								<?php
								$settings = array(
									'text_area_name' => '_wpideas_tab_content',
									'quicktags' => true,
									'tinymce' => true,
									'media_butons' => false,
									'textarea_rows' => 30,
									'editor_class' => 'contra',
									'editor_css' => '<style>#wp-_tour_itinerary_tab_content-editor-container .wp-editor-area{height:250px; width:100%;} #wp-_tour_itinerary_tab_content-editor-container .quicktags-toolbar input {width:auto;}</style>'
								);

								$id = '_wpideas_tab_content';

								wp_editor( $custom_tab_options[ 'content' ], $id, $settings );
								?>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php
		}

		function process_product_meta_custom_tab_wpideas( $post_id ) {
			update_post_meta( $post_id, '_wpideas_tab_enabled', ( isset( $_POST[ '_wpideas_tab_enabled' ] ) && $_POST[ '_wpideas_tab_enabled' ] ) ? 'yes' : 'no'  );
			update_post_meta( $post_id, '_wpideas_tab_title', $_POST[ '_wpideas_tab_title' ] );
			update_post_meta( $post_id, '_wpideas_tab_content', $_POST[ '_wpideas_tab_content' ] );
		}

		function woocommerce_product_custom_tab_wpideas( $tabs ) {
			global $post;

			$enabled = get_post_meta( $post -> ID, '_wpideas_tab_enabled', true );

			if ( $enabled != 'yes' )
				return $tabs;

			$custom_tab_options[ 'custom_tab_wpideas' ] = array(
				'title' => get_post_meta( $post -> ID, '_wpideas_tab_title', true ),
				'priority' => 50,
				'callback' => array( $this, 'woocommerce_product_custom_panel_wpideas' ),
			);

			$tabs = array_merge( $tabs, $custom_tab_options );
			return $tabs;
		}

		function woocommerce_product_custom_panel_wpideas() {
			global $post;
			echo '<p>' . wpautop( get_post_meta( $post -> ID, '_wpideas_tab_content', true ) ) . '</p>';
		}

	}

}
