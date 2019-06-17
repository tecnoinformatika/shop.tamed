<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsBookingsController' ) ) :


  class OsBookingsController extends OsController {

    private $booking;

    function __construct(){
      parent::__construct();

      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'bookings/';
      $this->vars['page_header'] = __('Citas', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Citas', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('citas', 'pending_approval') ) );
    }



    public function pending_approval(){
      $this->vars['page_header'] = __('Citas pendientes', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Citas pendientes', 'latepoint'), 'link' => false );

      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = 20;
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;

      $bookings = new OsBookingModel();
      $query_args = ['location_id' => OsLocationHelper::get_selected_location_id(), 'status' => [LATEPOINT_BOOKING_STATUS_PENDING, LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING]];

      if($this->logged_in_agent_id) $query_args['agent_id'] = $this->logged_in_agent_id;
      $this->vars['bookings'] = $bookings->where($query_args)->set_limit($per_page)->set_offset($offset)->order_by('id desc')->get_results_as_models();

      $count_bookings = new OsBookingModel();
      $total_bookings = $count_bookings->where($query_args)->count();
      $total_pages = ceil($total_bookings / $per_page);

      $this->vars['total_pages'] = $total_pages;
      $this->vars['total_bookings'] = $total_bookings;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;

      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_bookings']);

      $this->format_render(__FUNCTION__);
    }


    public function index(){
      
      $this->vars['page_header'] = __('Citas', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Todo', 'latepoint'), 'link' => false );

      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = 20;
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;


      $bookings = new OsBookingModel();
      $query_args = ['location_id' => OsLocationHelper::get_selected_location_id()];

      if($this->logged_in_agent_id) $query_args['agent_id'] = $this->logged_in_agent_id;
      $filter = isset($this->params['filter']) ? $this->params['filter'] : false;

      // TABLE SEARCH FILTERS
      if($filter){
        if($filter['service_id']) $query_args['service_id'] = $filter['service_id'];
        if($filter['agent_id']) $query_args['agent_id'] = $filter['agent_id'];
        if($filter['status']) $query_args[LATEPOINT_TABLE_BOOKINGS.'.status'] = $filter['status'];
        if($filter['id']) $query_args['id'] = $filter['id'];
        if($filter['created_date_from']){
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.created_at >='] = $filter['created_date_from'].' 00:00:00';
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.created_at <='] = $filter['created_date_from'].' 23:59:59';
        }
        if($filter['booking_date_from'] && $filter['booking_date_to']){
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.start_date >='] = $filter['booking_date_from'];
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.start_date <='] = $filter['booking_date_to'];
        }
        if($filter['customer']){
          $bookings->select(LATEPOINT_TABLE_BOOKINGS.'.*, '.LATEPOINT_TABLE_CUSTOMERS.'.first_name, '.LATEPOINT_TABLE_CUSTOMERS.'.last_name');
          $bookings->join(LATEPOINT_TABLE_CUSTOMERS, [LATEPOINT_TABLE_CUSTOMERS.'.id' => 'customer_id']);
          $query_args['CONCAT('.LATEPOINT_TABLE_CUSTOMERS.'.first_name, " " ,'.LATEPOINT_TABLE_CUSTOMERS.'.last_name) LIKE'] = '%'.$filter['customer'].'%';
          $this->vars['customer_name_query'] = $filter['customer'];
        }
      }

      if($this->logged_in_agent_id){
        $query_args['agent_id'] = $this->logged_in_agent_id;
        $this->vars['show_single_agent'] = $this->logged_in_agent;
      }else{
        $this->vars['show_single_agent'] = false;
      }

      // OUTPUT CSV IF REQUESTED
      if(isset($this->params['download']) && $this->params['download'] == 'csv'){
        $csv_filename = 'all_bookings_'.OsUtilHelper::random_text().'.csv';
        
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$csv_filename}.csv");

        $labels_row = [  __('ID', 'latepoint'), 
                              __('Servicio', 'latepoint'), 
                              __('Fecha y hora de inicio', 'latepoint'), 
                              __('Duración', 'latepoint'), 
                              __('Cliente', 'latepoint'), 
                              __('Telefono del cliente', 'latepoint'), 
                              __('Email del cliente', 'latepoint'), 
                              __('Técnico', 'latepoint'), 
                              __('Telefono del técnico', 'latepoint'), 
                              __('Email del técnico', 'latepoint'), 
                              __('Estado', 'latepoint'), 
                              __('Reservado en', 'latepoint') ];


        $custom_fields_for_customer = OsCustomFieldsHelper::get_custom_fields_arr('customer');
        foreach($custom_fields_for_customer as $custom_field){
          $labels_row[] = $custom_field['label'];
        }

        $bookings_data = [];
        $bookings_data[] = $labels_row;


        $bookings_arr = $bookings->where($query_args)->order_by('id desc')->get_results_as_models();                              
        if($bookings_arr){
          foreach($bookings_arr as $booking){
            $values_row = [  $booking->id, 
                                  $booking->service->name, 
                                  $booking->nice_start_date_time, 
                                  $booking->duration, 
                                  $booking->customer->full_name, 
                                  $booking->customer->phone, 
                                  $booking->customer->email, 
                                  $booking->agent->full_name, 
                                  $booking->agent->phone, 
                                  $booking->agent->email, 
                                  $booking->nice_status, 
                                  $booking->nice_created_at];
            foreach($custom_fields_for_customer as $custom_field){
              $values_row[] = $booking->customer->get_meta_by_key($custom_field['id'], '');
            }
            $bookings_data[] = $values_row;
          }

        }
        OsCSVHelper::array_to_csv($bookings_data);
        return;
      }

      $this->vars['bookings'] = $bookings->where($query_args)->set_limit($per_page)->set_offset($offset)->order_by('id desc')->get_results_as_models();

      $count_total_bookings = new OsBookingModel();
      if($filter['customer']){
        $count_total_bookings->join(LATEPOINT_TABLE_CUSTOMERS, [LATEPOINT_TABLE_CUSTOMERS.'.id' => 'customer_id']);
      }
      $total_bookings = $count_total_bookings->where($query_args)->count();
      $this->vars['total_bookings'] = $total_bookings;
      $total_pages = ceil($total_bookings / $per_page);

      $this->vars['total_pages'] = $total_pages;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;
      
      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_bookings']);

      $this->format_render(['json_view_name' => '_table_body', 'html_view_name' => __FUNCTION__], [], ['total_pages' => $total_pages, 'showing_from' => $this->vars['showing_from'], 'showing_to' => $this->vars['showing_to'], 'total_records' => $total_bookings]);
    }

    function quick_availability(){
      $agent_id = $this->params['agent_id'];
      $service_id = $this->params['service_id'];
      $location_id = isset($this->params['location_id']) ? $this->params['location_id'] : OsLocationHelper::get_selected_location_id();

      $date_string = isset($this->params['start_date']) ? $this->params['start_date'] : 'today';
      $start_date_obj = new OsWpDateTime($date_string);
      $start_date = $start_date_obj->format('Y-m-d');
      

      if($this->logged_in_agent_id) $agent_id = $this->logged_in_agent_id;

      $selected_agent = new OsAgentModel($agent_id);
      $selected_service = new OsServiceModel($service_id);
      $selected_location = new OsLocationModel($location_id);

      $this->vars['selected_agent'] = $selected_agent;
      $this->vars['selected_service'] = $selected_service;
      $this->vars['selected_location'] = $selected_location;

      $work_periods_arr = OsBookingHelper::get_work_periods(['agent_id' => $selected_agent->id, 'service_id' => $selected_service->id, 'location_id' => $selected_location->id]);
      $work_start_end = OsBookingHelper::get_work_start_end_time($work_periods_arr);

      $this->vars['work_start_end'] = $work_start_end;
      $this->vars['show_days_only'] = isset($this->params['show_days_only']) ? true : false;
      
      $this->vars['timeblock_interval'] = OsSettingsHelper::get_timeblock_interval();
      $this->vars['days_availability_html'] = OsBookingHelper::get_quick_availability_days($start_date, $selected_agent, $selected_service, $selected_location, $work_start_end, 30 );
      $this->vars['start_date'] = $start_date;
      $this->vars['end_date'] = $start_date_obj->modify('+30 days')->format('Y-m-d');

      $agents = new OsAgentModel();
      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);
      $this->vars['agents'] = $agents->get_results_as_models();

      $this->format_render(__FUNCTION__);
    }


    function request_cancellation(){
      $booking_id = $this->params['id'];
      $booking = new OsBookingModel($booking_id);
      if(OsAuthHelper::get_logged_in_customer_id() == $booking->customer_id){
        $this->params['status'] = LATEPOINT_BOOKING_STATUS_CANCELLED;
        $this->change_status();
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error! JSf29834', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    function change_status(){
      $booking_id = $this->params['id'];
      $new_status = $this->params['status'];
      $booking = new OsBookingModel($booking_id);
      $old_status = $booking->status;
      $old_status_nice = $booking->nice_status;
      $booking->status = $new_status;
      if($new_status == $old_status){
        $response_html = __('Appointment Status Updated', 'latepoint');
        $status = LATEPOINT_STATUS_SUCCESS;
      }else{
        if($booking->save()){
          $response_html = __('Appointment Status Updated', 'latepoint');
          $status = LATEPOINT_STATUS_SUCCESS;
          OsNotificationsHelper::process_booking_status_changed_notifications($booking, $old_status_nice);
          do_action('latepoint_booking_updated_admin', $booking);
          do_action('latepoint_booking_status_changed', $booking, $old_status);
          OsActivitiesHelper::create_activity(array('code' => 'booking_change_status', 'booking' => $booking, 'old_value' => $old_status));
        }else{
          $response_html = $booking->get_error_messages();
          $status = LATEPOINT_STATUS_ERROR;
        }
      }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


    function ical_download(){
      $booking_id = $this->params['latepoint_booking_id'];
      if($booking_id){
        $booking = new OsBookingModel($booking_id);
        if($booking->id && OsAuthHelper::is_customer_logged_in() && ($booking->customer_id == OsAuthHelper::get_logged_in_customer_id())){

          header('Content-Type: text/calendar; charset=utf-8');
          header('Content-Disposition: attachment; filename=booking_'.$booking->id.'.ics');

          $booking_description = sprintf(__('Appointment with %s for %s', 'latepoint'), $booking->agent->full_name, $booking->service->name);

          $ics = new ICS(array(
            'location' => $booking->location->full_address,
            'description' => '',
            'dtstart' => $booking->nice_start_date_time,
            'dtend' => $booking->nice_end_date_time,
            'summary' => $booking_description,
            'url' => get_site_url()
          ));

          echo $ics->to_string();
        }
      }
    }


    private function update_formatted_time_params(){
      if(isset($this->params['booking']['start_time']['formatted_value'])){
        $start_ampm = isset($this->params['booking']['start_time']['ampm']) ? $this->params['booking']['start_time']['ampm'] : false;
        $end_ampm = isset($this->params['booking']['end_time']['ampm']) ? $this->params['booking']['end_time']['ampm'] : false;
        $this->params['booking']['start_time'] = OsTimeHelper::convert_time_to_minutes($this->params['booking']['start_time']['formatted_value'], $start_ampm);
        $this->params['booking']['end_time'] = OsTimeHelper::convert_time_to_minutes($this->params['booking']['end_time']['formatted_value'], $end_ampm);
      }
    }

    /*
      Create booking (used in admin on quick side form save)
    */

    public function create(){
      if($this->params['booking']['id']){
        $this->update();
        return;
      }
      $form_values_to_update = array();
      $this->update_formatted_time_params();
      
      $customer_params = $this->params['customer'];
      $booking_params = $this->params['booking'];
      $custom_fields_data = isset($customer_params['custom_fields']) ? $customer_params['custom_fields'] : [];

      $booking = new OsBookingModel();
      $booking->set_data($booking_params);

      // Customer update/create
      if($booking->customer_id){
        $customer = new OsCustomerModel($booking->customer_id);
        $is_new_customer = false;
      }else{
        $customer = new OsCustomerModel();
        $is_new_customer = true;
      }
      $customer->set_data($customer_params);
      if($customer->validate_custom_fields($custom_fields_data) && $customer->save()){
        $customer->save_custom_fields($custom_fields_data);
        if($is_new_customer){
          OsNotificationsHelper::process_new_customer_notifications($customer);
          OsActivitiesHelper::create_activity(array('code' => 'customer_create', 'customer_id' => $customer->id));
        }

        $booking->customer_id = $customer->id;
        $form_values_to_update['booking[customer_id]'] = $booking->customer_id;
        if($booking->save()){
          $form_values_to_update['booking[id]'] = $booking->id;
          $response_html = __('Appointment Added: ID#', 'latepoint') . $booking->id;
          $status = LATEPOINT_STATUS_SUCCESS;
          do_action('latepoint_booking_created_admin', $booking);
          OsNotificationsHelper::process_new_booking_notifications($booking);
          OsActivitiesHelper::create_activity(array('code' => 'booking_create', 'booking' => $booking));
        }else{
          $response_html = $booking->get_error_messages();
          $status = LATEPOINT_STATUS_ERROR;
        }
      }else{
        // error customer validation/saving
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = $customer->get_error_messages();
        if(is_array($response_html)) $response_html = implode(', ', $response_html);
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html, 'form_values_to_update' => $form_values_to_update));
      }
    }


    /*
      Update booking
    */

    public function update(){
      $this->update_formatted_time_params();

      $customer_params = $this->params['customer'];
      $booking_params = $this->params['booking'];
      $custom_fields_data = isset($customer_params['custom_fields']) ? $customer_params['custom_fields'] : [];

      $booking = new OsBookingModel($booking_params['id']);
      $old_status = $booking->status;
      $old_status_nice = $booking->nice_status;
      $booking->set_data($booking_params);

      // Customer update/create
      if($booking->customer_id){
        $customer = new OsCustomerModel($booking->customer_id);
        $is_new_customer = false;
      }else{
        $customer = new OsCustomerModel();
        $is_new_customer = true;
      }
      $customer->set_data($customer_params);
      if($customer->validate_custom_fields($custom_fields_data) && $customer->save()){
        $customer->save_custom_fields($custom_fields_data);
        if($is_new_customer){
          OsNotificationsHelper::process_new_customer_notifications($customer);
          OsActivitiesHelper::create_activity(array('code' => 'customer_create', 'customer_id' => $customer->id));
        }

        $booking->customer_id = $customer->id;
        $form_values_to_update['booking[customer_id]'] = $booking->customer_id;
        if($booking->save()){
          do_action('latepoint_booking_updated_admin', $booking);
          OsActivitiesHelper::create_activity(array('code' => 'booking_update', 'booking' => $booking));
          if($old_status != $booking->status){
            OsNotificationsHelper::process_booking_status_changed_notifications($booking, $old_status_nice);
            do_action('latepoint_booking_status_changed', $booking, $old_status);
          }
          $response_html = __('Appointment Updated: ID#', 'latepoint') . $booking->id;
          $status = LATEPOINT_STATUS_SUCCESS;
        }else{
          $response_html = $booking->get_error_messages();
          $status = LATEPOINT_STATUS_ERROR;
        }
      }else{
        // error customer validation/saving
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = $customer->get_error_messages();
        if(is_array($response_html)) $response_html = implode(', ', $response_html);
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


    function customer_quick_edit_form(){
      $selected_customer = new OsCustomerModel();
      if(isset($this->params['customer_id'])){
        $selected_customer->load_by_id($this->params['customer_id']);
      }
      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
      $this->vars['selected_customer'] = $selected_customer;
      $this->format_render(__FUNCTION__);
    }

    function edit_form(){
      $agents = new OsAgentModel();
      $agents_arr = $agents->get_results();
      $this->vars['agents'] = $agents_arr;

      $customers = new OsCustomerModel();
      $customers_arr = $customers->get_results();
      $this->vars['customers'] = $customers_arr;

      $booking_id = $this->params['id'];

      $booking = new OsBookingModel($booking_id);

      $service = new OsServiceModel();
      $services = $service->get_results();

      $selected_agent = new OsAgentModel($booking->agent_id);
      $selected_customer = new OsCustomerModel($booking->customer_id);

      $this->vars['services'] = $services;
      $this->vars['booking'] = $booking;
      $this->vars['selected_agent'] = $selected_agent;
      $this->vars['selected_customer'] = $selected_customer;
      $this->format_render(__FUNCTION__);
    }

    function quick_edit_form(){
      $agents = new OsAgentModel();
      $agents_arr = $agents->get_results();
      $this->vars['agents'] = $agents_arr;

      $customers = new OsCustomerModel();
      $customers_arr = $customers->get_results_as_models();
      $this->vars['customers'] = $customers_arr;

      $booking_id = $this->params['id'];

      $booking = new OsBookingModel($booking_id);

      $service = new OsServiceModel();
      $services = $service->get_results();

      $selected_agent = new OsAgentModel($booking->agent_id);
      $selected_customer = new OsCustomerModel($booking->customer_id);

      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
      $this->vars['services'] = $services;
      $this->vars['booking'] = $booking;
      $this->vars['selected_agent'] = $selected_agent;
      $this->vars['selected_customer'] = $selected_customer;
      $this->format_render(__FUNCTION__);
    }


    function quick_new_form(){
      $agents = new OsAgentModel();
      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);
      $agents_arr = $agents->get_results();
      $this->vars['agents'] = $agents_arr;

      $customers = new OsCustomerModel();
      $customers_arr = $customers->get_results_as_models();
      $this->vars['customers'] = $customers_arr;
      
      $booking = new OsBookingModel();
      $service = new OsServiceModel();
      $services = $service->get_results();

      $booking->agent_id = isset($this->params['agent_id']) ? $this->params['agent_id'] : '';
      $booking->service_id = isset($this->params['service_id']) ? $this->params['service_id'] : '';
      $booking->customer_id = isset($this->params['customer_id']) ? $this->params['customer_id'] : '';
      $booking->location_id = isset($this->params['location_id']) ? $this->params['location_id'] : OsLocationHelper::get_selected_location_id();

      $booking->start_date = isset($this->params['start_date']) ? $this->params['start_date'] : OsTimeHelper::today_date('Y-m-d');
      $booking->start_time = isset($this->params['start_time']) ? $this->params['start_time'] : 600;

      $booking->end_date = $booking->start_date;
      if($booking->start_time) $booking->end_time = $booking->start_time + $booking->service->duration;
      $booking->buffer_before = $booking->service->buffer_before;
      $booking->buffer_after = $booking->service->buffer_after;
      $booking->status = 'approved';

      $selected_customer = new OsCustomerModel($booking->customer_id);
      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
      $this->vars['selected_customer'] = $selected_customer;
      $this->vars['services'] = $services;
      $this->vars['booking'] = $booking;
      $this->format_render(__FUNCTION__);
    }

    function daily_agent(){
      $this->vars['breadcrumbs'][] = array('label' => __('Daily View', 'latepoint'), 'link' => false );

      $services = new OsServiceModel();
      $agents = new OsAgentModel();

      if($this->logged_in_agent_id){
        $agents->where(['id' => $this->logged_in_agent_id]);
        $this->params['selected_agent_id'] = $this->logged_in_agent_id;
      }

      $agents_models = $agents->get_results_as_models();
      $selected_agent = $agents_models[0];
      if(isset($this->params['selected_agent_id'])){
        $selected_agent = $agents->load_by_id($this->params['selected_agent_id']);
      }
      $selected_agent_id = (isset($selected_agent)) ? $selected_agent->id : false;

      $services_models = $services->get_results_as_models();
      $this->vars['services'] = $services_models;
      $selected_service = $services_models[0];

      if(isset($this->params['selected_service_id'])){
        $selected_service = $services->load_by_id($this->params['selected_service_id']);
      }



      $selected_service_id = (isset($selected_service)) ? $selected_service->id : false;

      $this->vars['agents'] = $agents_models;
      $this->vars['selected_service'] = $selected_service;
      $this->vars['selected_agent'] = $selected_agent;
      $this->vars['selected_agent_id'] = $selected_agent_id;
      $this->vars['selected_service_id'] = $selected_service_id;

      $this->vars['timeblock_interval'] = OsSettingsHelper::get_timeblock_interval();

      $today_date = new OsWpDateTime('today');

      if(isset($this->params['target_date'])){
        $target_date = new OsWpDateTime($this->params['target_date']);
      }else{
        $target_date = new OsWpDateTime('today');
      }

      $this->vars['nice_selected_date'] = OsTimeHelper::nice_date($target_date->format('Y-m-d'));

      $calendar_prev = clone $target_date;
      $calendar_next = clone $target_date;
      $calendar_start = clone $target_date;
      $calendar_end = clone $target_date;

      $this->vars['today_date'] = $today_date;
      $this->vars['target_date'] = $target_date;

      $this->vars['calendar_prev'] = $calendar_prev->modify('- 7 days');
      $this->vars['calendar_next'] = $calendar_next->modify('+ 7 days');





      $work_periods_arr = OsBookingHelper::get_work_periods(['agent_id' => $selected_agent_id, 
                                                              'custom_date' => $target_date->format('Y-m-d'),
                                                              'location_id' => OsLocationHelper::get_selected_location_id(),
                                                              'week_day' => $target_date->format('N')]);
      $this->vars['work_periods_arr'] = $work_periods_arr;

      list($this->vars['work_start_minutes'], $this->vars['work_end_minutes']) = OsBookingHelper::get_work_start_end_time($work_periods_arr);

      $this->vars['work_total_minutes'] = $this->vars['work_end_minutes'] - $this->vars['work_start_minutes'];

      $bookings = OsBookingHelper::get_bookings_for_date($target_date->format('Y-m-d'), ['agent_id' => $selected_agent_id, 'location_id' => OsLocationHelper::get_selected_location_id()]);
      $services_count_by_types = OsBookingHelper::get_services_count_by_type_for_date($target_date->format('Y-m-d'), $selected_agent_id);


      $service_types_chart_labels_string = array();
      $service_types_chart_data_values_string = array();
      $service_types_chart_data_colors = array();

      foreach($services_count_by_types as $service_count_by_type){
        $service_types_chart_labels_string[] = $service_count_by_type['name'];
        $service_types_chart_data_values_string[] = $service_count_by_type['count'];
        $service_types_chart_data_colors[] = $service_count_by_type['bg_color'];
      }

      $this->vars['services_count_by_types'] = $services_count_by_types;
      $this->vars['service_types_chart_labels_string'] = implode(',', $service_types_chart_labels_string);
      $this->vars['service_types_chart_data_values_string'] = implode(',', $service_types_chart_data_values_string);
      $this->vars['service_types_chart_data_colors'] = implode(',', $service_types_chart_data_colors);




      $this->vars['bookings'] = $bookings;
      $this->vars['total_bookings'] = $bookings ? count($bookings) : 0;
      $this->vars['total_openings'] = OsAgentHelper::count_openings_for_date($selected_agent, $selected_service, OsLocationHelper::get_selected_location(), $target_date->format('Y-m-d'));

      $this->format_render(__FUNCTION__);
    }

    function monthly_agents(){

      $this->vars['page_header'] = __('Appointments', 'latepoint');
      $this->vars['breadcrumbs'] = [];

      if(isset($this->params['month']) && isset($this->params['year'])){
        $start_date_string = implode('-', [$this->params['year'], $this->params['month'], '01']);
        $this->vars['calendar_only'] = true;
      }else{
        $this->vars['calendar_only'] = false;
        $start_date_string = implode('-', [OsTimeHelper::today_date('Y'), OsTimeHelper::today_date('m'), '01']);
      }

      $agents = new OsAgentModel();

      if($this->logged_in_agent_id){
        $agents->where(['id' => $this->logged_in_agent_id]);
      }
      $agents_arr = $agents->get_results();



      $agents = array();
      foreach($agents_arr as $agent_row){
        $agent = new OsAgentModel();
        $agent->load_from_row_data($agent_row);
        $agents[] = $agent;
      }

      $this->vars['agents'] = $agents;
      $this->vars['start_date_string'] = $start_date_string;
      $this->vars['calendar_start_date'] = new OsWpDateTime($start_date_string);


      
      $this->format_render(__FUNCTION__);
    }


    function weekly_agent(){
      $this->vars['breadcrumbs'][] = array('label' => __('Weekly Calendar', 'latepoint'), 'link' => false );

      $agents = new OsAgentModel();


      if($this->logged_in_agent_id){
        $this->params['selected_agent_id'] = $this->logged_in_agent_id;
        $agents->where(['id' => $this->logged_in_agent_id]);
      }

      $agents_arr = $agents->get_results();

      $this->vars['agents'] = $agents_arr;
      if(isset($this->params['selected_agent_id'])){
        $selected_agent = $agents->load_by_id($this->params['selected_agent_id']);
      }else{
        if(isset($agents_arr)){
          $selected_agent = $agents->load_by_id($agents_arr[0]->id);
        }else{
          $selected_agent = false;
        }
      }

      $selected_agent_id = (isset($selected_agent) && $selected_agent) ? $selected_agent->id : false;
      $this->vars['selected_agent_id'] = $selected_agent_id;
      $this->vars['selected_agent'] = $selected_agent;

      $this->vars['timeblock_interval'] = OsSettingsHelper::get_timeblock_interval();

      $today_date = new OsWpDateTime('today');

      if(isset($this->params['target_date'])){
        $target_date = new OsWpDateTime($this->params['target_date']);
      }else{
        $target_date = new OsWpDateTime('today');
      }

      $calendar_prev = clone $target_date;
      $calendar_next = clone $target_date;
      $calendar_start = clone $target_date;
      $calendar_end = clone $target_date;

      $this->vars['today_date'] = $today_date;
      $this->vars['target_date'] = $target_date;
      $this->vars['calendar_start'] = $calendar_start->modify('monday this week');
      $this->vars['calendar_end'] = $calendar_end->modify('sunday this week');

      $this->vars['calendar_prev'] = $calendar_prev->modify('- 7 days');
      $this->vars['calendar_next'] = $calendar_next->modify('+ 7 days');




      $work_periods_arr = OsBookingHelper::get_work_periods(['agent_id' => $selected_agent_id, 
                                                              'location_id' => OsLocationHelper::get_selected_location_id()]);

      list($this->vars['work_start_minutes'], $this->vars['work_end_minutes']) = OsBookingHelper::get_work_start_end_time($work_periods_arr);

      $this->vars['work_total_minutes'] = $this->vars['work_end_minutes'] - $this->vars['work_start_minutes'];

      $this->format_render(__FUNCTION__);
    }













    public function load_monthly_calendar_days_only(){
      $target_date = new OsWpDateTime($this->params['target_date_string']);
      $this->vars['target_date'] = $target_date;

      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }


    public function load_monthly_calendar_days(){
      $target_date = new OsWpDateTime($this->params['target_date_string']);
      $service_id = $this->params['service_id'];
      $agent_id = $this->params['agent_id'];
      $location_id = isset($this->params['location_id']) ? $this->params['location_id'] : OsLocationHelper::get_selected_location_id();

      $calendar_settings = ['service_id' => $service_id, 'agent_id' => $agent_id, 'location_id' => $location_id];
      if(!isset($this->params['allow_full_access'])){
        $calendar_settings['earliest_possible_booking'] = OsSettingsHelper::get_settings_value('earliest_possible_booking', false);
        $calendar_settings['latest_possible_booking'] = OsSettingsHelper::get_settings_value('latest_possible_booking', false);
      }


      $this->format_render('_monthly_calendar_days', array('target_date' => $target_date, 'calendar_settings' => $calendar_settings));
    }

    private function remove_already_selected_steps(){
      // if current step is agents or services selection and we have it preselected - skip to next step
      if($this->restrictions['selected_service']){
        $this->booking->service_id = $this->restrictions['selected_service'];
        $step_index_to_remove = array_search('services', $this->step_names_in_order);
        if(false !== $step_index_to_remove){
          unset($this->step_names_in_order[$step_index_to_remove]);
          $this->step_names_in_order = array_values($this->step_names_in_order);
        }
      }
      if($this->restrictions['selected_location']){
        $this->booking->location_id = $this->restrictions['selected_location'];
        $step_index_to_remove = array_search('locations', $this->step_names_in_order);
        if(false !== $step_index_to_remove){
          unset($this->step_names_in_order[$step_index_to_remove]);
          $this->step_names_in_order = array_values($this->step_names_in_order);
        }
      }
      if($this->restrictions['selected_agent']){
        $this->booking->agent_id = $this->restrictions['selected_agent'];
        $step_index_to_remove = array_search('agents', $this->step_names_in_order);
        if(false !== $step_index_to_remove){
          unset($this->step_names_in_order[$step_index_to_remove]);
          $this->step_names_in_order = array_values($this->step_names_in_order);
        }
      }
    }



    private function set_restrictions($restrictions = array()){
      $this->restrictions = array('show_locations' => false, 
                                  'show_agents' => false, 
                                  'show_services' => false, 
                                  'show_service_categories' => false, 
                                  'selected_location' => false, 
                                  'selected_agent' => false, 
                                  'selected_service' => false, 
                                  'selected_service_category' => false,
                                  'calendar_start_date' => false);

      if(isset($restrictions) && !empty($restrictions)){
        // filter locations
        if(isset($restrictions['show_locations'])) 
          $this->restrictions['show_locations'] = $restrictions['show_locations'];

        // filter agents
        if(isset($restrictions['show_agents'])) 
          $this->restrictions['show_agents'] = $restrictions['show_agents'];

        // filter service category
        if(isset($restrictions['show_service_categories'])) 
          $this->restrictions['show_service_categories'] = $restrictions['show_service_categories'];

        // filter services
        if(isset($restrictions['show_services'])) 
          $this->restrictions['show_services'] = $restrictions['show_services'];

        // preselected service category
        if(isset($restrictions['selected_service_category']) && is_numeric($restrictions['selected_service_category']))
          $this->restrictions['selected_service_category'] = $restrictions['selected_service_category'];

        // preselected calendar start date
        if(isset($restrictions['calendar_start_date']) && OsTimeHelper::is_valid_date($restrictions['calendar_start_date']))
          $this->restrictions['calendar_start_date'] = $restrictions['calendar_start_date'];

        // restriction in settings can ovveride it
        if(OsTimeHelper::is_valid_date(OsSettingsHelper::get_settings_value('earliest_possible_booking')))
          $this->restrictions['calendar_start_date'] = OsSettingsHelper::get_settings_value('earliest_possible_booking');

        // preselected location
        if(isset($restrictions['selected_location']) && is_numeric($restrictions['selected_location'])){
          $this->restrictions['selected_location'] = $restrictions['selected_location'];
          $this->booking->location_id = $restrictions['selected_location'];
        }
        // preselected agent
        if(isset($restrictions['selected_agent']) && (is_numeric($restrictions['selected_agent']) || ($restrictions['selected_agent'] == LATEPOINT_ANY_AGENT))){
          $this->restrictions['selected_agent'] = $restrictions['selected_agent'];
          $this->booking->agent_id = $restrictions['selected_agent'];
        }

        // preselected service
        if(isset($restrictions['selected_service']) && is_numeric($restrictions['selected_service'])){
          $this->restrictions['selected_service'] = $restrictions['selected_service'];
          $this->booking->service_id = $restrictions['selected_service'];
        }
      }
      $this->vars['restrictions'] = $this->restrictions;
    }

    private function set_booking_object(){
      $this->booking = new OsBookingModel();
      if(isset($this->params['booking'])){
        $booking_params = $this->params['booking'];
        $this->booking->set_data($booking_params);
      }
      if(OsLocationHelper::count_locations() == 1) $this->booking->location_id = OsLocationHelper::get_selected_location_id();
    }

    public function steps($restrictions = false, $output = true){
      $this->set_booking_object();
      if((!$restrictions || empty($restrictions)) && isset($this->params['restrictions'])) $restrictions = $this->params['restrictions'];
      $this->set_restrictions($restrictions);
      $this->step_names_in_order = OsBookingHelper::get_step_names_in_order();
      $this->remove_already_selected_steps();
      $this->steps_models = array();
      foreach($this->step_names_in_order as $step_name){
        $step_model = new OsStepModel($step_name);
        $this->steps_models[] = $step_model;
      }
      $active_step_model = $this->steps_models[0];

      // if is payment step - check if total is not $0 and if it is skip payment step
      if(($active_step_model->name == 'payment') 
        && !($this->booking->full_amount_to_charge() > 0) 
        && !($this->booking->deposit_amount_to_charge() > 0) 
        && !OsSettingsHelper::is_env_demo()){
          $active_step_model = $this->steps_models[1];
      }
      $this->vars['show_next_btn'] = $this->can_step_show_next_btn($active_step_model->name);
      $this->vars['show_prev_btn'] = $this->can_step_show_prev_btn($active_step_model->name);
      $this->vars['steps_models'] = $this->steps_models;
      $this->vars['active_step_model'] = $active_step_model;
      $this->vars['active_step_partial_path'] = 'steps/_'.$active_step_model->name.'.php';


      // Call step function
      $step_function_name = 'step_'.$active_step_model->name;
      self::$step_function_name();

      $this->vars['current_step'] = $active_step_model->name;
      $this->vars['booking'] = $this->booking;
      $this->set_layout('none');

      if($output){
        $this->format_render(__FUNCTION__, array(), array('step' => $active_step_model->name));
      }else{
        return $this->format_render_return(__FUNCTION__, array(), array('step' => $active_step_model->name));
      }
    }


    public function get_step(){
      $step_direction = isset($this->params['step_direction']) ? $this->params['step_direction'] : 'next';
      switch ($step_direction) {
        case 'next':
          $this->next_step();
          break;
        case 'prev':
          $this->prev_step();
          break;
        case 'specific':
          $this->specific_step();
          break;
      }
    }

    public function specific_step(){
      $this->set_booking_object();
      $this->set_restrictions($this->params['restrictions']);
      $this->step_names_in_order = OsBookingHelper::get_step_names_in_order();
      $this->remove_already_selected_steps();
      // Check if a valid step name
      if(in_array($this->params['current_step'], $this->step_names_in_order)){
        $current_step = $this->params['current_step'];
        // if is payment step - check if total is not $0 and if it is skip payment step
        if(($current_step == 'payment') 
          && !($this->booking->full_amount_to_charge() > 0) 
          && !($this->booking->deposit_amount_to_charge() > 0) 
          && !OsSettingsHelper::is_env_demo()){
            $step_index = array_search($current_step, $this->step_names_in_order);
            $current_step = $this->step_names_in_order[$step_index + 1];
        }
      }else{
        $current_step = $this->step_names_in_order[0];
      }


      // Process step
      $step_function_name = 'step_'.$current_step;
      self::$step_function_name();

      $this->vars['booking'] = $this->booking;
      $this->vars['current_step'] = $current_step;

      $this->format_render('steps/_'.$current_step, array(), array(
        'step_name' => $current_step, 
        'show_next_btn' => $this->can_step_show_next_btn($current_step), 
        'show_prev_btn' => $this->can_step_show_prev_btn($current_step), 
        'is_first_step' => $this->is_first_step($current_step), 
        'is_last_step' => $this->is_last_step($current_step), 
        'is_pre_last_step' => $this->is_pre_last_step($current_step)));
    }

    public function next_step(){
      if(OsAuthHelper::is_customer_logged_in() && OsSettingsHelper::get_settings_value('max_future_bookings_per_customer')){
        $customer = OsAuthHelper::get_logged_in_customer();
        if($customer->future_bookings_count >= OsSettingsHelper::get_settings_value('max_future_bookings_per_customer')){
          $this->format_render('steps/_limit_reached', array(), array(
            'show_next_btn' => false, 
            'show_prev_btn' => false, 
            'is_first_step' => true, 
            'is_last_step' => true, 
            'is_pre_last_step' => false));
          return;
        }
      }
      $this->set_booking_object();

      $this->set_restrictions($this->params['restrictions']);
      $this->step_names_in_order = OsBookingHelper::get_step_names_in_order();
      $this->remove_already_selected_steps();

      // Check if a valid step name
      if(in_array($this->params['current_step'], $this->step_names_in_order)){
        $current_step = $this->params['current_step'];
      }else{
        $current_step = $this->step_names_in_order[0];
      }


      // Process submitted step
      $process_current_step_function_name = 'process_step_'.$current_step;
      self::$process_current_step_function_name();

      // Figure out what next step is
      $new_current_step_name = $this->get_next_step_name($current_step);
      $next_step_function_name = 'step_'.$new_current_step_name;
      self::$next_step_function_name();


      $this->vars['booking'] = $this->booking;
      $this->vars['current_step'] = $new_current_step_name;

      $this->format_render('steps/_'.$new_current_step_name, array(), array(
        'step_name' => $new_current_step_name, 
        'show_next_btn' => $this->can_step_show_next_btn($new_current_step_name), 
        'show_prev_btn' => $this->can_step_show_prev_btn($new_current_step_name), 
        'is_first_step' => $this->is_first_step($new_current_step_name), 
        'is_last_step' => $this->is_last_step($new_current_step_name), 
        'is_pre_last_step' => $this->is_pre_last_step($new_current_step_name)));
    }



    public function prev_step(){
      $this->set_booking_object();

      $this->set_restrictions($this->params['restrictions']);
      $this->step_names_in_order = OsBookingHelper::get_step_names_in_order();
      $this->remove_already_selected_steps();

      $current_step = $this->params['current_step'];



      // Check if a valid step name
      if(in_array($this->params['current_step'], $this->step_names_in_order)){
        $current_step = $this->params['current_step'];
      }else{
        $current_step = $this->step_names_in_order[0];
      }

      $new_current_step_name = $this->get_prev_step_name($current_step);
      $prev_step_function_name = 'step_'.$new_current_step_name;
      self::$prev_step_function_name();


      $this->vars['booking'] = $this->booking;
      $this->vars['current_step'] = $new_current_step_name;

      $this->format_render('steps/_'.$new_current_step_name, array(), array(
        'step_name' => $new_current_step_name, 
        'show_next_btn' => $this->can_step_show_next_btn($new_current_step_name), 
        'show_prev_btn' => $this->can_step_show_prev_btn($new_current_step_name),
        'is_first_step' => $this->is_first_step($new_current_step_name),  
        'is_last_step' => $this->is_last_step($new_current_step_name), 
        'is_pre_last_step' => $this->is_pre_last_step($new_current_step_name)));
    }




    private function get_next_step_name($current_step){
      $step_index = array_search($current_step, $this->step_names_in_order);
      if(($step_index + 1) >= count($this->step_names_in_order)) return $this->step_names_in_order[$step_index];
      $next_step = $this->step_names_in_order[$step_index + 1];
      if(($next_step == 'payment') 
          && !($this->booking->full_amount_to_charge() > 0) 
          && !($this->booking->deposit_amount_to_charge() > 0) 
          && !OsSettingsHelper::is_env_demo()){
        $next_step = $this->step_names_in_order[$step_index + 2];
      }
      return $next_step;
    }

    private function get_prev_step_name($current_step){
      $step_index = array_search($current_step, $this->step_names_in_order);
      $prev_step = ($step_index) ? $this->step_names_in_order[$step_index - 1] : $this->step_names_in_order[0];
      if(($prev_step == 'payment') 
          && !($this->booking->full_amount_to_charge() > 0) 
          && !($this->booking->deposit_amount_to_charge() > 0) 
          && !OsSettingsHelper::is_env_demo()
          && $step_index > 1){
        $prev_step = $this->step_names_in_order[$step_index - 2];
      }
      return $prev_step;
    }


    private function is_first_step($step_name){
      $step_index = array_search($step_name, $this->step_names_in_order);
      return $step_index == 0;
    }

    private function is_last_step($step_name){
      $step_index = array_search($step_name, $this->step_names_in_order);
      return (($step_index + 1) == count($this->step_names_in_order));
    }

    private function is_pre_last_step($step_name){
      $next_step_name = $this->get_next_step_name($step_name);
      $step_index = array_search($next_step_name, $this->step_names_in_order);
      return (($step_index + 1) == count($this->step_names_in_order));
    }

    private function can_step_show_prev_btn($step_name){
      $step_index = array_search($step_name, $this->step_names_in_order);
      // if first or last step
      if($step_index == 0 || (($step_index + 1) == count($this->step_names_in_order))){
        return false;
      }else{
        return true;
      }
    }

    private function can_step_show_next_btn($step_name){
      $show_payments_next = (count(OsSettingsHelper::get_payment_methods()) > 1) ? false : true;
      $step_show_btn_rules = array('services' => false, 
                                    'locations' => false, 
                                    'agents' => false, 
                                    'datepicker' => false, 
                                    'contact' => true, 
                                    'payment' => $show_payments_next, 
                                    'verify' => true, 
                                    'confirmation' => false);
      return $step_show_btn_rules[$step_name];
    }

















    // LOCATIONS

    public function process_step_locations(){
    }

    public function step_locations(){
      $locations_model = new OsLocationModel();
      $show_selected_locations_arr = ($this->restrictions['show_locations']) ? explode(',', $this->restrictions['show_locations']) : false;
      $connected_ids = OsConnectorHelper::get_connected_object_ids('location_id', ['service_id' => $this->booking->service_id, 'agent_id' => $this->booking->agent_id]);

      // if show only specific services are selected (restrictions) - remove ids that are not found in connection
      $show_locations_arr = (!empty($show_selected_locations_arr) && !empty($connected_ids)) ? array_intersect($connected_ids, $show_selected_locations_arr) : $connected_ids;
      if(!empty($show_locations_arr)) $locations_model->where_in('id', $show_locations_arr);

      $locations = $locations_model->should_be_active()->order_by('name asc')->get_results_as_models();
      $this->vars['locations'] = $locations;
    }






    // SERVICES

    public function process_step_services(){
    }

    public function step_services(){
      $services_model = new OsServiceModel();
      $show_selected_services_arr = $this->restrictions['show_services'] ? explode(',', $this->restrictions['show_services']) : false;
      $show_service_categories_arr = $this->restrictions['show_service_categories'] ? explode(',', $this->restrictions['show_service_categories']) : false;
      $preselected_category = $this->restrictions['selected_service_category'];

      $connected_ids = OsConnectorHelper::get_connected_object_ids('service_id', ['agent_id' => $this->booking->agent_id, 'location_id' => $this->booking->location_id]);
      // if show only specific services are selected (restrictions) - remove ids that are not found in connection
      $show_services_arr = (!empty($show_selected_services_arr) && !empty($connected_ids)) ? array_intersect($connected_ids, $show_selected_services_arr) : $connected_ids;

      if(!empty($show_services_arr)) $services_model->where_in('id', $show_services_arr);

      $services = $services_model->should_be_active()->get_results_as_models();

      $this->vars['show_services_arr'] = $show_services_arr;
      $this->vars['show_service_categories_arr'] = $show_service_categories_arr;
      $this->vars['preselected_category'] = $preselected_category;
      $this->vars['services'] = $services;
    }



    // AGENTS

    public function process_step_agents(){
    }

    public function step_agents(){
      $agents_model = new OsAgentModel();

      $show_selected_agents_arr = ($this->restrictions['show_agents']) ? explode(',', $this->restrictions['show_agents']) : false;
      $connected_ids = OsConnectorHelper::get_connected_object_ids('agent_id', ['service_id' => $this->booking->service_id, 'location_id' => $this->booking->location_id]);

      // If date/time is selected - filter agents who are available at that time
      if($this->booking->start_date && $this->booking->start_time){
        $available_agent_ids = [];
        foreach($connected_ids as $agent_id){
          if(OsAgentHelper::is_agent_available_on($agent_id, $this->booking->start_date, $this->booking->start_time, $this->booking->duration, $this->booking->service_id, $this->booking->location_id)) $available_agent_ids[] = $agent_id;
        }
        $connected_ids = (!empty($available_agent_ids) && !empty($connected_ids)) ? array_intersect($available_agent_ids, $connected_ids) : $connected_ids;
      }
      

      // if show only specific agents are selected (restrictions) - remove ids that are not found in connection
      $show_agents_arr = (!empty($show_selected_agents_arr) && !empty($connected_ids)) ? array_intersect($connected_ids, $show_selected_agents_arr) : $connected_ids;
      if(!empty($show_agents_arr)) $agents_model->where_in('id', $show_agents_arr);

      $agents = $agents_model->should_be_active()->get_results_as_models();


      $this->vars['agents'] = $agents;
    }



    // DATEPICKER

    public function step_datepicker(){
      if(empty($this->booking->agent_id)) $this->booking->agent_id = LATEPOINT_ANY_AGENT;
      $this->vars['calendar_start_date'] = $this->restrictions['calendar_start_date'] ? $this->restrictions['calendar_start_date'] : 'today';
    }

    public function process_step_datepicker(){
    }



    // AUTHENTICATION

    // Logs out customer and shows blank contact step
    public function logout_customer(){
      $customer = OsAuthHelper::logout_customer();

      $this->booking = new OsBookingModel();
      $this->booking->customer = new OsCustomerModel();

      $this->vars['booking'] = $this->booking;
      $this->vars['no_params'] = true;
      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
      $response_html = $this->render($this->get_view_uri('steps/_contact'), 'none');
      $status = LATEPOINT_STATUS_SUCCESS;

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    // Login customer and show contact step with prefilled info
    public function login_customer(){
      $customer_id = '';
      $customer = OsAuthHelper::login_customer($this->params['email'], $this->params['password']);
      if($customer){
        $this->booking = new OsBookingModel();
        $this->booking->customer = $customer;
        $this->booking->customer_id = $customer->id;
        $this->vars['booking'] = $this->booking;
        $this->vars['no_params'] = true;
        $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
        $response_html = $this->render($this->get_view_uri('steps/_contact'), 'none');
        $status = LATEPOINT_STATUS_SUCCESS;
        $customer_id = $customer->id;
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Sorry, that email or password didn\'t work.', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html, 'customer_id' => $customer_id));
      }
    }

    public function login_customer_using_social_data($network, $social_user){
      $customer_id = '';
      if(isset($social_user['social_id'])){
        $social_id_field_name = $network.'_user_id';
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = $social_user['social_id'];
        // Search for existing customer with email that google provided
        $customer = new OsCustomerModel();
        $customer = $customer->where(array('email' => $social_user['email']))->set_limit(1)->get_results_as_models();
        // Create customer if its not found
        if(!$customer){
          $customer = new OsCustomerModel();
          $customer->first_name = $social_user['first_name'];
          $customer->last_name = $social_user['last_name'];
          $customer->email = $social_user['email'];
          $customer->$social_id_field_name = $social_user['social_id'];
          if(!$customer->save()){
            $response_html = $customer->get_error_messages();
            $status = LATEPOINT_STATUS_ERROR;
          }
        }

        if(($status == LATEPOINT_STATUS_SUCCESS) && $customer->id){
          $customer_id = $customer->id;
          // Update customer google user id if its not set yet
          if($customer->$social_id_field_name != $social_user['social_id']){
            $customer->$social_id_field_name = $social_user['social_id'];
            $customer->save();
          }
          OsAuthHelper::authorize_customer($customer->id);
          $this->booking = new OsBookingModel();
          $this->booking->customer_id = $customer->id;
          $this->booking->customer = $customer;
          $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
          $this->vars['booking'] = $this->booking;
          $this->vars['no_params'] = true;
          $response_html = $this->render($this->get_view_uri('steps/_contact'), 'none');
        }
      }else{
        // ERROR WITH GOOGLE LOGIN
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = $social_user['error'];
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html, 'customer_id' => $customer_id));
      }

    }


    public function login_customer_using_google_token(){
      $social_user = OsSocialHelper::get_google_user_info_by_token($this->params['token']);
      $this->login_customer_using_social_data('google', $social_user);
    }

    public function login_customer_using_facebook_token(){
      $social_user = OsSocialHelper::get_facebook_user_info_by_token($this->params['token']);
      $this->login_customer_using_social_data('facebook', $social_user);
    }


    // CONTACT


    public function step_contact(){
      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');

      if(OsAuthHelper::is_customer_logged_in()){
        $this->booking->customer = OsAuthHelper::get_logged_in_customer();
        $this->booking->customer_id = $this->booking->customer->id;
      }else{
        $this->booking->customer = new OsCustomerModel();
      }
    }

    public function process_step_contact(){
      $status = LATEPOINT_STATUS_SUCCESS;

      $customer_params = $this->params['customer'];
      $logged_in_customer = OsAuthHelper::get_logged_in_customer();

      if($logged_in_customer && $this->params['booking']['customer_id']){
        // LOGGED IN
        if($logged_in_customer->id == $this->params['booking']['customer_id']){
          // LOGGED IN & VERIFIED
          // Check if they are changing the email on file
          if($logged_in_customer->email != $customer_params['email']){
            // Check if other customer already has this email
            $customer = new OsCustomerModel();
            $customer_with_email_exist = $customer->where(array('email'=> $customer_params['email'], 'id !=' => $logged_in_customer->id))->set_limit(1)->get_results_as_models();
            if($customer_with_email_exist){
              $status = LATEPOINT_STATUS_ERROR;
              $response_html = __('Another customer is registered with this email.', 'latepoint');
            }
          }
        }else{
          // LOGGED IN BUT WRONG CUSTOMER ID PASSED VIA FORM
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Customer does not match', 'latepoint');
        }
      }else{
        // NEW REGISTRATION
        $customer = new OsCustomerModel();
        $customer_exist = $customer->where(array('email'=> $customer_params['email']))->set_limit(1)->get_results_as_models();
        if($customer_exist){
          // CUSTOMER WITH THIS EMAIL EXISTS - ASK TO LOGIN, CHECK IF CURRENT CUSTOMER WAS REGISTERED AS A GUEST
          if($customer_exist->can_login_without_password()){
            $status == LATEPOINT_STATUS_SUCCESS;
            OsAuthHelper::authorize_customer($customer_exist->id);
          }else{
            // Not a guest account, do not allow using it
            $status = LATEPOINT_STATUS_ERROR;
            $response_html = __('An account with that email address already exists. Please try signing in.', 'latepoint');
          }
        }
      }
      if($status == LATEPOINT_STATUS_SUCCESS){
        $customer = new OsCustomerModel();
        $is_new_customer = true;
        if(OsAuthHelper::is_customer_logged_in()){
          $customer = OsAuthHelper::get_logged_in_customer();
          if(!$customer->is_new_record()) $is_new_customer = false;
        }
        $customer->set_data($customer_params);
        $custom_fields_data = isset($customer_params['custom_fields']) ? $customer_params['custom_fields'] : [];
        if($customer->validate_custom_fields($custom_fields_data) && $customer->save()){
          $customer->save_custom_fields($custom_fields_data);
          if($is_new_customer){
            OsNotificationsHelper::process_new_customer_notifications($customer);
            OsActivitiesHelper::create_activity(array('code' => 'customer_create', 'customer_id' => $customer->id));
          }

          $this->booking->customer_id = $customer->id;
          if(!OsAuthHelper::is_customer_logged_in()){
            OsAuthHelper::authorize_customer($customer->id);
          }
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = $customer->get_error_messages();
          if(is_array($response_html)) $response_html = implode(', ', $response_html);
        }
      }
      if($status == LATEPOINT_STATUS_ERROR){
        if($this->get_return_format() == 'json'){
          $this->send_json(array('status' => $status, 'message' => $response_html));
        }
      }

    }


    // VERIFICATION STEP

    public function process_step_verify(){

    }

    public function step_verify(){
      $this->vars['customer'] = new OsCustomerModel($this->booking->customer_id);
      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
    }

    // PAYMENT

    public function process_step_payment(){
    }

    public function step_payment(){

      $pay_methods = [];
      $pay_times = [];


      if(OsSettingsHelper::is_on('enable_payments')){

        if(OsSettingsHelper::is_accepting_payments_paypal()){
          $this->vars['paypal_amount_to_charge'] = ['deposit' => $this->booking->specs_calculate_deposit_price_to_charge(LATEPOINT_PAYMENT_METHOD_PAYPAL),
                                                    'full' => $this->booking->specs_calculate_full_price_to_charge(LATEPOINT_PAYMENT_METHOD_PAYPAL)];
          $pay_times['now'] = '
            <div class="lp-option lp-payment-trigger-paypal" data-method="'.LATEPOINT_PAYMENT_METHOD_PAYPAL.'">
              <div class="lp-option-image-w"><div class="lp-option-image" style="background-image: url('.LATEPOINT_IMAGES_URL.'payment_now_w_paypal.png)"></div></div>
              <div class="lp-option-label">'.__('Pay Now', 'latepoint').'</div>
            </div>';
          $pay_methods['paypal'] = '
            <div class="lp-option lp-option-with-paypal lp-payment-trigger-paypal" data-method="'.LATEPOINT_PAYMENT_METHOD_PAYPAL.'">
              <div class="lp-option-image-w"><div class="lp-option-image" style="background-image: url('.LATEPOINT_IMAGES_URL.'payment_paypal.png)"></div></div>
              <div class="lp-option-label">'.__('PayPal', 'latepoint').'</div>
            </div>';
        }

        if(OsSettingsHelper::is_on('enable_payments_cc')){
          $pay_times['now'] = '
            <div class="lp-option lp-payment-trigger-cc" data-method="'.LATEPOINT_PAYMENT_METHOD_CARD.'">
              <div class="lp-option-image-w"><div class="lp-option-image" style="background-image: url('.LATEPOINT_IMAGES_URL.'payment_cards.png)"></div></div>
              <div class="lp-option-label">'.__('Pay Now', 'latepoint').'</div>
            </div>';
          $pay_methods['cc'] = '
            <div class="lp-option lp-payment-trigger-cc" data-method="'.LATEPOINT_PAYMENT_METHOD_CARD.'">
              <div class="lp-option-image-w"><div class="lp-option-image" style="background-image: url('.LATEPOINT_IMAGES_URL.'payment_cards.png)"></div></div>
              <div class="lp-option-label">'.__('Credit Card', 'latepoint').'</div>
            </div>';
        }

        if(OsSettingsHelper::is_on('enable_payments_cc') && OsSettingsHelper::is_on('enable_payments_paypal')){
          $pay_times['now'] = '
            <div class="lp-option lp-payment-trigger-method-selector">
              <div class="lp-option-image-w"><div class="lp-option-image" style="background-image: url('.LATEPOINT_IMAGES_URL.'payment_now_w_paypal.png)"></div></div>
              <div class="lp-option-label">'.__('Pay Now', 'latepoint').'</div>
            </div>';
        }

        if(OsSettingsHelper::is_on('enable_payments_local')){
          $pay_times['later'] = '
            <div class="lp-option lp-payment-trigger-locally" data-method="'.LATEPOINT_PAYMENT_METHOD_LOCAL.'">
              <div class="lp-option-image-w"><div class="lp-option-image" style="background-image: url('.LATEPOINT_IMAGES_URL.'payment_later.png)"></div></div>
              <div class="lp-option-label">'.__('Pay Locally', 'latepoint').'</div>
            </div>';
        }
      }

      if(count($pay_times) == 2){
        $payment_css_class = 'lp-show-pay-times';
      }elseif(count($pay_methods) == 2){
        $payment_css_class = 'lp-show-pay-methods';
      }else{
        if($this->booking->can_pay_deposit_and_pay_full()){
          // deposit & full payment available
          $payment_css_class = 'lp-show-pay-portion-selection';
          if(OsSettingsHelper::is_on('enable_payments_cc')){
            // cards
            $this->booking->payment_method = LATEPOINT_PAYMENT_METHOD_CARD;
          }elseif(OsSettingsHelper::is_on('enable_payments_paypal')){
            // paypal
            $this->booking->payment_method = LATEPOINT_PAYMENT_METHOD_PAYPAL;
          }
        }else{
          if($this->booking->can_pay_deposit()){
            // deposit
            $this->booking->payment_portion = LATEPOINT_PAYMENT_PORTION_DEPOSIT;
          }elseif($this->booking->can_pay_full()){
            // full payment
            $this->booking->payment_portion = LATEPOINT_PAYMENT_PORTION_FULL;
          }
          if(OsSettingsHelper::is_on('enable_payments_cc')){
            $payment_css_class = 'lp-show-card';
            $this->booking->payment_method = LATEPOINT_PAYMENT_METHOD_CARD;
          }
          if(OsSettingsHelper::is_on('enable_payments_paypal')){
            $payment_css_class = 'lp-show-paypal';
            $this->booking->payment_method = LATEPOINT_PAYMENT_METHOD_PAYPAL;
          }
        }
      }

      $this->vars['pay_times'] = $pay_times;
      $this->vars['pay_methods'] = $pay_methods;
      $this->vars['payment_css_class'] = $payment_css_class;
    }


    // CONFIRMATION

    public function process_step_confirmation(){
    }

    public function step_confirmation(){
      $this->vars['customer'] = new OsCustomerModel($this->booking->customer_id);
      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
      if($this->booking->is_new_record()){
        if(!$this->booking->save_from_booking_form()){
          OsDebugHelper::log($this->booking->get_error_messages());
          $status = LATEPOINT_STATUS_ERROR;
          if(OsSettingsHelper::is_env_dev()){
            $response_html = __($this->booking->get_error_messages(), 'latepoint');
          }else{
            $response_html = __('Error! FYD82348', 'latepoint');
          }
          $this->send_json(array('status' => $status, 'message' => $response_html));
        }
      }
    }



  }

endif;