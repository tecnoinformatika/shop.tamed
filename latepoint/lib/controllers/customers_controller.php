<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsCustomersController' ) ) :


  class OsCustomersController extends OsController {

    function __construct(){
      parent::__construct();
      
      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'customers/';
      $this->vars['page_header'] = __('Customers', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Customers', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('customers', 'index') ) );
    }

    public function destroy(){
      if(filter_var($this->params['id'], FILTER_VALIDATE_INT)){
        $customer = new OsCustomerModel($this->params['id']);
        if($customer->delete()){
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Customer Removed', 'latepoint');
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Error Removing Customer', 'latepoint');
        }
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error Removing Customer', 'latepoint');
      }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    /*
      New customer form
    */

    public function new_form(){
      $this->vars['page_header'] = __('Crear nuevo cliente', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Crear nuevo cliente', 'latepoint'), 'link' => false );

      $this->vars['customer'] = new OsCustomerModel();
      $this->vars['wp_users_for_select'] = OsWpUserHelper::get_wp_users_for_select();

      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');

      $this->format_render(__FUNCTION__);
    }

    /*
      Edit customer
    */

    public function edit_form(){
      $customer_id = $this->params['id'];

      $this->vars['page_header'] = __('Editar cliente', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Editar cliente', 'latepoint'), 'link' => false );

      $this->vars['customer'] = new OsCustomerModel($customer_id);
      $this->vars['wp_users_for_select'] = OsWpUserHelper::get_wp_users_for_select();

      $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');

      $this->format_render(__FUNCTION__);
    }



    /*
      Create customer
    */

    public function create(){
      $customer = new OsCustomerModel();
      $customer->set_data($this->params['customer']);
      $userlogin = $this->params['customer']['first_name'].$this->params['customer']['last_name'];
      $useremail = $this->params['customer']['email'];
      register_new_user($userlogin, $useremail);
      $custom_fields_data = isset($this->params['customer']['custom_fields']) ? $this->params['customer']['custom_fields'] : [];
      if($customer->validate_custom_fields($custom_fields_data) && $customer->save()){
        $customer->save_custom_fields($custom_fields_data);
        $response_html = __('Cliente creado correctamente', 'latepoint');
        $status = LATEPOINT_STATUS_SUCCESS;
        OsActivitiesHelper::create_activity(array('code' => 'customer_create', 'customer_id' => $customer->id));
      }else{
        $response_html = $customer->get_error_messages();
        $status = LATEPOINT_STATUS_ERROR;
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


    /*
      Update customer
    */

    public function update(){
      $customer = new OsCustomerModel();
      if($this->params['customer']['password']){
        $this->params['customer']['password'] = OsAuthHelper::hash_password($this->params['customer']['password']);
      }
      $customer->set_data($this->params['customer']);
      $custom_fields_data = isset($this->params['customer']['custom_fields']) ? $this->params['customer']['custom_fields'] : [];

      if($customer->save()){
        $customer->save_custom_fields($custom_fields_data);
        $response_html = __('Cliente actualizado correctamente:', 'latepoint');
        $status = LATEPOINT_STATUS_SUCCESS;
        OsActivitiesHelper::create_activity(array('code' => 'customer_update', 'customer_id' => $customer->id));
      }else{
        $response_html = $customer->get_error_messages();
        $status = LATEPOINT_STATUS_ERROR;
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function logout(){
      OsAuthHelper::logout_customer();
      wp_redirect(OsSettingsHelper::get_customer_dashboard_url());
    }

    public function login(){
      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }

    public function do_login(){
      $customer = OsAuthHelper::login_customer($this->params['customer_login']['email'], $this->params['customer_login']['password']);
      if($customer){
        $response_html = OsSettingsHelper::get_customer_dashboard_url();
        $status = LATEPOINT_STATUS_SUCCESS;
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Datos de usuario invalidos', 'latepoint');
      }
      $creds = array();
      $creds['user_login'] = $this->params['customer_login']['email'];
      $creds['user_password'] = $this->params['customer_login']['password'];
      $creds['remember'] = true;
      $user = wp_signon( $creds, false );
      if ( is_wp_error($user) )
      $user->get_error_message();
      $status = $status . $user->get_error_message();
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


    public function password_reset_form(){
      $this->vars['from_booking'] = (isset($this->params['from_booking']) && $this->params['from_booking']);
      $this->set_layout('none');
      return $this->format_render_return(__FUNCTION__);
    }

    public function request_password_reset_token(){
      $this->set_layout('none');
      $this->vars['from_booking'] = (isset($this->params['from_booking']) && $this->params['from_booking']);

      if(isset($this->params['password_reset_email'])){
        $customer_model = new OsCustomerModel();
        $customer = $customer_model->where(['email' => $this->params['password_reset_email']])->set_limit(1)->get_results_as_models();
        $customer_mailer = new OsCustomerMailer();
        if($customer && $customer_mailer->password_reset_request($customer, $customer->account_nonse)){
          return $this->format_render_return('password_reset_form');
        }else{
          $this->vars['reset_token_error'] = ($customer) ? __('Error! el email no fue enviado', 'latepoint') : __('El email no coincide con un cliente', 'latepoint');
          return $this->format_render_return(__FUNCTION__);
        }
      }else{
        return $this->format_render_return(__FUNCTION__);
      }
    }

    public function dashboard(){
      if(!OsAuthHelper::is_customer_logged_in()){
        $this->set_layout('none');
        return $this->format_render_return('login');
      }else{
        $customer = OsAuthHelper::get_logged_in_customer();
        $this->vars['customer'] = $customer;
        $this->set_layout('none');
        $this->vars['custom_fields_for_customer'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');
        return $this->format_render_return(__FUNCTION__);
      }
    }


    public function change_password(){
      $customer = new OsCustomerModel();
      
      if($this->params['password_reset_token'] && $customer->get_by_account_nonse($this->params['password_reset_token'])){
        if(!empty($this->params['password']) && $this->params['password'] == $this->params['password_confirmation']){
          if($customer->update_attributes(array('password' => OsAuthHelper::hash_password($this->params['password']), 'is_guest' => false))){
            $status = LATEPOINT_STATUS_SUCCESS;
            $response_html = __('Tu contraseña fue actualizada correctamente.', 'latepoint');
          }else{
            $response_html = __('Error! Message Code: KS723J', 'latepoint');
            $status = LATEPOINT_STATUS_ERROR;
          }
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Error! la contraseña no coincide.', 'latepoint');
        }
      }else{
        $response_html = __('Invalid Secret Key', 'latepoint');
        $status = LATEPOINT_STATUS_ERROR;
      }


      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function set_account_password_on_booking_completion(){

      $customer = new OsCustomerModel();
      
      if($this->params['account_nonse'] && $customer->get_by_account_nonse($this->params['account_nonse'])){
        if(!empty($this->params['password']) && $this->params['password'] == $this->params['password_confirmation']){
          if($customer->update_attributes(array('password' => OsAuthHelper::hash_password($this->params['password']), 'is_guest' => false))){
            $status = LATEPOINT_STATUS_SUCCESS;
            $response_html = __('Account Created. You can now manage your appointments.', 'latepoint');
          }else{
            $response_html = __('Error! Message Code: KS723J', 'latepoint');
            $status = LATEPOINT_STATUS_ERROR;
          }
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Error! Passwords do not match.', 'latepoint');
        }
      }else{
        $response_html = __('Error! Message Code: JS76SD', 'latepoint');
        $status = LATEPOINT_STATUS_ERROR;
      }


      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


    public function index(){

      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = 20;
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;


      $customers = new OsCustomerModel();
      $query_args = [];

      $filter = isset($this->params['filter']) ? $this->params['filter'] : false;

      // TABLE SEARCH FILTERS
      if($filter){
        if($filter['id']) $query_args['id'] = $filter['id'];
        if($filter['registration_date_from'] && $filter['registration_date_to']){
          $query_args[LATEPOINT_TABLE_CUSTOMERS.'.created_at >='] = $filter['registration_date_from'];
          $query_args[LATEPOINT_TABLE_CUSTOMERS.'.created_at <='] = $filter['registration_date_to'];
        }
        if($filter['customer']){
          $query_args['CONCAT('.LATEPOINT_TABLE_CUSTOMERS.'.first_name, " " ,'.LATEPOINT_TABLE_CUSTOMERS.'.last_name) LIKE'] = '%'.$filter['customer'].'%';
          $this->vars['customer_name_query'] = $filter['customer'];
        }
        if($filter['phone']){
          $query_args['phone LIKE'] = '%'.$filter['phone'].'%';
          $this->vars['phone_query'] = $filter['phone'];
        }
        if($filter['email']){
          $query_args['email LIKE'] = '%'.$filter['email'].'%';
          $this->vars['email_query'] = $filter['email'];
        }
      }

      if($this->logged_in_agent_id){
        $query_args['agent_id'] = $this->logged_in_agent_id;
        $customers->select(LATEPOINT_TABLE_CUSTOMERS.'.*')->join(LATEPOINT_TABLE_BOOKINGS, ['customer_id' => LATEPOINT_TABLE_CUSTOMERS.'.id'])->group_by(LATEPOINT_TABLE_CUSTOMERS.'.id');
      }

      // OUTPUT CSV IF REQUESTED
      if(isset($this->params['download']) && $this->params['download'] == 'csv'){
        $csv_filename = 'customers_'.OsUtilHelper::random_text().'.csv';
        
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$csv_filename}.csv");

        $labels_row = [ __('ID', 'latepoint'), 
                        __('Name', 'latepoint'), 
                        __('Phone', 'latepoint'), 
                        __('Email', 'latepoint'), 
                        __('Total Appointments', 'latepoint'), 
                        __('Registered On', 'latepoint') ];
        $custom_fields_for_customer = OsCustomFieldsHelper::get_custom_fields_arr('customer');
        foreach($custom_fields_for_customer as $custom_field){
          $labels_row[] = $custom_field['label'];
        }

        $customers_data = [];
        $customers_data[] = $labels_row;


        $customers_arr = $customers->where($query_args)->order_by('id desc')->get_results_as_models();                              
        if($customers_arr){
          foreach($customers_arr as $customer){
            $values_row = [ $customer->id, 
                            $customer->full_name, 
                            $customer->phone, 
                            $customer->email, 
                            $customer->total_bookings, 
                            $customer->formatted_created_date()];
            foreach($custom_fields_for_customer as $custom_field){
              $values_row[] = $customer->get_meta_by_key($custom_field['id'], '');
            }
            $customers_data[] = $values_row;
          }
        }
        OsCSVHelper::array_to_csv($customers_data);
        return;
      }

      $this->vars['customers'] = $customers->where($query_args)->set_limit($per_page)->set_offset($offset)->order_by('id desc')->get_results_as_models();

      $count_total_customers = new OsCustomerModel();
      if($this->logged_in_agent_id){
        $count_total_customers->join(LATEPOINT_TABLE_BOOKINGS, ['customer_id' => LATEPOINT_TABLE_CUSTOMERS.'.id'])->group_by(LATEPOINT_TABLE_CUSTOMERS.'.id');
        $total_customers = $count_total_customers->select('customer_id')->where($query_args)->get_results();
        $total_customers = count($total_customers);
      }else{
        $total_customers = $count_total_customers->where($query_args)->count();
      }
      $this->vars['total_customers'] = $total_customers;
      $total_pages = ceil($total_customers / $per_page);

      $this->vars['total_pages'] = $total_pages;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;
      
      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_customers']);

      $this->format_render(['json_view_name' => '_table_body', 'html_view_name' => __FUNCTION__], [], ['total_pages' => $total_pages, 'showing_from' => $this->vars['showing_from'], 'showing_to' => $this->vars['showing_to'], 'total_records' => $total_customers]);
    }



  }


endif;