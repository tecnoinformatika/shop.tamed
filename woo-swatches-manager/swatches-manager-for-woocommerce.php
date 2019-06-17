<?php
/**
 * Plugin Name: WooCommerce Swatches Manager
 * Plugin URI: https://wordpress.org/plugins/woo-swatches-manager/
 * Author: JCodex
 * Author URI: http://jcodex.com/
 * Description: An WooCommerce Add-on to influence variable items to be more delightful and customer friendly.
 *
 * Version: 1.1
 * Requires at least:    4.4.0
 * Tested up to:         4.9.0
 * WC requires at least: 3.0.0
 * WC tested up to: 	 3.2.5
 * Text Domain: jcsmw
 * Domain Path: /languages/
 */
// Create a helper function for easy SDK access.
function wsm_jc() {
    global $wsm_jc;

    if ( ! isset( $wsm_jc ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $wsm_jc = fs_dynamic_init( array(
            'id'                  => '1734',
            'slug'                => 'woo-swatches-manager',
            'type'                => 'plugin',
            'public_key'          => 'pk_02b8c526f611dee8a68e073dce0f6',
            'is_premium'          => false,
            // If your plugin is a serviceware, set this option to false.
            'has_premium_version' => true,
            'has_addons'          => false,
            'has_paid_plans'      => true,
            'menu'                => array(
                'slug'           => 'woo-swatches-manager',
				'first-path'     => 'admin.php?page=woo-swatches-manager&tab=how-to-use',
                'support'        => false,
                'parent'         => array(
                    'slug' => 'woocommerce',
                ),
            ),
            // Set the SDK to work in a sandbox mode (for development & testing).
            // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
            'secret_key'          => 'sk_ts<=_ZKJ=Ju{7B}0YYbubMl_sZsON',
        ) );
    }

    return $wsm_jc;
}

// Init Freemius.
wsm_jc();
// Signal that SDK was initiated.
do_action( 'wsm_jc_loaded' );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * The main plugin class
 */
final class JC_WC_Swatches_Manager {
	/**
	 * The single instance of the class
	 *
	 * @var JC_WC_Swatches_Manager
	 */
	protected static $instance = null;

	/**
	 * Extra attribute types
	 *
	 * @var array
	 */
	public $types = array();

	/**
	 * Main instance
	 *
	 * @return JC_WC_Swatches_Manager
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->types = array(
			'color' => esc_html__( 'Color', 'tlsmw' ),
			'image' => esc_html__( 'Image', 'tlsmw' ),
			'label' => esc_html__( 'Label', 'tlsmw' ),
		);

		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		require_once 'includes/class-admin.php';
		require_once 'includes/class-frontend.php';
	}

	/**
	 * Initialize hooks
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		add_filter( 'product_attributes_type_selector', array( $this, 'add_attribute_types' ) );

		if ( is_admin() ) {
			add_action( 'init', array( 'JC_WC_Swatches_Manager_Admin', 'instance' ) );
		} else {
			add_action( 'init', array( 'JC_WC_Swatches_Manager_Frontend', 'instance' ) );
		}
	}

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'tlsmw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add extra attribute types
	 * Add color, image and label type
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public function add_attribute_types( $types ) {
		$types = array_merge( $types, $this->types );

		return $types;
	}

	/**
	 * Get attribute's properties
	 *
	 * @param string $taxonomy
	 *
	 * @return object
	 */
	public function get_tax_attribute( $taxonomy ) {
		global $wpdb;

		$attr = substr( $taxonomy, 3 );
		$attr = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '$attr'" );

		return $attr;
	}

	/**
	 * Instance of admin
	 *
	 * @return JC_WC_Swatches_Manager_Admin
	 */
	public function admin() {
		return JC_WC_Swatches_Manager_Admin::instance();
	}

	/**
	 * Instance of frontend
	 *
	 * @return JC_WC_Swatches_Manager_Frontend
	 */
	public function frontend() {
		return JC_WC_Swatches_Manager_Frontend::instance();
	}
}

/**
 * Main instance of plugin
 *
 * @return JC_WC_Swatches_Manager
 */
function JC_SMW() {
	return JC_WC_Swatches_Manager::instance();
}

/**
 * Display notice in case of WooCommerce plugin is not activated
 */
function jc_wc_swatches_manager_wc_notice() {
	?>

	<div class="error">
		<p><?php esc_html_e( 'Soo Product Attribute Swatches is enabled but not effective. It requires WooCommerce in order to work.', 'jcsmw' ); ?></p>
	</div>

	<?php
}

/**
 * Construct plugin when plugins loaded in order to make sure WooCommerce API is fully loaded
 * Check if WooCommerce is not activated then show an admin notice
 * or create the main instance of plugin
 */
function jc_wc_swatches_manager_constructor() {
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'jc_wc_swatches_manager_wc_notice' );
	} else {
		JC_SMW();
	}
}

add_action( 'plugins_loaded', 'jc_wc_swatches_manager_constructor' );

