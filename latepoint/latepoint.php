<?php
/**
 * Plugin Name: LatePoint
 * Description: Appointment Scheduling Software for WordPress
 * Version: 2.0.0
 * Author: LatePoint
 * Author URI: http://latepoint.com
 * Text Domain: latepoint
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'LatePoint' ) ) :

/**
 * Main LatePoint Class.
 *
 */

final class LatePoint {

  /**
   * LatePoint version.
   *
   */
  public $version = '2.0.0';
  public $db_version = '1.1.9';




  /**
   * LatePoint Constructor.
   */
  public function __construct() {
    if( !session_id() ) session_start();
    
    $this->define_constants();
    $this->includes();
    $this->init_hooks();
    OsDatabaseHelper::check_db_version();
    OsDatabaseHelper::check_db_version_for_addons();


    $GLOBALS['latepoint_settings'] = new OsSettingsHelper();

  }


  /**
   * Define constant if not already set.
   *
   */
  public function define( $name, $value ) {
    if ( ! defined( $name ) ) {
      define( $name, $value );
    }
  }



  /**
   * Get the plugin url. *has trailing slash
   * @return string
   */
  public static function plugin_url() {
    return plugin_dir_url( __FILE__ ) ;
  }

  public static function public_javascripts() {
    return plugin_dir_url( __FILE__ ) . 'public/javascripts/';
  }

  public static function public_vendor_javascripts() {
    return plugin_dir_url( __FILE__ ) . 'public/javascripts/vendor/';
  }

  public static function public_stylesheets() {
    return plugin_dir_url( __FILE__ ) . 'public/stylesheets/';
  }

  public static function node_modules_url() {
    return plugin_dir_url( __FILE__ ) . 'node_modules/';
  }

  public static function vendor_assets_url() {
    return plugin_dir_url( __FILE__ ) . 'vendor/';
  }

  public static function images_url() {
    return plugin_dir_url( __FILE__ ) . 'public/images/';
  }

  /**
   * Get the plugin path.
   * @return string
   */
  public static function plugin_path() {
    return plugin_dir_path( __FILE__ ) ;
  }


  /**
   * Define LatePoint Constants.
   */
  public function define_constants() {
    $upload_dir = wp_upload_dir();

    // ENVIRONMENTS TYPES
    $this->define( 'LATEPOINT_ENV_LIVE', 'live' );
    $this->define( 'LATEPOINT_ENV_DEMO', 'demo' );
    $this->define( 'LATEPOINT_ENV_DEV', 'dev' );


    $this->define( 'LATEPOINT_ENV', LATEPOINT_ENV_LIVE );
    $this->define( 'LATEPOINT_ENV_PAYMENTS', LATEPOINT_ENV_LIVE );

    $this->define( 'LATEPOINT_ALLOW_SMS', true );
    $this->define( 'LATEPOINT_ALLOW_EMAILS', true );

    $this->define( 'LATEPOINT_PLUGIN_FILE', __FILE__ );
    $this->define( 'LATEPOINT_ABSPATH', dirname( __FILE__ ) . '/' );
    $this->define( 'LATEPOINT_LIB_ABSPATH', LATEPOINT_ABSPATH . 'lib/' );
    $this->define( 'LATEPOINT_BOWER_ABSPATH', LATEPOINT_ABSPATH . 'vendor/bower_components/' );
    $this->define( 'LATEPOINT_VIEWS_ABSPATH', LATEPOINT_LIB_ABSPATH . 'views/' );
    $this->define( 'LATEPOINT_VIEWS_ABSPATH_SHARED', LATEPOINT_LIB_ABSPATH . 'views/shared/' );
    $this->define( 'LATEPOINT_VIEWS_MAILERS_ABSPATH', LATEPOINT_VIEWS_ABSPATH . 'mailers/' );
    $this->define( 'LATEPOINT_VIEWS_LAYOUTS_ABSPATH', LATEPOINT_VIEWS_ABSPATH . 'layouts/' );
    $this->define( 'LATEPOINT_VIEWS_PARTIALS_ABSPATH', LATEPOINT_VIEWS_ABSPATH . 'partials/' );
    $this->define( 'LATEPOINT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

    $this->define( 'LATEPOINT_PLUGIN_URL', $this->plugin_url() );
    $this->define( 'LATEPOINT_LIB_URL', LATEPOINT_PLUGIN_URL . 'lib/' );
    $this->define( 'LATEPOINT_PUBLIC_URL', LATEPOINT_PLUGIN_URL. 'public/' );
    $this->define( 'LATEPOINT_IMAGES_URL', LATEPOINT_PUBLIC_URL. 'images/' );
    $this->define( 'LATEPOINT_DEFAULT_AVATAR_URL', LATEPOINT_IMAGES_URL . 'default-avatar.jpg');
    $this->define( 'LATEPOINT_SERVER', 'https://latepoint.com');

    $this->define( 'LATEPOINT_WP_AGENT_ROLE', 'latepoint_agent');
    $this->define( 'LATEPOINT_WP_CUSTOMER_ROLE', 'latepoint_customer');
    $this->define( 'LATEPOINT_WP_ADMIN_ROLE', 'latepoint_admin');

    $this->define( 'LATEPOINT_VERSION', $this->version );
    $this->define( 'LATEPOINT_ENCRYPTION_KEY', 'oiaf(*Ufdsoh2ie7QEy,R@6(I9H/VoX^r4}SHC_7W-<$S!,/kd)OSw?.Y9lcd105cu$' );

    $this->define( 'LATEPOINT_AGENT_POST_TYPE', 'latepoint_agent' );
    $this->define( 'LATEPOINT_SERVICE_POST_TYPE', 'latepoint_service' );
    $this->define( 'LATEPOINT_CUSTOMER_POST_TYPE', 'latepoint_customer' );

    $this->define( 'LATEPOINT_DB_VERSION', $this->db_version );

    global $wpdb;
    $this->define( 'LATEPOINT_TABLE_BOOKINGS', $wpdb->prefix . 'latepoint_bookings');
    $this->define( 'LATEPOINT_TABLE_SERVICES', $wpdb->prefix . 'latepoint_services');
    $this->define( 'LATEPOINT_TABLE_SETTINGS', $wpdb->prefix . 'latepoint_settings');
    $this->define( 'LATEPOINT_TABLE_SERVICE_CATEGORIES', $wpdb->prefix . 'latepoint_service_categories');
    $this->define( 'LATEPOINT_TABLE_WORK_PERIODS', $wpdb->prefix . 'latepoint_work_periods');
    $this->define( 'LATEPOINT_TABLE_CUSTOM_PRICES', $wpdb->prefix . 'latepoint_custom_prices');
    $this->define( 'LATEPOINT_TABLE_AGENTS_SERVICES', $wpdb->prefix . 'latepoint_agents_services');
    $this->define( 'LATEPOINT_TABLE_ACTIVITIES', $wpdb->prefix . 'latepoint_activities');
    $this->define( 'LATEPOINT_TABLE_TRANSACTIONS', $wpdb->prefix . 'latepoint_transactions');
    $this->define( 'LATEPOINT_TABLE_AGENTS', $wpdb->prefix . 'latepoint_agents');
    $this->define( 'LATEPOINT_TABLE_CUSTOMERS', $wpdb->prefix . 'latepoint_customers');
    $this->define( 'LATEPOINT_TABLE_CUSTOMER_META', $wpdb->prefix . 'latepoint_customer_meta');
    $this->define( 'LATEPOINT_TABLE_BOOKING_META', $wpdb->prefix . 'latepoint_booking_meta');
    $this->define( 'LATEPOINT_TABLE_AGENT_META', $wpdb->prefix . 'latepoint_agent_meta');
    $this->define( 'LATEPOINT_TABLE_STEP_SETTINGS', $wpdb->prefix . 'latepoint_step_settings');
    $this->define( 'LATEPOINT_TABLE_LOCATIONS', $wpdb->prefix . 'latepoint_locations');
    $this->define( 'LATEPOINT_TABLE_SENT_REMINDERS', $wpdb->prefix . 'latepoint_sent_reminders');

    $this->define( 'LATEPOINT_BOOKING_STATUS_APPROVED', 'approved' );
    $this->define( 'LATEPOINT_BOOKING_STATUS_PENDING', 'pending' );
    $this->define( 'LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING', 'payment_pending' );
    $this->define( 'LATEPOINT_BOOKING_STATUS_CANCELLED', 'cancelled' );
    
    $this->define( 'LATEPOINT_DEFAULT_TIME_SYSTEM', '12' );

    $this->define( 'LATEPOINT_STATUS_ERROR', 'error' );
    $this->define( 'LATEPOINT_STATUS_SUCCESS', 'success' );

    $this->define( 'LATEPOINT_SERVICE_STATUS_ACTIVE', 'active' );
    $this->define( 'LATEPOINT_SERVICE_STATUS_DISABLED', 'disabled' );

    $this->define( 'LATEPOINT_LOCATION_STATUS_ACTIVE', 'active' );
    $this->define( 'LATEPOINT_LOCATION_STATUS_DISABLED', 'disabled' );

    $this->define( 'LATEPOINT_AGENT_STATUS_ACTIVE', 'active' );
    $this->define( 'LATEPOINT_AGENT_STATUS_DISABLED', 'disabled' );


    $this->define( 'LATEPOINT_DEFAULT_TIMEBLOCK_INTERVAL', 15 );
    $this->define( 'LATEPOINT_DEFAULT_PHONE_CODE', '+1' );
    $this->define( 'LATEPOINT_DEFAULT_PHONE_FORMAT', '(999) 999-9999' );

    $this->define( 'LATEPOINT_TRANSACTION_STATUS_APPROVED', 'approved' );
    $this->define( 'LATEPOINT_TRANSACTION_STATUS_DECLINED', 'declined' );

    // Payments
    $this->define( 'LATEPOINT_DEFAULT_BRAINTREE_CURRENCY_ISO_CODE', 'USD' );
    $this->define( 'LATEPOINT_DEFAULT_STRIPE_CURRENCY_ISO_CODE', 'usd' );
    $this->define( 'LATEPOINT_DEFAULT_PAYPAL_CURRENCY_ISO_CODE', 'USD' );
    
    $this->define( 'LATEPOINT_PAYMENT_PROCESSOR_STRIPE', 'stripe' );
    $this->define( 'LATEPOINT_PAYMENT_PROCESSOR_BRAINTREE', 'braintree' );
    $this->define( 'LATEPOINT_PAYMENT_PROCESSOR_PAYPAL', 'paypal' );


    $this->define( 'LATEPOINT_PAYMENT_METHOD_LOCAL', 'local' );
    $this->define( 'LATEPOINT_PAYMENT_METHOD_PAYPAL', 'paypal' );
    $this->define( 'LATEPOINT_PAYMENT_METHOD_CARD', 'card' );

    $this->define( 'LATEPOINT_PAYMENT_PORTION_FULL', 'full' );
    $this->define( 'LATEPOINT_PAYMENT_PORTION_DEPOSIT', 'deposit' );

    $this->define( 'LATEPOINT_ANY_AGENT', 'any' );

    $this->define( 'LATEPOINT_ANY_AGENT_ORDER_RANDOM', 'random' );
    $this->define( 'LATEPOINT_ANY_AGENT_ORDER_PRICE_HIGH', 'price_high' );
    $this->define( 'LATEPOINT_ANY_AGENT_ORDER_PRICE_LOW', 'price_low' );
    $this->define( 'LATEPOINT_ANY_AGENT_ORDER_BUSY_HIGH', 'busy_high' );
    $this->define( 'LATEPOINT_ANY_AGENT_ORDER_BUSY_LOW', 'busy_low' );
  }


  /**
   * Include required core files used in admin and on the frontend.
   */
  public function includes() {

    // COMPOSER AUTOLOAD
    require LATEPOINT_ABSPATH . 'vendor/autoload.php';

    // TODO - replace with __autoload https://stackoverflow.com/questions/599670/how-to-include-all-php-files-from-a-directory

    // CONTROLLERS
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/activities_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/search_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/agents_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/customers_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/services_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/transactions_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/auth_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/service_categories_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/settings_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/bookings_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/dashboard_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/wizard_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/updates_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/addons_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/debug_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/notifications_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/custom_fields_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/locations_controller.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/controllers/reminders_controller.php' );


    // MODELS
    include_once( LATEPOINT_ABSPATH . 'lib/models/model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/activity_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/work_period_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/agent_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/service_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/connector_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/service_category_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/customer_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/settings_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/booking_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/sent_reminder_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/step_settings_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/step_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/transaction_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/meta_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/booking_meta_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/customer_meta_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/agent_meta_model.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/models/location_model.php' );


    // HELPERS
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/wp_date_time.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/router_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/reminders_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/auth_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/encrypt_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/social_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/updates_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/addons_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/license_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/form_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/util_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/debug_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/wp_user_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/menu_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/image_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/icalendar_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/booking_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/activities_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/settings_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/customer_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/agent_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/service_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/database_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/money_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/time_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/notifications_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/work_periods_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/updates_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/replacer_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/payments_paypal_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/payments_stripe_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/payments_braintree_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/payments_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/meta_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/shortcodes_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/custom_fields_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/connector_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/location_helper.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/helpers/csv_helper.php' );


    // MAILERS
    include_once( LATEPOINT_ABSPATH . 'lib/mailers/mailer.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/mailers/agent_mailer.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/mailers/customer_mailer.php' );

    // SMSERS
    include_once( LATEPOINT_ABSPATH . 'lib/smsers/smser.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/smsers/agent_smser.php' );
    include_once( LATEPOINT_ABSPATH . 'lib/smsers/customer_smser.php' );


    do_action('latepoint_includes');
  }


  /**
   * Hook into actions and filters.
   */
  public function init_hooks() {
    
    // Activation hook
    register_activation_hook( __FILE__, array($this, 'create_required_tables' ));
    register_activation_hook(__FILE__, array( $this, 'on_activate' ));
    register_deactivation_hook(__FILE__, [$this, 'on_deactivate']);

    add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
    add_action( 'init', array( $this, 'init' ), 0 );
    add_action( 'admin_menu', array( $this, 'init_menus' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'load_front_scripts_and_styles' ));
    add_action( 'admin_enqueue_scripts',  array( $this, 'load_admin_scripts_and_styles' ));
    add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ));
    add_filter( 'body_class', array( $this, 'add_body_class' ) );


    // Add Link to latepoint to admin bar
    add_action( 'admin_bar_menu', array($this, 'add_latepoint_link_to_admin_bar'), 999 );


    if(OsSettingsHelper::is_using_google_login()) add_action( 'wp_head', array( $this, 'add_google_signin_meta_tags' ));
    
    // fix for output buffering error in WP
    remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

    add_action ('wp_loaded', array( $this, 'pre_route_call'));


    // Create router action
    // ajax
    add_action( 'wp_ajax_latepoint_route_call', array( $this, 'route_call') );
    add_action( 'wp_ajax_nopriv_latepoint_route_call', array( $this, 'route_call') );
    // admin custom post/get
    add_action( 'admin_post_latepoint_route_call', array( $this, 'route_call') );
    add_action( 'admin_post_nopriv_latepoint_route_call', array( $this, 'route_call') );

    // crons
    add_action('latepoint_send_reminders', [$this, 'send_reminders']);


    // Register a URL that will set this variable to true
    add_action( 'init', array( $this, 'front_route_init' ));

    // Auth
    add_filter( 'login_redirect', [$this, 'agent_user_redirect'], 10, 3 );
    

    // But WordPress has a whitelist of variables it allows, so we must put it on that list
    add_action( 'query_vars', array( $this, 'front_route_query_vars' ));

    // If this is done, we can access it later
    // This example checks very early in the process:
    // if the variable is set, we include our page and stop execution after it
    add_action( 'parse_request', array( $this, 'front_route_parse_request' ));



    add_action('admin_init', array( $this, 'redirect_after_activation'));
  }


  public function agent_user_redirect($redirect_to, $request, $user) {
    global $user;
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
      if ( in_array(LATEPOINT_WP_AGENT_ROLE, $user->roles ) ) {
        return OsRouterHelper::build_link(['dashboard', 'for_agent']);
      }
    }
    return $redirect_to;
  }

  public function send_reminders(){
    OsRemindersHelper::process_reminders();
  }

  public function on_deactivate(){
    wp_clear_scheduled_hook('latepoint_send_reminders');
  }

  function on_activate() {
    add_role(LATEPOINT_WP_AGENT_ROLE, __('LatePoint Agent'));
    $agent_role = get_role( LATEPOINT_WP_AGENT_ROLE );

    // $agent_role->add_cap( 'delete_posts' );
    // $agent_role->add_cap( 'delete_published_posts' );
    // $agent_role->add_cap( 'edit_posts' );
    // $agent_role->add_cap( 'edit_published_posts' );
    // $agent_role->add_cap( 'publish_posts' );
    $agent_role->add_cap( 'read' );
    $agent_role->add_cap( 'upload_files' );
    $agent_role->add_cap( 'edit_bookings' );


    if (! wp_next_scheduled ( 'latepoint_send_reminders' )) {
      wp_schedule_event(time(), 'hourly', 'latepoint_send_reminders');
    }

    add_role(LATEPOINT_WP_CUSTOMER_ROLE, __('LatePoint Customer'));
    // if wizard has not been visited yet - redirect to it
    if(!get_option('latepoint_wizard_visited', false)) add_option('latepoint_redirect_to_wizard', true);
  }

  function redirect_after_activation() {
    if (get_option('latepoint_redirect_to_wizard', false)) {
      delete_option('latepoint_redirect_to_wizard');
      if(!isset($_GET['activate-multi'])){
        wp_redirect(OsRouterHelper::build_link(OsRouterHelper::build_route_name('wizard', 'setup')));
      }
    }
  }

  public function front_route_parse_request( $wp ){
    if ( isset( $wp->query_vars['latepoint_is_custom_route'] ) ) {
      if(isset($wp->query_vars['route_name'])){
        $this->route_call();
      }
    }
  }

  public function front_route_query_vars( $query_vars )
  {
      $query_vars[] = 'latepoint_booking_id';
      $query_vars[] = 'latepoint_is_custom_route';
      $query_vars[] = 'route_name';
      return $query_vars;
  }

  public function front_route_init() {
    add_rewrite_rule( '^ical/([0-9]+)/?', OsRouterHelper::build_front_link(OsRouterHelper::build_route_name('bookings', 'ical_download')).'latepoint_booking_id=$matches[1]', 'top' );
    add_rewrite_rule( '^agent-login/?', OsRouterHelper::build_front_link(OsRouterHelper::build_route_name('agents', 'login_form')), 'top' );
  }

  public function route_call(){
    $route_name = OsRouterHelper::get_request_param('route_name', OsRouterHelper::build_route_name('dashboard', 'index'));
    OsRouterHelper::call_by_route_name($route_name, OsRouterHelper::get_request_param('return_format', 'html'));
  }

  public function agent_route_call(){
    $route_name = OsRouterHelper::get_request_param('route_name', OsRouterHelper::build_route_name('dashboard', 'for_agent'));
    OsRouterHelper::call_by_route_name($route_name, OsRouterHelper::get_request_param('return_format', 'html'));
  }

  public function pre_route_call(){
    if(OsRouterHelper::get_request_param('pre_route')){
      $this->route_call();
    }
  }


  public function customer_logout() {
    if ( isset( $_GET['os-action'] ) ) {
      OsAuthHelper::logout_customer();
      wp_redirect(OsSettingsHelper::get_customer_login_url());
      exit;
    }
  }



  /**
   * Init LatePoint when WordPress Initialises.
   */
  public function init() {
    $this->register_post_types();
    $this->register_shortcodes();
    // Set up localisation.
    $this->load_plugin_textdomain();
    if(OsSettingsHelper::is_using_stripe_payments()){
      OsPaymentsStripeHelper::set_api_key();
    }elseif(OsSettingsHelper::is_using_braintree_payments()){
      OsPaymentsBraintreeHelper::set_api_key();
    }
  }


  public function load_plugin_textdomain() {
    load_plugin_textdomain('latepoint', false, dirname(plugin_basename(__FILE__)) . '/languages');
  }


  /**
   * Register a custom menu page.
   */
  function init_menus() {
    if(current_user_can('edit_bookings')){
      $route_call_func = array( $this, 'agent_route_call');
      $capabilities = 'edit_bookings';
    }else{
      $route_call_func = array( $this, 'route_call');
      $capabilities = 'manage_options';
    }
    // link for admins
    add_menu_page(
        __( 'LatePoint', 'latepoint' ),
        __( 'LatePoint', 'latepoint' ),
        $capabilities,
        'latepoint',
        $route_call_func,
        'data:image/png;base64,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'
    );


  }


  function add_latepoint_link_to_admin_bar( $wp_admin_bar ) {
    $img_src = 'data:image/png;base64,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';
    $link = current_user_can('edit_bookings') ? OsRouterHelper::build_link(OsRouterHelper::build_route_name('dashboard', 'for_agent')) : OsRouterHelper::build_link(OsRouterHelper::build_route_name('dashboard', 'index'));
    $args = array(
      'id'    => 'latepoint_top_link',
      'title' => '<img style="height: 19px; margin-top: -2px; width: auto; margin-right: 5px; vertical-align: middle; display: inline-block;" src="'.$img_src.'"/><span style="">'.__('LatePoint', 'latepoint').'</span>',
      'href'  => $link,
      'meta'  => array( 'class' => '' )
    );
    $wp_admin_bar->add_node( $args );
  }


  /**
   * Register shortcodes
   */
  public function register_shortcodes() {
    add_shortcode( 'latepoint_book_button', array('OsShortcodesHelper', 'shortcode_latepoint_book_button' ));
    add_shortcode( 'latepoint_book_form', array('OsShortcodesHelper', 'shortcode_latepoint_book_form' ));
    add_shortcode( 'latepoint_customer_dashboard', array('OsShortcodesHelper', 'shortcode_latepoint_customer_dashboard' ));
    add_shortcode( 'latepoint_customer_login', array('OsShortcodesHelper', 'shortcode_latepoint_customer_login' ));
  }

  /*

   SHORTCODES 

  */




  public function setup_environment() {
    if ( ! current_theme_supports( 'post-thumbnails' ) ) {
      add_theme_support( 'post-thumbnails' );
    }
    add_post_type_support( LATEPOINT_AGENT_POST_TYPE, 'thumbnail' );
    add_post_type_support( LATEPOINT_SERVICE_POST_TYPE, 'thumbnail' );
    add_post_type_support( LATEPOINT_CUSTOMER_POST_TYPE, 'thumbnail' );
  }







  public function create_required_tables() {
    OsDatabaseHelper::run_setup();
  }



  /**
   * Register core post types.
   */
  public function register_post_types() {
  }



  public function add_facebook_sdk_js_code(){
    $facebook_app_id = OsSettingsHelper::get_settings_value('facebook_app_id');
    return "window.fbAsyncInit = function() {
              FB.init({
                appId      : '{$facebook_app_id}',
                cookie     : true,
                xfbml      : true,
                version    : 'v3.1'
              });
                
              FB.AppEvents.logPageView();
                
            };

            (function(d, s, id){
               var js, fjs = d.getElementsByTagName(s)[0];
               if (d.getElementById(id)) {return;}
               js = d.createElement(s); js.id = id;
               js.src = 'https://connect.facebook.net/en_US/sdk.js';
               fjs.parentNode.insertBefore(js, fjs);
             }(document, 'script', 'facebook-jssdk'));";

  }


  public function add_google_signin_meta_tags(){
    echo '<meta name="google-signin-client_id" content="'.OsSettingsHelper::get_settings_value('google_client_id').'">';
  }

  /**
  * Register scripts and styles - FRONT 
  */
  public function load_front_scripts_and_styles() {
    $localized_vars = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 
      'time_system' => OsTimeHelper::get_time_system(), 
      'msg_not_available' => __('Not Available', 'latepoint'), 
      'phone_format' => OsSettingsHelper::get_phone_format(),
      'enable_phone_masking' => OsUtilHelper::is_phone_formatting_disabled() ? 'no' : 'yes',
      'booking_button_route' => OsRouterHelper::build_route_name('bookings', 'steps'),
      'show_booking_end_time' => (OsSettingsHelper::get_settings_value('show_booking_end_time') == 'on') ? 'yes' : 'no',
      'stripe_key' => '',
      'braintree_key' => '',
      'braintree_tokenization_key' => '',
      'is_braintree_active' => false,
      'is_stripe_active' => false,
      'demo_mode' => OsSettingsHelper::is_env_demo(),
      'is_braintree_paypal_active' => false,
      'is_paypal_native_active' => false,
      'cancel_booking_prompt' => __('Estas seguro de cancelar la cita?', 'latepoint'),
      'body_font_family' => '-apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
    );

    // Stylesheets
    wp_enqueue_style( 'latepoint-main-front',   $this->public_stylesheets() . 'main_front.css', false, $this->version );
    // if(OsSettingsHelper::is_env_demo()) wp_enqueue_style( 'latepoint-fonts', '//fast.fonts.net/cssapi/cdae3b8a-b5bb-4b63-b153-bfa1831f29b5.css' );

    // Javscripts
    wp_enqueue_script( 'sprintf',                 $this->public_vendor_javascripts() . 'sprintf.min.js', [], $this->version );
    if(false == OsUtilHelper::is_phone_formatting_disabled()) wp_enqueue_script( 'jquery-mask',             $this->public_vendor_javascripts() . 'jquery.inputmask.bundle.min.js', ['jquery'], $this->version );

    // Addon scripts and styles
    do_action('latepoint_wp_enqueue_scripts');

    // PAYMENTS
    if(!OsSettingsHelper::is_env_demo()){
      // -- Stripe
      if(OsSettingsHelper::is_using_stripe_payments()){
        wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', false, null );
        $localized_vars['stripe_key'] = OsPaymentsStripeHelper::get_publishable_key();
        $localized_vars['is_stripe_active'] = true;
      }

      // -- Braintree
      if(OsSettingsHelper::is_using_braintree_payments()){
        wp_enqueue_script( 'braintree-client', 'https://js.braintreegateway.com/web/3.44.2/js/client.min.js', false, null );
        wp_enqueue_script( 'braintree-hosted-fields', 'https://js.braintreegateway.com/web/3.44.2/js/hosted-fields.min.js', false, null );
        $localized_vars['braintree_key'] = OsPaymentsBraintreeHelper::get_publishable_key();
        $localized_vars['braintree_tokenization_key'] = OsPaymentsBraintreeHelper::get_tokenization_key();
        $localized_vars['is_braintree_active'] = true;

        // PayPal Braintree
        if(OsSettingsHelper::is_using_paypal_braintree_payments()){
          wp_enqueue_script( 'braintree-checkout', 'https://www.paypalobjects.com/api/checkout.js', false, null );
          wp_enqueue_script( 'braintree-paypal-checkout', 'https://js.braintreegateway.com/web/3.44.2/js/paypal-checkout.min.js', false, null );
          $localized_vars['is_braintree_paypal_active'] = true;
          $localized_vars['paypal_payment_currency'] = OsSettingsHelper::get_braintree_currency_iso_code();
          $localized_vars['braintree_paypal_environment_name'] = OsPaymentsBraintreeHelper::get_environment_name();
          $localized_vars['braintree_paypal_client_auth'] = OsPaymentsBraintreeHelper::generate_client_token();
        }
      }

      // PayPal Native
      if(OsSettingsHelper::is_using_paypal_native_payments()){
          wp_enqueue_script( 'paypal-sdk', 'https://www.paypal.com/sdk/js?intent=authorize&commit=false&client-id='.OsSettingsHelper::get_settings_value('paypal_client_id'), false, null );
          $localized_vars['is_paypal_native_active'] = true;
      }
    }

    // Google Login
    if(OsSettingsHelper::is_using_google_login()) wp_enqueue_script( 'google-platform', 'https://apis.google.com/js/platform.js', false, null );

    wp_enqueue_script( 'latepoint-main-front',  $this->public_javascripts() . 'main_front.js', array('jquery', 'sprintf'), $this->version );

    if(OsSettingsHelper::is_using_facebook_login()) wp_add_inline_script( 'latepoint-main-front', $this->add_facebook_sdk_js_code());
    
    wp_localize_script( 'latepoint-main-front', 'latepoint_helper', $localized_vars );
  }

  public function add_admin_body_class( $classes ) {
    if((is_admin() || current_user_can('edit_bookings')) && isset($_GET['page']) && $_GET['page'] == 'latepoint'){
      $classes = $classes.' latepoint-admin latepoint';
    }
    return $classes;
  }

  public function add_body_class( $classes ) {
    $classes[] = 'latepoint';
    return $classes;
  }


  /**
  * Register admin scripts and styles - ADMIN
  */
  public function load_admin_scripts_and_styles() {
    // Stylesheets
    wp_enqueue_style( 'latepoint-main-back', $this->public_stylesheets() . 'main_back.css', false, $this->version );
    // if(OsSettingsHelper::is_env_demo()) wp_enqueue_style( 'latepoint-fonts', '//fast.fonts.net/cssapi/cdae3b8a-b5bb-4b63-b153-bfa1831f29b5.css' );

    // Javscripts
    wp_enqueue_media();


    wp_enqueue_script( 'sprintf',                 $this->public_vendor_javascripts() . 'sprintf.min.js', [], $this->version );
    wp_enqueue_script( 'dragula-js',              $this->public_vendor_javascripts() . 'dragula.min.js', [], $this->version );
    wp_enqueue_script( 'chart-js',                $this->public_vendor_javascripts() . 'Chart.min.js', [], $this->version );
    wp_enqueue_script( 'moment-js',               $this->public_vendor_javascripts() . 'moment.min.js', [], $this->version );
    wp_enqueue_script( 'jquery-mask',             $this->public_vendor_javascripts() . 'jquery.inputmask.bundle.min.js', ['jquery'], $this->version );
    wp_enqueue_script( 'daterangepicker',         $this->public_vendor_javascripts() . 'daterangepicker.min.js', ['moment-js'], $this->version );
    wp_enqueue_script( 'pickr-widget',            $this->public_vendor_javascripts() . 'pickr.min.js', [], $this->version );
    wp_enqueue_script( 'circles-js',              $this->public_javascripts() . 'circles.js', $this->version );
    wp_enqueue_script( 'latepoint-main-back',     $this->public_javascripts() . 'main_back.js', ['jquery', 'sprintf', 'dragula-js', 'chart-js', 'moment-js', 'jquery-mask', 'daterangepicker', 'pickr-widget', 'circles-js'], $this->version );

    do_action('latepoint_admin_enqueue_scripts');

    $localized_vars = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 
      'click_to_copy_done' => __('Copied', 'latepoint'),
      'click_to_copy_prompt' => __('Click to copy', 'latepoint'),
      'approve_confirm' => __('Are you sure you want to approve this booking?', 'latepoint'),
      'reject_confirm' => __('Are you sure you want to reject this booking?', 'latepoint'),
      'time_system' => OsTimeHelper::get_time_system(), 
      'msg_not_available' => __('Not Available', 'latepoint'), 
      'msg_addon_installed' => __('Installed', 'latepoint'), 
      'phone_format' => OsSettingsHelper::get_phone_format(),
      'enable_phone_masking' => OsUtilHelper::is_phone_formatting_disabled() ? 'no' : 'yes'  );


    $localized_vars = apply_filters('latepoint_localized_vars_admin', $localized_vars);

    wp_localize_script( 'latepoint-main-back', 'latepoint_helper', $localized_vars );

  }

}
endif;


$LATEPOINT = new LatePoint();
