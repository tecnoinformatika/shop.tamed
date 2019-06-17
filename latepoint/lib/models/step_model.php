<?php

class OsStepModel extends OsModel{
  var $id,
      $name,
      $order_number,
      $icon_image_id,
      $title,
      $sub_title,
      $use_custom_image = 'off',
      $updated_at,
      $created_at,
      $description;



  function __construct($step = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_STEP_SETTINGS;
    $this->nice_names = array();

    if($step){
      $this->name = $step;
      foreach($this->allowed_params() as $param){
        $default = $this->get_default_value($param, $step);
        if($default) $this->$param = $this->get_default_value($param, $step);
      }
      $step_settings = $this->where(array('step' => $step))->get_results();
      foreach($step_settings as $step_setting){
        if(array_search($step_setting->label, $this->allowed_params()) && property_exists($this, $step_setting->label)){
          $label = $step_setting->label;
          $this->$label = $step_setting->value;
        }
      }
    }
  }

  public function is_using_custom_image(){
    return ($this->use_custom_image == 'on');
  }


  protected function get_icon_image_url(){
    if($this->is_using_custom_image()){
      if(!$this->icon_image_id){
        return '';
      }else{
        return OsImageHelper::get_image_url_by_id($this->icon_image_id);
      }
    }else{
      $color_scheme = OsSettingsHelper::get_booking_form_color_scheme();
      return LATEPOINT_IMAGES_URL.'steps/colors/'.$color_scheme.'/'.$this->name.'.png';
    }
  }


  protected function before_save(){

  }


  public function save(){
    $this->before_save();
    if($this->validate()){
      $step_settings = $this->where(array('step' => $this->name))->get_results();
      foreach($this->allowed_params() as $param){
        $param_exists_in_db = false;
        foreach($step_settings as $step_setting){
          if($step_setting->label == $param){
            // Update
            $this->db->update(
              $this->table_name, 
              array('value' => $this->prepare_param($param, $this->$param), 'updated_at' => OsTimeHelper::today_date("Y-m-d H:i:s")), 
              array('step' => $this->name, 'label' => $param));
            OsDebugHelper::log($this->last_query);
            $param_exists_in_db = true;
          }
        }
        if(!$param_exists_in_db){
          // New
          $this->db->insert(
            $this->table_name, 
            array('label' => $param, 'value' => $this->prepare_param($param, $this->$param), 'step' => $this->name, 'updated_at' => OsTimeHelper::today_date("Y-m-d H:i:s"), 'created_at' => OsTimeHelper::today_date("Y-m-d H:i:s"),));
          OsDebugHelper::log($this->last_query);
        }
      }
    }else{
      return false;  
    }
    return true;
  }



  protected function params_to_save($role = 'admin'){
    $params_to_save = array('order_number',
                            'icon_image_id',
                            'title',
                            'sub_title',
                            'use_custom_image',
                            'description');
    return $params_to_save;
  }


  protected function allowed_params($role = 'admin'){
    $allowed_params = array('order_number',
                            'icon_image_id',
                            'title',
                            'sub_title',
                            'use_custom_image',
                            'description');
    return $allowed_params;
  }


  function get_default_value($property, $step){
    $defaults = array( 
      'locations' => array(
          'title' => __('Select Location', 'latepoint'),
          'order_number' => 1,
          'sub_title' => __('Select Location', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint')
      ),
      'services' => array(
          'title' => __('Select Service', 'latepoint'),
          'order_number' => 2,
          'sub_title' => __('Select Service', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint')
      ),
      'agents' => array(
          'title' => __('Select Agent', 'latepoint'),
          'order_number' => 3,
          'sub_title' => __('Select Agent', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint')
      ),
      'datepicker' => array(
          'title' => __('Select Date & Time', 'latepoint'),
          'order_number' => 4,
          'sub_title' => __('Select Date & Time', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint')
      ),
      'contact' => array(
          'title' => __('Enter Information', 'latepoint'),
          'order_number' => 5,
          'sub_title' => __('Enter Information', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint')
      ),
      'payment' => array(
          'title' => __('Select Payment Method', 'latepoint'),
          'order_number' => 6,
          'sub_title' => __('Enter Payment Information', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint')
      ),
      'verify' => array(
          'title' => __('Verify Order Details', 'latepoint'),
          'order_number' => 7,
          'sub_title' => __('Verify Booking Details', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint')
      ),
      'confirmation' => array(
          'title' => __('Confirmation', 'latepoint'),
          'order_number' => 8,
          'sub_title' => __('Appointment Confirmation', 'latepoint'),
          'description' => __('Handles different career a accordingly, after a of the for found customary feedback by happiness', 'latepoint'),
      )
    );
    if(isset($defaults[$step]) && isset($defaults[$step][$property])){
      return $defaults[$step][$property];
    }else{
      return false;
    }
  }

  protected function properties_to_validate(){
    $validations = array(
      'name' => array('presence'),
      'title' => array('presence'),
      'sub_title' => array('presence'),
      'order_number' => array('presence'),
    );
    return $validations;
  }
}