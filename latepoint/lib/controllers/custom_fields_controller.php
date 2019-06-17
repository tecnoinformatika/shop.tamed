<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsCustomFieldsController' ) ) :


  class OsCustomFieldsController extends OsController {



    function __construct(){
      parent::__construct();

      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'custom_fields/';
      $this->vars['page_header'] = __('Custom Fields', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Custom Fields', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('custom_fields', 'customer_index') ) );
    }

    public function delete(){
      if(isset($this->params['id']) && !empty($this->params['id'])){
        if(OsCustomFieldsHelper::delete($this->params['id'])){
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Custom Field Removed', 'latepoint');
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Error Removing Custom Field', 'latepoint');
        }
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Invalid Field ID', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function new_form(){
      $this->vars['custom_field'] = ['id' => OsCustomFieldsHelper::generate_custom_field_id(), 
      'label' => '', 
      'type' => '', 
      'required' => 'off', 
      'width' => 'os-col-12', 
      'placeholder' => '',
      'options' => ''];
      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }

    public function save(){
      if($this->params['custom_fields']){
        foreach($this->params['custom_fields'] as $custom_field){
          $validation_errors = OsCustomFieldsHelper::has_validation_errors($custom_field);
          if(is_array($validation_errors)){
            $status = LATEPOINT_STATUS_ERROR;
            $response_html = implode(', ', $validation_errors);
          }else{
            if(OsCustomFieldsHelper::save($custom_field)){
              $status = LATEPOINT_STATUS_SUCCESS;
              $response_html = __('Custom Field Saved', 'latepoint');
            }else{
              $status = LATEPOINT_STATUS_ERROR;
              $response_html = __('Error Saving Custom Field', 'latepoint');
            }
          }
        }
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Invalid params', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function update_order(){
      $fields_for = $this->params['fields_for'];
      $ordered_fields = $this->params['ordered_fields'];
      $fields_in_db = OsCustomFieldsHelper::get_custom_fields_arr('customer');
      $ordered_fields_in_db = [];
      foreach($ordered_fields as $field_id => $field_order){
        if(isset($fields_in_db[$field_id])){
          $ordered_fields_in_db[$field_id] = $fields_in_db[$field_id];
        }
      }
      if(OsCustomFieldsHelper::save_custom_fields_arr($ordered_fields_in_db, $fields_for)){
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Order Updated', 'latepoint');
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error Updating Order of Custom Fields', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function for_customer(){

      $this->vars['page_header'] = __('Custom Fields', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Custom Fields', 'latepoint'), 'link' => false );

      $this->vars['custom_fields_for_customers'] = OsCustomFieldsHelper::get_custom_fields_arr('customer');

      $this->format_render(__FUNCTION__);

    }


  }

endif;