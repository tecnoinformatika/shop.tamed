<?php

class OsBookingModel extends OsModel{
  var $id,
      $service_id,
      $customer_id,
      $agent_id,
      $location_id,
      $buffer_before,
      $buffer_after,
      $status,
      $start_date,
      $end_date,
      $start_time,
      $end_time,
      $payment_method,
      $payment_portion,
      $payment_token,
      $coupon_code,
      $updated_at,
      $created_at;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_BOOKINGS;
    $this->nice_names = array('service_id' => __('Service', 'latepoint'), 
                              'agent_id' => __('Agent', 'latepoint'));

    if($id){
      $this->load_by_id($id);
    }
  }

  public function delete_meta_by_key($meta_key){
    if($this->is_new_record()) return true;

    $meta = new OsBookingMetaModel();
    return $meta->delete_by_key($meta_key, $this->id);
  }

  public function get_payment_method_nice_name(){
    $payment_methods = [
      LATEPOINT_PAYMENT_METHOD_LOCAL => __('Local Payment', 'latepoint'),
      LATEPOINT_PAYMENT_METHOD_PAYPAL => __('PayPal', 'latepoint'),
      LATEPOINT_PAYMENT_METHOD_CARD => __('Credit/Debit Card', 'latepoint')
    ];
    $nice_name = (!empty($this->payment_method) && isset($payment_methods[$this->payment_method])) ? $payment_methods[$this->payment_method] : __('n/a', 'latepoint');
    return $nice_name;
  }

  public function get_ical_download_link(){
    return OsRouterHelper::build_admin_post_link(['bookings', 'ical_download'], ['latepoint_booking_id' => $this->id]);
  }

  public function get_meta_by_key($meta_key, $default = false){
    if($this->is_new_record()) return $default;

    $meta = new OsBookingMetaModel();
    return $meta->get_by_key($meta_key, $this->id, $default);
  }

  public function save_meta_by_key($meta_key, $meta_value){
    if($this->is_new_record()) return false;

    $meta = new OsBookingMetaModel();
    return $meta->save_by_key($meta_key, $meta_value, $this->id);
  }

  public function calculate_end_time(){
    return $this->start_time + $this->get_duration();
  }

  public function get_duration(){
    if(!empty($this->end_time)){
      return $this->end_time - $this->start_time;
    }else{
      return $this->service->duration;
    }
  }

  public function get_start_timestamp(){
    return $this->format_start_date_and_time('U');
  }

  public function get_nice_created_at(){
    return date_format(date_create_from_format('Y-m-d H:i:s', $this->created_at), 'M j, Y');
  }


  // Saves booking from the booking form on the frontend
  public function save_from_booking_form(){
    if($this->service_id && $this->agent_id && $this->customer_id){
      $service = new OsServiceModel($this->service_id);

      if($this->agent_id == LATEPOINT_ANY_AGENT){
        $this->agent_id = OsBookingHelper::get_any_agent_for_booking_by_rule($this);
        if(!$this->agent_id){
          $this->add_error('no_agents_available', __('There are no available agents for selected time block.', 'latepoint'));
          return false;
        }
      }

      $this->end_date = $this->start_date;
      $this->end_time = $this->start_time + $service->duration;
      $this->buffer_before = $service->buffer_before;
      $this->buffer_after = $service->buffer_after;

      $was_new = $this->is_new_record();

      // IS PAYMENT REQUIRED?
      if(($this->payment_method == LATEPOINT_PAYMENT_METHOD_CARD || $this->payment_method == LATEPOINT_PAYMENT_METHOD_PAYPAL) && !OsSettingsHelper::is_env_demo()){
        if(!empty($this->payment_token)){

          $result = OsPaymentsHelper::process_payment_by_token($this->payment_token, $this);
          if($result['status'] == LATEPOINT_STATUS_SUCCESS){
            $transaction = new OsTransactionModel();
            $transaction->customer_id = $this->customer_id;
            $transaction->token = $result['charge_id'];
            $transaction->payment_method = $this->payment_method;
            $transaction->amount = $this->amount_to_charge();
            $transaction->processor = OsPaymentsHelper::get_processor_name($transaction->payment_method);
            $transaction->status = LATEPOINT_TRANSACTION_STATUS_APPROVED;
          }else{
            $this->add_error('payment_error', $result['message']);
            return false;
          }
        }else{
          $this->add_error('missing_payment_token', __('Payment Token Missing', 'latepoint'));
          return false;
        }
      }else{
        $transaction = false;
      }

      apply_filters('latepoint_before_booking_save_frontend', $this);

      if($this->save()){
        if($transaction){
          $transaction->booking_id = $this->id;
          $transaction->save();
        }
        if($was_new){
          OsNotificationsHelper::process_new_booking_notifications($this);
          OsActivitiesHelper::create_activity(array('code' => 'booking_create', 'booking' => $this));
          do_action('latepoint_booking_created_frontend', $this);
        }else{
          OsNotificationsHelper::process_update_booking_notifications($this);
          OsActivitiesHelper::create_activity(array('code' => 'booking_update', 'booking' => $this));
          do_action('latepoint_booking_updated_frontend', $this);
        }
        return true;
      }else{
        return false;
      }
    }else{
      return false;
    }
  }

  public function get_nice_status(){
    return OsBookingHelper::get_nice_status_name($this->status);
  }

  public function get_latest_bookings_sorted_by_status($args = array()){
    $args = array_merge(array('service_id' => false, 'customer_id' => false, 'agent_id' => false, 'location_id' => false, 'limit' => false, 'offset' => false), $args);

    $bookings = new OsBookingModel();
    $query_args = array();
    if($args['service_id']) $query_args['service_id'] = $args['service_id'];
    if($args['customer_id']) $query_args['customer_id'] = $args['customer_id'];
    if($args['agent_id']) $query_args['agent_id'] = $args['agent_id'];
    if($args['location_id']) $query_args['location_id'] = $args['location_id'];
    if($args['limit']) $bookings->set_limit($args['limit']);
    if($args['offset']) $bookings->set_offset($args['offset']);

    return $bookings->where($query_args)->should_not_be_cancelled()->order_by("status != '".LATEPOINT_BOOKING_STATUS_PENDING."' asc, start_date asc, start_time asc")->get_results_as_models();

  }

  
  public function should_not_be_cancelled(){
    return $this->where(['status !=' => LATEPOINT_BOOKING_STATUS_CANCELLED]);
  }

  public function should_be_active(){
    return $this->where(['status' => LATEPOINT_BOOKING_STATUS_APPROVED]);
  }

  public function should_be_in_future(){
    return $this->where(['OR' => ['start_date >' => OsTimeHelper::today_date('Y-m-d'), 
                                                'AND' => ['start_date' => OsTimeHelper::today_date('Y-m-d'),
                                                               'start_time >' => OsTimeHelper::get_current_minutes()]]]);
  }

  public function is_active(){
    return ($this->status == LATEPOINT_BOOKING_STATUS_APPROVED);
  }

  public function get_upcoming_bookings($agent_id = false, $customer_id = false, $service_id = false, $location_id = false, $limit = 3){
    $bookings = new OsBookingModel();
    $args = array('OR' => array('start_date >' => OsTimeHelper::today_date('Y-m-d'), 
                                'AND' => array('start_date' => OsTimeHelper::today_date('Y-m-d'),
                                               'start_time >' => OsTimeHelper::get_current_minutes())));
    if($service_id) $args['service_id'] = $service_id;
    if($customer_id) $args['customer_id'] = $customer_id;
    if($agent_id) $args['agent_id'] = $agent_id;
    if($location_id) $args['location_id'] = $location_id;
    return $bookings->should_be_active()->where($args)->set_limit($limit)->order_by('start_date asc, start_time asc')->get_results_as_models();

  }

  protected function get_nice_start_time(){
    return OsTimeHelper::minutes_to_hours_and_minutes($this->start_time);
  }

  protected function get_nice_end_time(){
    return OsTimeHelper::minutes_to_hours_and_minutes($this->end_time);
  }


  protected function get_nice_start_date(){
    $d = OsWpDateTime::os_createFromFormat("Y-m-d", $this->start_date);
    return $d->format("M j, Y");
  }

  protected function get_nice_start_date_no_year(){
    $d = OsWpDateTime::os_createFromFormat("Y-m-d", $this->start_date);
    if($d->format('Y') == OsTimeHelper::today_date('Y')){
      return $d->format("F j");
    }else{
      return $d->format("F j, Y");
    }
  }

  public function format_end_date_and_time($format = "Y-m-d H:i:s"){
    $date = OsWpDateTime::os_createFromFormat("Y-m-d H:i:s", $this->end_date.' '.OsTimeHelper::minutes_to_army_hours_and_minutes($this->end_time).':00');
    return $date->format($format);
  }

  public function format_start_date_and_time($format = "Y-m-d H:i:s"){
    $date = OsWpDateTime::os_createFromFormat("Y-m-d H:i:s", $this->start_date.' '.OsTimeHelper::minutes_to_army_hours_and_minutes($this->start_time).':00');
    return $date->format($format);
  }

  public function format_start_date_and_time_for_google(){
    return $this->format_start_date_and_time(\DateTime::RFC3339);
  }

  public function format_end_date_and_time_for_google(){
    return $this->format_end_date_and_time(\DateTime::RFC3339);
  }

  protected function get_time_left(){
    $now_datetime = new OsWpDateTime('now');
    $booking_datetime = OsWpDateTime::os_createFromFormat("Y-m-d H:i:s", $this->format_start_date_and_time());
    $css_class = 'left-days';

    if($booking_datetime){
      $diff = $now_datetime->diff($booking_datetime);
      if($diff->d > 0){
        $left = $diff->format('%a days');
      }else{
        if($diff->h > 0){
          $css_class = 'left-hours';
          $left = $diff->format('%h hours');
        }else{
          $css_class = 'left-minutes';
          $left = $diff->format('%i minutes');
        }
      }
    }else{
      $left = 'n/a';
    }

    return '<span class="time-left '.$css_class.'">'.$left.'</span>';
  }


  protected function get_nice_start_date_time(){
    if($this->start_date == OsTimeHelper::today_date('Y-m-d')){
      $date = __('Today', 'latepoint');
    }else{
      $date = $this->nice_start_date_no_year;
    }
    return $date.', '.$this->nice_start_time;
  }

  protected function get_nice_end_date_time(){
    if($this->start_date == OsTimeHelper::today_date('Y-m-d')){
      $date = __('Today', 'latepoint');
    }else{
      $date = $this->nice_start_date_no_year;
    }
    return $date.', '.$this->nice_end_time;
  }

  protected function get_agent(){
    if($this->agent_id){
      if(!isset($this->agent) || (isset($this->agent) && ($this->agent->id != $this->agent_id))){
        $this->agent = new OsAgentModel($this->agent_id);
      }
    }else{
      $this->agent = new OsAgentModel();
    }
    return $this->agent;
  }

  public function get_agent_full_name(){
    if($this->agent_id == LATEPOINT_ANY_AGENT){
      return __('Any Available Agent', 'latepoint');
    }else{
      return $this->agent->full_name;
    }
  }


  protected function get_location(){
    if($this->location_id){
      if(!isset($this->location) || (isset($this->location) && ($this->location->id != $this->location_id))){
        $this->location = new OsLocationModel($this->location_id);
      }
    }else{
      $this->location = new OsLocationModel();
    }
    return $this->location;
  }

  protected function get_customer(){
    if($this->customer_id){
      if(!isset($this->customer) || (isset($this->customer) && ($this->customer->id != $this->customer_id))){
        $this->customer = new OsCustomerModel($this->customer_id);
      }
    }else{
      $this->customer = new OsCustomerModel();
    }
    return $this->customer;
  }


  protected function get_service(){
    if($this->service_id){
      if(!isset($this->service) || (isset($this->service) && ($this->service->id != $this->service_id))){
        $this->service = new OsServiceModel($this->service_id);
      }
    }else{
      $this->service = new OsServiceModel();
    }
    return $this->service;
  }




  public function generate_datetime(){
    $dateTime = new OsWpDateTime($this->start_date . ' 00:00:00');
    $dateTime->modify('+'. $this->start_time .' minutes');

    $this->start_date = $dateTime->format('Y-m-d H:i:s');
  }


  protected function before_save(){
    $this->end_date = $this->start_date;
    if(empty($this->payment_method)) $this->payment_method = LATEPOINT_PAYMENT_METHOD_LOCAL;
    if(empty($this->status)) $this->status = $this->get_default_status();
    if(empty($this->ip_address)) $this->ip_address = $_SERVER['REMOTE_ADDR'];
  }

  public function get_default_status(){
    return OsBookingHelper::get_default_booking_status();
  }

  public function update_status($new_status){
    return $this->update_attributes(array('status' => $new_status));
  }

  public function save_avatar($image_id = false){
    if((false === $image_id) && $this->image_id) $image_id = $this->image_id;
    if($image_id && $this->post_id){
      set_post_thumbnail($this->post_id, $image_id);
      $this->image_id = $image_id;
    }
    return $this->image_id;
  }
  
  public function can_pay_deposit_and_pay_full(){
    return (($this->full_amount_to_charge() > 0) && ($this->deposit_amount_to_charge() > 0));
  }

  public function can_pay_deposit(){
    return ($this->deposit_amount_to_charge() > 0);
  }

  public function can_pay_full(){
    return ($this->full_amount_to_charge() > 0);
  }

  public function specs_calculate_price_to_charge($payment_method = false){
    if($this->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT){
      return $this->specs_calculate_deposit_price_to_charge($payment_method = false);
    }else{
      return $this->specs_calculate_full_price_to_charge($payment_method = false);
    }
  }

  public function amount_to_charge(){
    if($this->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT){
      return $this->deposit_amount_to_charge();
    }else{
      return $this->full_amount_to_charge();
    }
  }

  public function full_amount_to_charge($apply_filters = true){
    return OsMoneyHelper::calculate_full_amount_to_charge($this, $apply_filters);
  }

  public function deposit_amount_to_charge($apply_filters = true){
    return OsMoneyHelper::calculate_deposit_amount_to_charge($this, $apply_filters);
  }


  public function specs_calculate_full_price_to_charge($payment_method = false){
    if(!$payment_method) $payment_method = $this->payment_method;
    return OsPaymentsHelper::convert_charge_amount_to_requirements($this->full_amount_to_charge(), $payment_method);
  }

  public function specs_calculate_deposit_price_to_charge($payment_method = false){
    if(!$payment_method) $payment_method = $this->payment_method;
    return OsPaymentsHelper::convert_charge_amount_to_requirements($this->deposit_amount_to_charge(), $payment_method);
  }

  public function formatted_full_price($apply_filters = true){
    return OsMoneyHelper::format_price($this->full_amount_to_charge($apply_filters));
  }

  public function formatted_deposit_price($apply_filters = true){
    return OsMoneyHelper::format_price($this->deposit_amount_to_charge($apply_filters));
  }


  protected function allowed_params($role = 'admin'){
    $allowed_params = array('service_id',
                            'agent_id',
                            'customer_id',
                            'location_id',
                            'start_date',
                            'end_date',
                            'start_time',
                            'end_time',
                            'payment_method',
                            'payment_portion',
                            'payment_token',
                            'buffer_before',
                            'buffer_after',
                            'coupon_code',
                            'status');
    return $allowed_params;
  }


  protected function params_to_save($role = 'admin'){
    $params_to_save = array('service_id',
                            'agent_id',
                            'customer_id',
                            'location_id',
                            'start_date',
                            'end_date',
                            'start_time',
                            'end_time',
                            'payment_method',
                            'payment_portion',
                            'buffer_before',
                            'buffer_after',
                            'coupon_code',
                            'status');
    return $params_to_save;
  }


  protected function properties_to_validate(){
    $validations = array(
      'service_id' => array('presence'),
      'agent_id' => array('presence'),
      'location_id' => array('presence'),
      'customer_id' => array('presence'),
      'start_date' => array('presence'),
      'end_date' => array('presence'),
      'status' => array('presence'),
    );
    return $validations;
  }
}