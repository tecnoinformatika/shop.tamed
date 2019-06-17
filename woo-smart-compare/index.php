<?php
/*
Plugin Name: WPC Smart Compare for WooCommerce
Plugin URI: https://wpclever.net/
Description: Smart products compare for WooCommerce.
Version: 2.7.0
Author: WPclever.net
Author URI: https://wpclever.net
Text Domain: wooscp
Domain Path: /languages/
WC requires at least: 3.0
WC tested up to: 3.6.4
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WOOSCP_VERSION' ) && define( 'WOOSCP_VERSION', '2.7.0' );
! defined( 'WOOSCP_URI' ) && define( 'WOOSCP_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOSCP_REVIEWS' ) && define( 'WOOSCP_REVIEWS', 'https://wordpress.org/support/plugin/woo-smart-compare/reviews/?filter=5' );
! defined( 'WOOSCP_CHANGELOG' ) && define( 'WOOSCP_CHANGELOG', 'https://wordpress.org/plugins/woo-smart-compare/#developers' );
! defined( 'WOOSCP_DISCUSSION' ) && define( 'WOOSCP_DISCUSSION', 'https://wordpress.org/support/plugin/woo-smart-compare' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOSCP_URI );

include 'includes/wpc-menu.php';
include 'includes/wpc-dashboard.php';

if ( ! function_exists( 'wooscp_init' ) ) {
	add_action( 'plugins_loaded', 'wooscp_init', 11 );

	function wooscp_init() {
		// load text-domain
		load_plugin_textdomain( 'wooscp', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0.0', '>=' ) ) {
			add_action( 'admin_notices', 'wooscp_notice_wc' );

			return;
		}

		if ( ! class_exists( 'WPcleverWooscp' ) ) {
			class WPcleverWooscp {
				protected static $wooscp_fields = array();
				protected static $wooscp_attributes = array();

				function __construct() {
					self::$wooscp_fields = array(
						'image'         => esc_html__( 'Image', 'wooscp' ),
						'sku'           => esc_html__( 'SKU', 'wooscp' ),
						'rating'        => esc_html__( 'Rating', 'wooscp' ),
						'price'         => esc_html__( 'Price', 'wooscp' ),
						'stock'         => esc_html__( 'Stock', 'wooscp' ),
						'availability'  => esc_html__( 'Availability', 'wooscp' ),
						'add_to_cart'   => esc_html__( 'Add to cart', 'wooscp' ),
						'description'   => esc_html__( 'Description', 'wooscp' ),
						'content'       => esc_html__( 'Content', 'wooscp' ),
						'weight'        => esc_html__( 'Weight', 'wooscp' ),
						'dimensions'    => esc_html__( 'Dimensions', 'wooscp' ),
						'color'         => esc_html__( 'Color', 'wooscp' ),
						'attributes'    => esc_html__( 'Attributes', 'wooscp' ),
						'custom_fields' => esc_html__( 'Custom fields', 'wooscp' ),
					);

					// init
					add_action( 'init', array( $this, 'wooscp_init' ) );
					add_action( 'wp_footer', array( $this, 'wooscp_wp_footer' ) );
					add_action( 'wp_enqueue_scripts', array( $this, 'wooscp_wp_enqueue_scripts' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'wooscp_admin_enqueue_scripts' ) );

					// search
					add_action( 'wp_ajax_wooscp_search', array( $this, 'wooscp_search' ) );
					add_action( 'wp_ajax_nopriv_wooscp_search', array( $this, 'wooscp_search' ) );

					// after user login
					add_action( 'wp_login', array( $this, 'wooscp_wp_login' ), 10, 2 );

					// ajax load bar items
					add_action( 'wp_ajax_wooscp_load_compare_bar', array( $this, 'wooscp_load_compare_bar' ) );
					add_action( 'wp_ajax_nopriv_wooscp_load_compare_bar', array( $this, 'wooscp_load_compare_bar' ) );

					// ajax load compare table
					add_action( 'wp_ajax_wooscp_load_compare_table', array( $this, 'wooscp_load_compare_table' ) );
					add_action( 'wp_ajax_nopriv_wooscp_load_compare_table', array(
						$this,
						'wooscp_load_compare_table'
					) );

					// settings page
					add_action( 'admin_menu', array( $this, 'wooscp_admin_menu' ) );

					// settings link
					add_filter( 'plugin_action_links', array( $this, 'wooscp_action_links' ), 10, 2 );
					add_filter( 'plugin_row_meta', array( $this, 'wooscp_row_meta' ), 10, 2 );

					// menu items
					add_filter( 'wp_nav_menu_items', array( $this, 'wooscp_nav_menu_items' ), 99, 2 );

					add_filter( 'wp_dropdown_cats', array( $this, 'wooscp_dropdown_cats_multiple' ), 10, 2 );
				}

				function wooscp_init() {
					// attributes
					$wc_attributes = wc_get_attribute_taxonomies();
					if ( $wc_attributes ) {
						foreach ( $wc_attributes as $wc_attribute ) {
							self::$wooscp_attributes[ $wc_attribute->attribute_name ] = $wc_attribute->attribute_label;
						}
					}

					// shortcode
					add_shortcode( 'wooscp', array( $this, 'wooscp_shortcode' ) );

					// image sizes
					add_image_size( 'wooscp-large', 600, 600, true );
					add_image_size( 'wooscp-small', 96, 96, true );

					// add button for archive
					$wooscp_button_archive = apply_filters( 'filter_wooscp_button_archive', get_option( '_wooscp_button_archive', 'after_add_to_cart' ) );
					switch ( $wooscp_button_archive ) {
						case 'after_title':
							add_action( 'woocommerce_shop_loop_item_title', array( $this, 'wooscp_add_button' ), 11 );
							break;
						case 'after_rating':
							add_action( 'woocommerce_after_shop_loop_item_title', array(
								$this,
								'wooscp_add_button'
							), 6 );
							break;
						case 'after_price':
							add_action( 'woocommerce_after_shop_loop_item_title', array(
								$this,
								'wooscp_add_button'
							), 11 );
							break;
						case 'before_add_to_cart':
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wooscp_add_button' ), 9 );
							break;
						case 'after_add_to_cart':
							add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wooscp_add_button' ), 11 );
							break;
					}

					// add button for single
					$wooscp_button_single = apply_filters( 'filter_wooscp_button_single', get_option( '_wooscp_button_single', '31' ) );
					if ( $wooscp_button_single !== '0' ) {
						add_action( 'woocommerce_single_product_summary', array(
							$this,
							'wooscp_add_button'
						), $wooscp_button_single );
					}
				}

				function wooscp_wp_login( $user_login, $user ) {
					if ( isset( $user->data->ID ) ) {
						$user_products = get_user_meta( $user->data->ID, 'wooscp_products', true );
						if ( $user_products !== '' ) {
							setcookie( 'wooscp_products_' . md5( 'wooscp' . $user->data->ID ), $user_products, time() + 604800, '/' );
						}
					}
				}

				function wooscp_wp_enqueue_scripts() {
					// hint
					wp_enqueue_style( 'hint', WOOSCP_URI . 'assets/libs/hint/hint.min.css' );

					// dragarrange
					wp_enqueue_script( 'dragarrange', WOOSCP_URI . 'assets/libs/dragarrange/drag-arrange.js', array( 'jquery' ), WOOSCP_VERSION, true );

					// table head fixer
					wp_enqueue_script( 'table-head-fixer', WOOSCP_URI . 'assets/libs/table-head-fixer/table-head-fixer.js', array( 'jquery' ), WOOSCP_VERSION, true );

					// perfect srollbar
					wp_enqueue_style( 'perfect-scrollbar', WOOSCP_URI . 'assets/libs/perfect-scrollbar/css/perfect-scrollbar.min.css' );
					wp_enqueue_style( 'perfect-scrollbar-wpc', WOOSCP_URI . 'assets/libs/perfect-scrollbar/css/custom-theme.css' );
					wp_enqueue_script( 'perfect-scrollbar', WOOSCP_URI . 'assets/libs/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js', array( 'jquery' ), WOOSCP_VERSION, true );

					// frontend css & js
					wp_enqueue_style( 'wooscp-frontend', WOOSCP_URI . 'assets/css/frontend.css' );
					wp_enqueue_script( 'wooscp-frontend', WOOSCP_URI . 'assets/js/frontend.js', array( 'jquery' ), WOOSCP_VERSION, true );

					$wooscp_limit_notice = get_option( '_wooscp_limit_notice' );
					if ( empty( $wooscp_limit_notice ) ) {
						$wooscp_limit_notice = esc_html__( 'You can add a maximum of {limit} products to the compare table.', 'wooscp' );
					}
					wp_localize_script( 'wooscp-frontend', 'wooscpVars', array(
							'ajaxurl'       => admin_url( 'admin-ajax.php' ),
							'user_id'       => md5( 'wooscp' . get_current_user_id() ),
							'open_button'   => $this->wooscp_nice_class_id( get_option( '_wooscp_open_button', '' ) ),
							'open_table'    => get_option( '_wooscp_open_immediately', 'yes' ) === 'yes' ? 'yes' : 'no',
							'open_bar'      => get_option( '_wooscp_open_bar_immediately', 'no' ) === 'yes' ? 'yes' : 'no',
							'click_again'   => get_option( '_wooscp_click_again', 'no' ) === 'yes' ? 'yes' : 'no',
							'remove_all'    => esc_html__( 'Do you want to remove all products from the compare?', 'wooscp' ),
							'hide_empty'    => get_option( '_wooscp_hide_empty', 'no' ),
							'click_outside' => get_option( '_wooscp_click_outside', 'yes' ),
							'freeze_column' => get_option( '_wooscp_freeze_column', 'yes' ),
							'freeze_row'    => get_option( '_wooscp_freeze_row', 'yes' ),
							'limit'         => get_option( '_wooscp_limit', '100' ),
							'limit_notice'  => $wooscp_limit_notice,
							'nonce'         => wp_create_nonce( 'wooscp-nonce' ),
						)
					);
				}

				function wooscp_admin_enqueue_scripts( $hook ) {
					if ( strpos( $hook, 'wooscp' ) ) {
						wp_enqueue_style( 'wp-color-picker' );
						wp_enqueue_style( 'wooscp-backend', WOOSCP_URI . 'assets/css/backend.css' );
						wp_enqueue_script( 'dragarrange', WOOSCP_URI . 'assets/libs/dragarrange/drag-arrange.js', array( 'jquery' ), WOOSCP_VERSION, true );
						wp_enqueue_script( 'wooscp-backend', WOOSCP_URI . 'assets/js/backend.js', array(
							'jquery',
							'wp-color-picker'
						) );
					}
				}

				function wooscp_action_links( $links, $file ) {
					static $plugin;
					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}
					if ( $plugin === $file ) {
						$settings_link = '<a href="' . admin_url( 'admin.php?page=wpclever-wooscp&tab=settings' ) . '">' . esc_html__( 'Settings', 'wooscp' ) . '</a>';
						$links[]       = '<a href="' . admin_url( 'admin.php?page=wpclever-wooscp&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'wooscp' ) . '</a>';
						array_unshift( $links, $settings_link );
					}

					return (array) $links;
				}

				function wooscp_row_meta( $links, $file ) {
					static $plugin;
					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}
					if ( $plugin === $file ) {
						$row_meta = array(
							'support' => '<a href="https://wpclever.net/contact" target="_blank">' . esc_html__( 'Premium support', 'wooscp' ) . '</a>',
						);

						return array_merge( $links, $row_meta );
					}

					return (array) $links;
				}

				function wooscp_admin_menu() {
					add_submenu_page( 'wpclever', esc_html__( 'Smart Compare', 'wooscp' ), esc_html__( 'Smart Compare', 'wooscp' ), 'manage_options', 'wpclever-wooscp', array(
						$this,
						'wooscp_settings_page'
					) );
				}

				function wooscp_dropdown_cats_multiple( $output, $r ) {
					if ( isset( $r['multiple'] ) && $r['multiple'] ) {
						$output = preg_replace( '/^<select/i', '<select multiple', $output );
						$output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );
						foreach ( array_map( 'trim', explode( ',', $r['selected'] ) ) as $value ) {
							$output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );
						}
					}

					return $output;
				}

				function wooscp_settings_page() {
					$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings';
					?>
                    <div class="wpclever_settings_page wrap">
                        <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Smart Compare', 'wooscp' ) . ' ' . WOOSCP_VERSION; ?></h1>
                        <div class="wpclever_settings_page_desc about-text">
                            <p>
								<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'wooscp' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                                <br/>
                                <a href="<?php echo esc_url( WOOSCP_REVIEWS ); ?>"
                                   target="_blank"><?php esc_html_e( 'Reviews', 'wooscp' ); ?></a> | <a
                                        href="<?php echo esc_url( WOOSCP_CHANGELOG ); ?>"
                                        target="_blank"><?php esc_html_e( 'Changelog', 'wooscp' ); ?></a>
                                | <a href="<?php echo esc_url( WOOSCP_DISCUSSION ); ?>"
                                     target="_blank"><?php esc_html_e( 'Discussion', 'wooscp' ); ?></a>
                            </p>
                        </div>
                        <div class="wpclever_settings_page_nav">
                            <h2 class="nav-tab-wrapper">
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-wooscp&tab=settings' ); ?>"
                                   class="<?php echo( $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
									<?php esc_html_e( 'Settings', 'wooscp' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-wooscp&tab=premium' ); ?>"
                                   class="<?php echo( $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
									<?php esc_html_e( 'Premium Version', 'wooscp' ); ?>
                                </a>
                            </h2>
                        </div>
                        <div class="wpclever_settings_page_content">
							<?php if ( $active_tab === 'settings' ) { ?>
                                <form method="post" action="options.php">
									<?php wp_nonce_field( 'update-options' ) ?>
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'General', 'wooscp' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Open button', 'wooscp' ); ?></th>
                                            <td>
                                                <input type="text" name="_wooscp_open_button"
                                                       value="<?php echo get_option( '_wooscp_open_button', '' ); ?>"
                                                       placeholder="<?php esc_html_e( 'button class or id', 'wooscp' ); ?>"/>
                                                <span class="description">
											<?php printf( esc_html__( 'The class or id of the button, when clicking on this button the compare bar & compare table will show up. Example %s or %s', 'wooscp' ), '<code>.open-compare-btn</code>', '<code>#open-compare-btn</code>' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Open compare bar immediately', 'wooscp' ); ?></th>
                                            <td>
                                                <input type="checkbox"
                                                       name="_wooscp_open_bar_immediately"
                                                       value="yes" <?php echo( get_option( '_wooscp_open_bar_immediately', 'no' ) === 'yes' ? 'checked' : '' ); ?>/>
                                                <span class="description">
											<?php esc_html_e( 'Check it if you want to open the compare bar immediately on page loaded.', 'wooscp' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Open compare table immediately', 'wooscp' ); ?></th>
                                            <td>
                                                <input type="checkbox"
                                                       name="_wooscp_open_immediately"
                                                       value="yes" <?php echo( get_option( '_wooscp_open_immediately', 'yes' ) === 'yes' ? 'checked' : '' ); ?>/>
                                                <span class="description">
											<?php esc_html_e( 'Check it if you want to open the compare table immediately when click to compare button. If not, it just add product to the compare bar.', 'wooscp' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Hide on Cart & Checkout page', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_hide_checkout">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_hide_checkout', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_hide_checkout', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Hide the compare on the Cart & Checkout page?', 'wooscp' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Remove when clicking again', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_click_again">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_click_again', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_click_again', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Do you want to remove product when clicking again?', 'wooscp' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Close button', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_close_button">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_close_button', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_close_button', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Enable the close button at top-right conner of compare table?', 'wooscp' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Hide if empty', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_hide_empty">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_hide_empty', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_hide_empty', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
													<?php esc_html_e( 'Hide the compare table and compare bar if haven\'t any product.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Limit', 'wooscp' ); ?></th>
                                            <td>
                                                <input name="_wooscp_limit" type="number" min="1" max="100" step="1"
                                                       value="<?php echo get_option( '_wooscp_limit', '100' ); ?>"/>
                                                <span class="description">
													<?php esc_html_e( 'The maximum of products can be added to the compare table.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Limit notice', 'wooscp' ); ?></th>
                                            <td>
                                                <input name="_wooscp_limit_notice" type="text"
                                                       style="width: 100%; max-width: 600px"
                                                       class="regular-text"
                                                       value="<?php echo get_option( '_wooscp_limit_notice' ); ?>"
                                                       placeholder="<?php esc_html_e( 'You can add a maximum of {limit} products to the compare table.', 'wooscp' ); ?>"/>
                                                <p class="description">
													<?php esc_html_e( 'The notice when reaching the limit. Use {limit} to show the number. Leave blank if you want to use the default text and can be translated.', 'wooscp' ); ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Compare', 'wooscp' ); ?>
                                            </th>
                                            <td>

                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Fields', 'wooscp' ); ?></th>
                                            <td>
                                                <ul class="wooscp-fields">
													<?php
													$saved_fields = $saved_fields_arr = array();
													if ( is_array( get_option( '_wooscp_fields' ) ) ) {
														$saved_fields = get_option( '_wooscp_fields' );
													} else {
														$saved_fields = array_keys( self::$wooscp_fields );
													}
													foreach ( $saved_fields as $sf ) {
														if ( isset( self::$wooscp_fields[ $sf ] ) ) {
															$saved_fields_arr[ $sf ] = self::$wooscp_fields[ $sf ];
														}
													}
													$fields_merge = array_merge( $saved_fields_arr, self::$wooscp_fields );
													foreach ( $fields_merge as $key => $value ) {
														echo '<li class="wooscp-fields-item"><input type="checkbox" name="_wooscp_fields[]" value="' . $key . '" ' . ( in_array( $key, $saved_fields, false ) ? 'checked' : '' ) . '/><span class="label">' . $value . '</span></li>';
													}
													?>
                                                </ul>
                                                <span class="description">
                                                    <?php esc_html_e( 'Please choose the fields you want to show on the compare table. You also can drag/drop to rearrange these fields.', 'wooscp' ); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Attributes', 'wooscp' ); ?></th>
                                            <td>
                                                <ul class="wooscp-attributes">
													<?php
													$saved_attributes = $saved_attributes_arr = array();
													if ( is_array( get_option( '_wooscp_attributes' ) ) ) {
														$saved_attributes = get_option( '_wooscp_attributes' );
													}
													foreach ( $saved_attributes as $sa ) {
														if ( isset( self::$wooscp_attributes[ $sa ] ) ) {
															$saved_attributes_arr[ $sa ] = self::$wooscp_attributes[ $sa ];
														}
													}
													$attributes_merge = array_merge( $saved_attributes_arr, self::$wooscp_attributes );
													foreach ( $attributes_merge as $key => $value ) {
														echo '<li class="wooscp-attributes-item"><input type="checkbox" name="_wooscp_attributes[]" value="' . $key . '" ' . ( in_array( $key, $saved_attributes, false ) ? 'checked' : '' ) . '/><span class="label">' . $value . '</span></li>';
													}
													?>
                                                </ul>
                                                <span class="description">
													<?php esc_html_e( 'Please choose the attributes you want to show on the compare table. You also can drag/drop to rearrange these attributes.', 'wooscp' ); ?>
												</span>
                                                <p class="description" style="color: red">
                                                    * This feature only available on Premium Version. Click <a
                                                            href="https://wpclever.net/downloads/woocommerce-smart-compare"
                                                            target="_blank">here</a> to buy, just $19!
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Custom fields', 'wooscp' ); ?></th>
                                            <td>
                                                <textarea name="_wooscp_custom_fields" rows="10" cols="50"
                                                          class="large-text"><?php echo get_option( '_wooscp_custom_fields' ); ?></textarea>
                                                <span class="description">
													<?php esc_html_e( 'Add custom field names, split by a comma.', 'wooscp' ); ?>
												</span>
                                                <p class="description" style="color: red">
                                                    * This feature only available on Premium Version. Click <a
                                                            href="https://wpclever.net/downloads/woocommerce-smart-compare"
                                                            target="_blank">here</a> to buy, just $19!
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Image size', 'wooscp' ); ?></th>
                                            <td>
												<?php
												$image_size          = get_option( '_wooscp_image_size', 'wooscp-large' );
												$image_sizes         = $this->wooscp_get_image_sizes();
												$image_sizes['full'] = array(
													'width'  => '',
													'height' => '',
													'crop'   => false
												);
												if ( ! empty( $image_sizes ) ) {
													echo '<select name="_wooscp_image_size">';
													foreach ( $image_sizes as $image_size_name => $image_size_data ) {
														echo '<option value="' . esc_attr( $image_size_name ) . '" ' . ( $image_size_name === $image_size ? 'selected' : '' ) . '>' . esc_attr( $image_size_name ) . ( ! empty( $image_size_data['width'] ) ? ' ' . $image_size_data['width'] . '&times;' . $image_size_data['height'] : '' ) . ( $image_size_data['crop'] ? ' (cropped)' : '' ) . '</option>';
													}
													echo '</select>';
												}
												?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Freeze first column', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_freeze_column">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_freeze_column', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_freeze_column', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select> <span class="description">
													<?php esc_html_e( 'Freeze the first column (fields and attributes title) when scrolling horizontally.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Freeze first row', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_freeze_row">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_freeze_row', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_freeze_row', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select> <span class="description">
													<?php esc_html_e( 'Freeze the first row (product name) when scrolling vertically.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Button', 'wooscp' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for the compare button in each product.', 'wooscp' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Type', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_button_type">
                                                    <option
                                                            value="button" <?php echo( get_option( '_wooscp_button_type', 'button' ) === 'button' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Button', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="link" <?php echo( get_option( '_wooscp_button_type', 'button' ) === 'link' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Link', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Text', 'wooscp' ); ?></th>
                                            <td>
                                                <input type="text" name="_wooscp_button_text"
                                                       value="<?php echo get_option( '_wooscp_button_text' ); ?>"
                                                       placeholder="<?php esc_html_e( 'Compare', 'wooscp' ); ?>"/>
                                                <span class="description">
													<?php esc_html_e( 'Leave blank if you want to use the default text and can be translated.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Extra class (optional)', 'wooscp' ); ?></th>
                                            <td>
                                                <input type="text" name="_wooscp_button_class"
                                                       value="<?php echo get_option( '_wooscp_button_class', '' ); ?>"/>
                                                <span class="description">
													<?php esc_html_e( 'Add extra class for action button/link, split by one space.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Show on products list', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_button_archive">
                                                    <option
                                                            value="after_title" <?php echo( get_option( '_wooscp_button_archive', 'after_add_to_cart' ) === 'after_title' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After title', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="after_rating" <?php echo( get_option( '_wooscp_button_archive', 'after_add_to_cart' ) === 'after_rating' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After rating', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="after_price" <?php echo( get_option( '_wooscp_button_archive', 'after_add_to_cart' ) === 'after_price' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After price', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="before_add_to_cart" <?php echo( get_option( '_wooscp_button_archive', 'after_add_to_cart' ) === 'before_add_to_cart' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Before add to cart', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="after_add_to_cart" <?php echo( get_option( '_wooscp_button_archive', 'after_add_to_cart' ) === 'after_add_to_cart' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After add to cart', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="0" <?php echo( get_option( '_wooscp_button_archive', 'after_add_to_cart' ) === '0' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'None', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Show on single product page', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_button_single">
                                                    <option
                                                            value="6" <?php echo( get_option( '_wooscp_button_single', '31' ) === '6' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After title', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="11" <?php echo( get_option( '_wooscp_button_single', '31' ) === '11' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After price & rating', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="21" <?php echo( get_option( '_wooscp_button_single', '31' ) === '21' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After excerpt', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="29" <?php echo( get_option( '_wooscp_button_single', '31' ) === '29' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Before add to cart', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="31" <?php echo( get_option( '_wooscp_button_single', '31' ) === '31' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After add to cart', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="41" <?php echo( get_option( '_wooscp_button_single', '31' ) === '41' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After meta', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="51" <?php echo( get_option( '_wooscp_button_single', '31' ) === '51' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'After sharing', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="0" <?php echo( get_option( '_wooscp_button_single', '31' ) === '0' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'None', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Manual compare button', 'wooscp' ); ?></th>
                                            <td>
												<span class="description">
													<?php
													printf( esc_html__( 'You can add the compare button by manually, please use the shortcode %s, eg. %s for the product with ID is 99.', 'wooscp' ), '<code>[wooscp id="{product id}"]</code>', '<code>[wooscp id="99"]</code>' );
													?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Categories', 'wooscp' ); ?></th>
                                            <td>
												<?php
												$selected_cats = get_option( '_wooscp_search_cats' );
												if ( empty( $selected_cats ) ) {
													$selected_cats = array( 0 );
												}
												wc_product_dropdown_categories(
													array(
														'name'             => '_wooscp_search_cats',
														'hide_empty'       => 0,
														'value_field'      => 'id',
														'multiple'         => true,
														'show_option_all'  => esc_html__( 'All categories', 'wooscp' ),
														'show_option_none' => '',
														'selected'         => implode( ',', $selected_cats )
													) );
												?>
                                                <span class="description">
													<?php esc_html_e( 'Only show the Compare button for products in selected categories.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Bar', 'wooscp' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for the compare bar.', 'wooscp' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Add more button', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_bar_add">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_bar_add', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_bar_add', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
													<?php esc_html_e( 'Add the button to search product and add to compare list immediately.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Add more count', 'wooscp' ); ?></th>
                                            <td>
                                                <input type="number" min="1" max="100" name="_wooscp_search_count"
                                                       value="<?php echo get_option( '_wooscp_search_count', 10 ); ?>"/>
                                                <span class="description">
													<?php esc_html_e( 'The result count of search function when clicking on "Add more" button.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Remove all button', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_bar_remove">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_bar_remove', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_bar_remove', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Add the button to remove all products from compare immediately.', 'wooscp' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Bar background color', 'wooscp' ); ?></th>
                                            <td>
												<?php $wooscp_bar_bg_color_default = apply_filters( 'wooscp_bar_bg_color_default', '#292a30' ); ?>
                                                <input type="text" name="_wooscp_bar_bg_color"
                                                       value="<?php echo get_option( '_wooscp_bar_bg_color', $wooscp_bar_bg_color_default ); ?>"
                                                       class="wooscp_color_picker"/>
                                                <span class="description">
											<?php printf( esc_html__( 'Choose the background color for the compare bar, default %s', 'wooscp' ), '<code>' . $wooscp_bar_bg_color_default . '</code>' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Bar button text', 'wooscp' ); ?></th>
                                            <td>
                                                <input type="text" name="_wooscp_bar_btn_text"
                                                       value="<?php echo get_option( '_wooscp_bar_btn_text' ); ?>"
                                                       placeholder="<?php esc_html_e( 'Compare', 'wooscp' ); ?>"/>
                                                <span class="description">
													<?php esc_html_e( 'Leave blank if you want to use the default text and can be translated.', 'wooscp' ); ?>
												</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Bar button color', 'wooscp' ); ?></th>
                                            <td>
												<?php
												$wooscp_bar_btn_color_default = apply_filters( 'wooscp_bar_btn_color_default', '#00a0d2' );
												?>
                                                <input type="text" name="_wooscp_bar_btn_color"
                                                       value="<?php echo get_option( '_wooscp_bar_btn_color', $wooscp_bar_btn_color_default ); ?>"
                                                       class="wooscp_color_picker"/>
                                                <span class="description">
											<?php printf( esc_html__( 'Choose the color for the button on compare bar, default %s', 'wooscp' ), '<code>' . $wooscp_bar_btn_color_default . '</code>' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Bar position', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_bar_pos">
                                                    <option
                                                            value="bottom" <?php echo( get_option( '_wooscp_bar_pos', 'bottom' ) === 'bottom' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Bottom', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="top" <?php echo( get_option( '_wooscp_bar_pos', 'bottom' ) === 'top' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Top', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Bar align', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_bar_align">
                                                    <option
                                                            value="right" <?php echo( get_option( '_wooscp_bar_align', 'right' ) === 'right' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Right', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="left" <?php echo( get_option( '_wooscp_bar_align', 'right' ) === 'left' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Left', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Click outside to hide', 'wooscp' ); ?></th>
                                            <td>
                                                <select name="_wooscp_click_outside">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_wooscp_click_outside', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'wooscp' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_wooscp_click_outside', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'wooscp' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Menu', 'wooscp' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for the Compare menu item.', 'wooscp' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Menu(s)', 'wooscp' ); ?></th>
                                            <td>
												<?php
												$nav_args  = array(
													'hide_empty' => false,
													'fields'     => 'id=>name',
												);
												$nav_menus = get_terms( 'nav_menu', $nav_args );

												$saved_menus = get_option( '_wooscp_menus', array() );
												foreach ( $nav_menus as $nav_id => $nav_name ) {
													echo '<input type="checkbox" name="_wooscp_menus[]" value="' . $nav_id . '" ' . ( is_array( $saved_menus ) && in_array( $nav_id, $saved_menus, false ) ? 'checked' : '' ) . '/><label>' . $nav_name . '</label><br/>';
												}
												?>
                                                <span class="description">
											<?php esc_html_e( 'Choose the menu(s) you want to add the "compare menu" at the end.', 'wooscp' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_html_e( 'Update Options', 'wooscp' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options"
                                                       value="_wooscp_open_button,_wooscp_button_type,_wooscp_button_text,_wooscp_button_class,_wooscp_button_archive,_wooscp_button_single,_wooscp_open_bar_immediately,_wooscp_open_immediately,_wooscp_hide_checkout,_wooscp_click_again,_wooscp_close_button,_wooscp_hide_empty,_wooscp_bar_add,_wooscp_bar_remove,_wooscp_bar_bg_color,_wooscp_bar_btn_text,_wooscp_bar_btn_color,_wooscp_bar_pos,_wooscp_bar_align,_wooscp_click_outside,_wooscp_limit,_wooscp_limit_notice,_wooscp_fields,_wooscp_attributes,_wooscp_custom_fields,_wooscp_image_size,_wooscp_freeze_column,_wooscp_freeze_row,_wooscp_search_count,_wooscp_search_cats,_wooscp_menus"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'premium' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>Get the Premium Version just $19! <a
                                                href="https://wpclever.net/downloads/woocommerce-smart-compare"
                                                target="_blank">https://wpclever.net/downloads/woocommerce-smart-compare</a>
                                    </p>
                                    <p><strong>Extra features for Premium Version</strong></p>
                                    <ul style="margin-bottom: 0">
                                        <li>- Add custom attributes to compare table</li>
                                        <li>- Add custom fields to compare table</li>
                                        <li>- Get the lifetime update & premium support</li>
                                    </ul>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}

				function wooscp_load_compare_bar() {
					// get items
					$wooscp_output   = '';
					$wooscp_products = array();
					if ( isset( $_POST['products'] ) && ( $_POST['products'] !== '' ) ) {
						$wooscp_products = explode( ',', $_POST['products'] );
					} else {
						$wooscp_cookie = 'wooscp_products_' . md5( 'wooscp' . get_current_user_id() );
						if ( isset( $_COOKIE[ $wooscp_cookie ] ) && ( $_COOKIE[ $wooscp_cookie ] !== '' ) ) {
							$wooscp_products = explode( ',', $_COOKIE[ $wooscp_cookie ] );
						}
					}
					if ( is_array( $wooscp_products ) && ( count( $wooscp_products ) > 0 ) ) {
						$args     = array(
							'post_type'           => 'product',
							'ignore_sticky_posts' => 1,
							'no_found_rows'       => 1,
							'posts_per_page'      => - 1,
							'post__in'            => $wooscp_products,
							'orderby'             => 'post__in'
						);
						$products = new WP_Query( $args );
						if ( $products->have_posts() ) {
							while ( $products->have_posts() ) {
								$products->the_post();
								$product_img   = get_the_post_thumbnail_url( get_the_ID(), 'wooscp-small' );
								$wooscp_output .= '<div class="wooscp-bar-item" data-id="' . get_the_ID() . '">';
								if ( $product_img !== '' ) {
									$wooscp_output .= '<img draggable="false" src="' . $product_img . '"/>';
								}
								$wooscp_output .= '<span class="wooscp-bar-item-remove" data-id="' . get_the_ID() . '"></span></div>';
							}
							wp_reset_postdata();
						}
					}
					echo $wooscp_output;
					die();
				}

				function wooscp_load_compare_table() {
					// get items
					$wooscp_output        = '';
					$wooscp_products      = array();
					$wooscp_products_data = array();
					if ( isset( $_POST['products'] ) && ( $_POST['products'] !== '' ) ) {
						$wooscp_products = explode( ',', $_POST['products'] );
					} else {
						$wooscp_cookie = 'wooscp_products_' . md5( 'wooscp' . get_current_user_id() );
						if ( isset( $_COOKIE[ $wooscp_cookie ] ) && ( $_COOKIE[ $wooscp_cookie ] !== '' ) ) {
							if ( is_user_logged_in() ) {
								update_user_meta( get_current_user_id(), 'wooscp_products', $_COOKIE[ $wooscp_cookie ] );
							}
							$wooscp_products = explode( ',', $_COOKIE[ $wooscp_cookie ] );
						}
					}
					if ( is_array( $wooscp_products ) && ( count( $wooscp_products ) > 0 ) ) {
						foreach ( $wooscp_products as $wooscp_product ) {
							$product = wc_get_product( $wooscp_product );
							if ( ! $product ) {
								continue;
							}
							$product_availability                                    = $product->get_availability();
							$wooscp_products_data[ $wooscp_product ]['title']        = '<a href="' . $product->get_permalink() . '" draggable="false">' . $product->get_title() . '</a>';
							$wooscp_products_data[ $wooscp_product ]['image']        = '<a href="' . $product->get_permalink() . '" draggable="false">' . $product->get_image( get_option( '_wooscp_image_size', 'wooscp-large' ), array( 'draggable' => 'false' ) ) . '</a>';
							$wooscp_products_data[ $wooscp_product ]['sku']          = $product->get_sku();
							$wooscp_products_data[ $wooscp_product ]['price']        = $product->get_price_html();
							$wooscp_products_data[ $wooscp_product ]['stock']        = $product->is_in_stock() ? esc_html__( 'In stock', 'wooscp' ) : esc_html__( 'Out of stock', 'wooscp' );
							$wooscp_products_data[ $wooscp_product ]['add_to_cart']  = do_shortcode( '[add_to_cart id="' . $wooscp_product . '"]' );
							$wooscp_products_data[ $wooscp_product ]['description']  = $product->get_short_description();
							$wooscp_products_data[ $wooscp_product ]['content']      = $product->get_description();
							$wooscp_products_data[ $wooscp_product ]['weight']       = $product->get_weight();
							$wooscp_products_data[ $wooscp_product ]['dimensions']   = $product->get_dimensions();
							$wooscp_products_data[ $wooscp_product ]['rating']       = wc_get_rating_html( $product->get_average_rating() );
							$wooscp_products_data[ $wooscp_product ]['color']        = $product->get_attribute( 'pa_color' );
							$wooscp_products_data[ $wooscp_product ]['availability'] = $product_availability['availability'];
						}
						$wooscp_table_class = 'table';
						if ( count( $wooscp_products_data ) === 2 ) {
							$wooscp_products_data['p1']['title'] = '';
							$wooscp_table_class                  .= ' has-2';
						} elseif ( count( $wooscp_products_data ) === 1 ) {
							$wooscp_products_data['p1']['title'] = '';
							$wooscp_products_data['p2']['title'] = '';
							$wooscp_table_class                  .= ' has-1';
						}
						$wooscp_output .= '<table id="wooscp_table" class="' . esc_attr( $wooscp_table_class ) . '"><thead><tr><th>&nbsp;</th>';
						foreach ( $wooscp_products_data as $wooscp_product ) {
							if ( $wooscp_product['title'] !== '' ) {
								$wooscp_output .= '<th>' . $wooscp_product['title'] . '</th>';
							} else {
								$wooscp_output .= '<th class="th-placeholder"></th>';
							}
						}
						$wooscp_output .= '</tr></thead><tbody>';
						if ( is_array( get_option( '_wooscp_fields' ) ) ) {
							$saved_fields = get_option( '_wooscp_fields' );
						} else {
							$saved_fields = array_keys( self::$wooscp_fields );
						}
						foreach ( $saved_fields as $saved_field ) {
							if ( ( $saved_field !== 'attributes' ) && ( $saved_field !== 'custom_fields' ) ) {
								$wooscp_output .= '<tr class="tr-' . esc_attr( $saved_field ) . '"><td>' . esc_html( self::$wooscp_fields[ $saved_field ] ) . '</td>';
								foreach ( $wooscp_products_data as $wooscp_product ) {
									if ( $wooscp_product['title'] !== '' ) {
										if ( isset( $wooscp_product[ $saved_field ] ) ) {
											$wooscp_output .= '<td>' . $wooscp_product[ $saved_field ] . '</td>';
										} else {
											$wooscp_output .= '<td>&nbsp;</td>';
										}
									} else {
										$wooscp_output .= '<td class="td-placeholder"></td>';
									}
								}
								$wooscp_output .= '</tr>';
							}
						}
						$wooscp_output .= '</tbody></table>';
					} else {
						$wooscp_output = '<div class="wooscp-no-result">' . esc_html__( 'No products in the compare table.', 'wooscp' ) . '</div>';
					}
					echo $wooscp_output;
					die();
				}

				function wooscp_search() {
					if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wooscp-nonce' ) ) {
						die( 'Permissions check failed' );
					}
					$keyword       = sanitize_text_field( $_POST['keyword'] );
					$selected_cats = get_option( '_wooscp_search_cats' );
					if ( empty( $selected_cats ) ) {
						$selected_cats = array( 0 );
					}
					$wooscp_query_args = array(
						's'              => $keyword,
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => get_option( '_wooscp_search_count', 10 )
					);
					if ( is_array( $selected_cats ) && ( count( $selected_cats ) > 0 ) && ( $selected_cats[0] !== '0' ) ) {
						$wooscp_query_args['tax_query'] = array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $selected_cats,
							),
						);
					}
					$wooscp_query = new WP_Query( $wooscp_query_args );
					if ( $wooscp_query->have_posts() ) {
						echo '<ul>';
						while ( $wooscp_query->have_posts() ) {
							$wooscp_query->the_post();
							echo '<li>';
							echo '<div class="item-inner">';
							echo '<div class="item-image">' . get_the_post_thumbnail( get_the_ID(), 'wooscp-small' ) . '</div>';
							echo '<div class="item-name">' . get_the_title() . '</div>';
							echo '<div class="item-add wooscp-item-add" data-id="' . get_the_ID() . '"><span>+</span></div>';
							echo '</div>';
							echo '</li>';
						}
						echo '</ul>';
						wp_reset_postdata();
					} else {
						echo '<ul><span>' . sprintf( esc_html__( 'No results found for "%s"', 'wooscp' ), $keyword ) . '</span></ul>';
					}
					die();
				}

				function wooscp_add_button() {
					echo do_shortcode( '[wooscp]' );
				}

				function wooscp_shortcode( $atts ) {
					$output = '';

					$atts = shortcode_atts( array(
						'id'   => null,
						'type' => get_option( '_wooscp_button_type', 'button' )
					), $atts, 'wooscp' );

					if ( ! $atts['id'] ) {
						global $product;
						$atts['id'] = $product->get_id();
					}

					if ( $atts['id'] ) {
						// check cats
						$selected_cats = get_option( '_wooscp_search_cats', array() );
						if ( ! empty( $selected_cats ) && ( $selected_cats[0] !== '0' ) ) {
							$in_cats = false;
							$terms   = wp_get_post_terms( $atts['id'], 'product_cat', array( 'fields' => 'ids' ) );
							foreach ( $terms as $term ) {
								if ( in_array( $term, $selected_cats, false ) ) {
									$in_cats = true;
								}
							}
							if ( ! $in_cats ) {
								return '';
							}
						}

						// button text
						$button_text = get_option( '_wooscp_button_text' );
						if ( empty( $button_text ) ) {
							$button_text = esc_html__( 'Compare', 'wooscp' );
						}
						if ( $atts['type'] === 'link' ) {
							$output = '<a href="#" class="wooscp-btn wooscp-btn-' . esc_attr( $atts['id'] ) . ' ' . get_option( '_wooscp_button_class' ) . '" data-id="' . esc_attr( $atts['id'] ) . '">' . esc_html( $button_text ) . '</a>';
						} else {
							$output = '<button class="wooscp-btn wooscp-btn-' . esc_attr( $atts['id'] ) . ' ' . get_option( '_wooscp_button_class' ) . '" data-id="' . esc_attr( $atts['id'] ) . '">' . esc_html( $button_text ) . '</button>';
						}
					}

					return apply_filters( 'wooscp_button_html', $output, $atts['id'] );
				}

				function wooscp_wp_footer() {
					$wooscp_class = 'wooscp-area';
					$wooscp_class .= ' wooscp-bar-' . get_option( '_wooscp_bar_pos', 'bottom' ) . ' wooscp-bar-' . get_option( '_wooscp_bar_align', 'right' );
					if ( get_option( '_wooscp_hide_checkout', 'yes' ) === 'yes' ) {
						$wooscp_class .= ' wooscp-hide-checkout';
					}
					$wooscp_bar_bg_color_default  = apply_filters( 'wooscp_bar_bg_color_default', '#292a30' );
					$wooscp_bar_btn_color_default = apply_filters( 'wooscp_bar_btn_color_default', '#00a0d2' );
					?>
                    <div class="wooscp-search">
                        <div class="wooscp-search-inner">
                            <div class="wooscp-search-content">
                                <div class="wooscp-search-content-inner">
                                    <div class="wooscp-search-close"></div>
                                    <div class="wooscp-search-input">
                                        <input type="search" id="wooscp_search_input"
                                               placeholder="<?php esc_html_e( 'Type any keyword to search...', 'wooscp' ); ?>"/>
                                    </div>
                                    <div class="wooscp-search-result"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="wooscp-area" class="<?php echo esc_attr( $wooscp_class ); ?>"
                         data-bg-color="<?php echo apply_filters( 'wooscp_bar_bg_color', get_option( '_wooscp_bar_bg_color', $wooscp_bar_bg_color_default ) ); ?>"
                         data-btn-color="<?php echo apply_filters( 'wooscp_bar_btn_color', get_option( '_wooscp_bar_btn_color', $wooscp_bar_btn_color_default ) ); ?>">
                        <div class="wooscp-inner">
                            <div class="wooscp-table">
                                <div class="wooscp-table-inner">
									<?php echo( get_option( '_wooscp_close_button', 'yes' ) === 'yes' ? '<span id="wooscp-table-close" class="wooscp-table-close">' . esc_html__( 'Close', 'wooscp' ) . '</span>' : '' ); ?>
                                    <div class="wooscp-table-items"></div>
                                </div>
                            </div>
                            <div class="wooscp-bar">
								<?php if ( get_option( '_wooscp_bar_add', 'yes' ) !== 'no' ) { ?>
                                    <div class="wooscp-bar-search hint--top"
                                         aria-label="<?php esc_html_e( 'Add product', 'wooscp' ); ?>"></div>
								<?php } ?>
                                <div class="wooscp-bar-items"></div>
								<?php if ( get_option( '_wooscp_bar_remove', 'no' ) === 'yes' ) { ?>
                                    <div class="wooscp-bar-remove hint--top"
                                         aria-label="<?php esc_html_e( 'Remove all', 'wooscp' ); ?>"></div>
								<?php } ?>
                                <div class="wooscp-bar-btn wooscp-bar-btn-text">
                                    <div class="wooscp-bar-btn-icon-wrapper">
                                        <div class="wooscp-bar-btn-icon-inner"><span></span><span></span><span></span>
                                        </div>
                                    </div>
									<?php
									$bar_btn_text = get_option( '_wooscp_bar_btn_text' );
									if ( empty( $bar_btn_text ) ) {
										$bar_btn_text = esc_html__( 'Compare', 'wooscp' );
									}
									echo apply_filters( 'wooscp_bar_btn_text', esc_html( $bar_btn_text ) );
									?>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
				}

				public static function wooscp_get_count() {
					$wooscp_products = array();
					if ( isset( $_POST['products'] ) && ( $_POST['products'] !== '' ) ) {
						$wooscp_products = explode( ',', $_POST['products'] );
					} else {
						$wooscp_cookie = 'wooscp_products_' . md5( 'wooscp' . get_current_user_id() );
						if ( isset( $_COOKIE[ $wooscp_cookie ] ) && ( $_COOKIE[ $wooscp_cookie ] !== '' ) ) {
							$wooscp_products = explode( ',', $_COOKIE[ $wooscp_cookie ] );
						}
					}

					return count( $wooscp_products );
				}

				function wooscp_nav_menu_items( $items, $args ) {
					$saved_menus = get_option( '_wooscp_menus', array() );
					if ( isset( $args->menu->term_id ) && is_array( $saved_menus ) && in_array( $args->menu->term_id, $saved_menus, false ) ) {
						$items .= '<li class="menu-item wooscp-menu-item menu-item-type-wooscp"><a href="#"><span class="wooscp-menu-item-inner" data-count="' . $this->wooscp_get_count() . '">' . esc_html__( 'Compare', 'wooscp' ) . '</span></a></li>';
					}

					return $items;
				}

				function wooscp_get_image_sizes() {
					global $_wp_additional_image_sizes;
					$sizes = array();

					foreach ( get_intermediate_image_sizes() as $_size ) {
						if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
							$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
							$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
							$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
						} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
							$sizes[ $_size ] = array(
								'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
								'height' => $_wp_additional_image_sizes[ $_size ]['height'],
								'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
							);
						}
					}

					return $sizes;
				}

				function wooscp_nice_class_id( $str ) {
					return preg_replace( '/[^a-zA-Z0-9#._-]/', '', $str );
				}
			}

			new WPcleverWooscp();
		}
	}
} else {
	add_action( 'admin_notices', 'wooscp_notice_premium' );
}

if ( ! function_exists( 'wooscp_notice_wc' ) ) {
	function wooscp_notice_wc() {
		?>
        <div class="error">
            <p><strong>WPC Smart Compare</strong> require WooCommerce version 3.0.0 or greater.</p>
        </div>
		<?php
	}
}

if ( ! function_exists( 'wooscp_notice_premium' ) ) {
	function wooscp_notice_premium() {
		?>
        <div class="error">
            <p>Seems you're using both free and premium version of <strong>WPC Smart Compare</strong>. Please
                deactivate the free version when using the premium version.</p>
        </div>
		<?php
	}
}
