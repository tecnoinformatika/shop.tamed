<?php 

class OsSettingsHelper {
  
  public $time_system;
  public $currency_symbol_before;
  public $currency_symbol_after;

  static $timeblock_interval;

  private static $encrypted_settings = ['license', 
                                        'google_calendar_client_secret', 
                                        'facebook_app_secret', 
                                        'google_client_secret', 
                                        'notifications_sms_twilio_auth_token', 
                                        'stripe_secret_key', 
                                        'braintree_secret_key', 
                                        'braintree_merchant_id',
                                        'paypal_client_secret'];

  function __construct(){
    $this->time_system = OsSettingsHelper::get_settings_value('time_system', LATEPOINT_DEFAULT_TIME_SYSTEM);
    $this->currency_symbol_before = OsSettingsHelper::get_settings_value('currency_symbol_before', '$');
    $this->currency_symbol_after = OsSettingsHelper::get_settings_value('currency_symbol_after');
    $this->disable_phone_formatting = OsSettingsHelper::get_settings_value('disable_phone_formatting', 'off');
  }


  // ENVIRONMENT SETTINGS

  // BASE ENVIRONMENT
  public static function is_env_live(){
    return (LATEPOINT_ENV == LATEPOINT_ENV_LIVE);
  }

  public static function is_env_dev(){
    return (LATEPOINT_ENV == LATEPOINT_ENV_DEV);
  }

  public static function is_env_demo(){
    return (LATEPOINT_ENV == LATEPOINT_ENV_DEMO);
  }

  // SMS, EMAILS

  public static function is_sms_allowed(){
    return LATEPOINT_ALLOW_SMS;
  }

  public static function is_email_allowed(){
    return LATEPOINT_ALLOW_EMAILS;
  }

  // PAYMENTS ENVIRONMENT
  public static function is_env_payments_live(){
    return (self::get_payments_environment() == LATEPOINT_ENV_LIVE);
  }

  public static function is_env_payments_dev(){
    return (self::get_payments_environment() == LATEPOINT_ENV_DEV);
  }

  public static function is_env_payments_demo(){
    return (self::get_payments_environment() == LATEPOINT_ENV_DEMO);
  }

  public static function get_payments_environment(){
    return OsSettingsHelper::get_settings_value('payments_environment', LATEPOINT_ENV_LIVE);
  }



  public static function is_accepting_payments(){
    return OsSettingsHelper::is_on('enable_payments');
  }

  public static function is_accepting_payments_cards(){
    return OsSettingsHelper::is_on('enable_payments_cc');
  }

  public static function is_accepting_payments_paypal(){
    return OsSettingsHelper::is_on('enable_payments_paypal');
  }

  public static function is_accepting_payments_local(){
    return OsSettingsHelper::is_on('enable_payments_local');
  }

  public static function get_payment_methods(){
    $payment_methods = [];
    if(self::is_accepting_payments()){
      if(self::is_accepting_payments_cards()) $payment_methods[] = 'cards';
      if(self::is_accepting_payments_paypal()) $payment_methods[] = 'paypal';
      if(self::is_accepting_payments_local()) $payment_methods[] = 'local';
    }
    return $payment_methods;
  }

  public static function can_process_payments(){
    return (self::is_using_stripe_payments() || self::is_using_braintree_payments());
  }

  public static function is_using_stripe_payments(){
    return (OsSettingsHelper::get_settings_value('enable_payments_stripe') == 'on');
  }

  public static function is_using_braintree_payments(){
    return (OsSettingsHelper::get_settings_value('enable_payments_braintree') == 'on');
  }

  public static function is_using_paypal_braintree_payments(){
    return (OsSettingsHelper::is_on('enable_payments_braintree') && OsSettingsHelper::is_on('enable_payments_paypal') && OsSettingsHelper::is_on('paypal_use_braintree_api'));
  }

  public static function is_using_paypal_native_payments(){
    return (OsSettingsHelper::is_on('enable_payments_paypal') && !OsSettingsHelper::is_on('paypal_use_braintree_api'));
  }

  public static function is_sms_processor_setup(){
    $phone = OsSettingsHelper::get_settings_value('notifications_sms_twilio_phone');
    $account_id = OsSettingsHelper::get_settings_value('notifications_sms_twilio_account_sid');
    $auth_token = OsSettingsHelper::get_settings_value('notifications_sms_twilio_auth_token');
    return (!empty($phone) && !empty($account_id) && !empty($auth_token));
  }

  public static function is_sms_notifications_enabled(){
    return (OsSettingsHelper::get_settings_value('notifications_sms') == 'on');
  }

  public static function is_using_google_login(){
    return (OsSettingsHelper::get_settings_value('enable_google_login') == 'on');
  }

  public static function is_using_facebook_login(){
    return (OsSettingsHelper::get_settings_value('enable_facebook_login') == 'on');
  }

  public static function get_steps_support_text(){
    $default = '<h5>Questions?</h5><p>Call (858) 939-3746 for help</p>';
    return OsSettingsHelper::get_settings_value('steps_support_text', $default);
  }

  public static function save_setting_by_name($name, $value){
    $settings_model = new OsSettingsModel();
    $settings_model = $settings_model->where(array('name' => $name))->set_limit(1)->get_results_as_models();
    if($settings_model){
      $settings_model->value = self::prepare_value($name, $value);
    }else{
      $settings_model = new OsSettingsModel();
      $settings_model->name = $name;
      $settings_model->value = self::prepare_value($name, $value);
    }
    return $settings_model->save();
  }

  public static function prepare_value($name, $value){
    if(in_array($name, self::$encrypted_settings)){
      $value = OsEncryptHelper::encrypt_value($value);
    }
    return $value;
  }

  public static function get_settings_value($name, $default = false){
    $settings_model = new OsSettingsModel();
    $settings_model = $settings_model->where(array('name' => $name))->set_limit(1)->get_results_as_models();
    if($settings_model){
      if(in_array($name, self::$encrypted_settings)){
        return OsEncryptHelper::decrypt_value($settings_model->value);
      }else{
        return $settings_model->value;
      }
    }else{
      return $default;
    }
  }

  public static function get_stripe_currency_iso_code(){
    return OsSettingsHelper::get_settings_value('stripe_currency_iso_code', LATEPOINT_DEFAULT_STRIPE_CURRENCY_ISO_CODE);
  }

  public static function get_braintree_currency_iso_code(){
    return OsSettingsHelper::get_settings_value('braintree_currency_iso_code', LATEPOINT_DEFAULT_BRAINTREE_CURRENCY_ISO_CODE);
  }

  public static function get_any_agent_order(){
    return OsSettingsHelper::get_settings_value('any_agent_order', LATEPOINT_ANY_AGENT_ORDER_RANDOM);
  }

  public static function get_day_calendar_min_height(){
    return OsSettingsHelper::get_settings_value('day_calendar_min_height', 700);
  }

  public static function get_phone_format(){
    $interval = LATEPOINT_DEFAULT_PHONE_FORMAT;
    $settings_value = OsSettingsHelper::get_settings_value('phone_format');
    if($settings_value) $interval = $settings_value;
    return $interval;
  }

  public static function get_timeblock_interval(){
    if(self::$timeblock_interval) return self::$timeblock_interval;
    $interval = LATEPOINT_DEFAULT_TIMEBLOCK_INTERVAL;
    $settings_value = OsSettingsHelper::get_settings_value('timeblock_interval');
    if($settings_value) $interval = $settings_value;
    self::$timeblock_interval = $interval;
    return $interval;
  }

  public static function get_country_phone_code(){
  	$phone_code = LATEPOINT_DEFAULT_PHONE_CODE;
  	$settings_value = OsSettingsHelper::get_settings_value('country_phone_code');
  	if($settings_value) $phone_code = $settings_value;
  	return $phone_code;
  }

  public static function get_customer_dashboard_url(){
    return OsSettingsHelper::get_settings_value('page_url_customer_dashboard', '/customer-dashboard');
  }

  public static function get_customer_login_url(){
    return OsSettingsHelper::get_settings_value('page_url_customer_login', '/customer-login');
  }


  // BOOKING STEPS

  public static function steps_show_service_categories(){
    return (OsSettingsHelper::get_settings_value('steps_show_service_categories') == 'on');
  }

  public static function steps_show_agent_bio(){
    return (OsSettingsHelper::get_settings_value('steps_show_agent_bio') == 'on');
  }
  
  public static function get_booking_form_color_scheme(){
    return OsSettingsHelper::get_settings_value('color_scheme_for_booking_form', 'blue');
  }


  public static function is_on($setting){
    return (OsSettingsHelper::get_settings_value($setting) == 'on');
  }


}

?>