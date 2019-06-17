<?php
class OsServiceModel extends OsModel{
  public $id,
      $name,
      $short_description,
      $selection_image_id,
      $description_image_id,
      $is_price_variable,
      $price_min,
      $price_max,
      $charge_amount,
      $deposit_amount,
      $duration = 60,
      $buffer_before,
      $buffer_after,
      $category_id,
      $status,
      $bg_color,
      $order_number,
      $timeblock_interval,
      $is_custom_price = false,
      $is_custom_hours = false,
      $is_custom_duration = false,
      $updated_at,
      $created_at;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_SERVICES;
    $this->services_agents_table_name = LATEPOINT_TABLE_AGENTS_SERVICES;
    $this->nice_names = array(
                              'name' => __('Service Name', 'latepoint'),
                              'short_description' => __('Service Short Description', 'latepoint'),
                              'selection_image_id' => __('Service Selection Image', 'latepoint'),
                              'description_image_id' => __('Service Description Image', 'latepoint'),
                              'is_price_variable' => __('Variable Price', 'latepoint'),
                              'price_min' => __('Minimum Price', 'latepoint'),
                              'price_max' => __('Maximum Price', 'latepoint'),
                              'charge_amount' => __('Charge Amount', 'latepoint'),
                              'deposit_amount' => __('Deposit Amount', 'latepoint'),
                              'duration' => __('Service Duration', 'latepoint'),
                              'buffer_before' => __('Buffer Before', 'latepoint'),
                              'buffer_after' => __('Buffer After', 'latepoint'),
                              'bg_color' => __('Background Color', 'latepoint'),
                              'category_id' => __('Service Category', 'latepoint'));

    if($id){
      $this->load_by_id($id);
    }
  }

  public function get_timeblock_interval(){
    if(!$this->timeblock_interval){
      $this->timeblock_interval = OsSettingsHelper::get_timeblock_interval();
    }
    return $this->timeblock_interval;
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id', 
                            'name', 
                            'short_description', 
                            'category_id',
                            'selection_image_id',
                            'is_price_variable',
                            'price_min',
                            'price_max',
                            'charge_amount',
                            'deposit_amount',
                            'duration',
                            'buffer_before',
                            'buffer_after',
                            'bg_color',
                            'order_number',
                            'status',
                            'description_image_id');
    return $allowed_params;
  }


  protected function before_create(){
  }

  protected function set_defaults(){
    if(empty($this->category_id)) $this->category_id = 0;
    if(empty($this->buffer_before)) $this->buffer_before = 0;
    if(empty($this->buffer_after)) $this->buffer_after = 0;
    if(empty($this->price_min)) $this->price_min = 0;
    if(empty($this->price_max)) $this->price_max = 0;
    if(empty($this->charge_amount)) $this->charge_amount = 0;
    if(empty($this->deposit_amount)) $this->deposit_amount = 0;
    if(empty($this->is_deposit_required)) $this->is_deposit_required = false;
    if(empty($this->deposit_value)) $this->deposit_value = 0;
    if(empty($this->status)) $this->status = LATEPOINT_SERVICE_STATUS_ACTIVE;
    if(empty($this->bg_color)) $this->bg_color = $this->generate_new_bg_color();
  }

  public function save_custom_schedule($work_periods){
    foreach($work_periods as &$work_period){
      $work_period['service_id'] = $this->id;
    }
    unset($work_period);
    OsWorkPeriodsHelper::save_work_periods($work_periods);
  }

  public function delete_custom_schedule(){
    $work_periods_model = new OsWorkPeriodModel();
    $work_periods = $work_periods_model->where(array('service_id' => $this->id, 'agent_id' => 0, 'location_id' => 0, 'custom_date' => 'IS NULL'))->get_results_as_models();
    if(is_array($work_periods)){
      foreach($work_periods as $work_period){
        $work_period->delete();
      }
    }
  }

  public function generate_new_bg_color(){
    $services = new OsServiceModel();
    $service_colors_results = $services->select('bg_color')->group_by('bg_color')->get_results(ARRAY_A);
    $services_used_colors = array_map(function($service){ return $service['bg_color']; }, $service_colors_results);
    $default_colors = OsServiceHelper::get_default_colors();
    $colors_left = array_diff($default_colors, $services_used_colors);
    if(!empty($colors_left)){
      // reset array
      $colors_left = array_values($colors_left);
      $bg_color = $colors_left[0];
    }else{
      $bg_color = '#3d52ea';
    }
    return $bg_color;
  }


  public function delete($id = false){
    if(!$id && isset($this->id)){
      $id = $this->id;
    }
    if($id && $this->db->delete( $this->table_name, array('id' => $id), array( '%d' ))){
      $this->db->delete(LATEPOINT_TABLE_AGENTS_SERVICES, array('service_id' => $id), array( '%d' ) );
      $this->db->delete(LATEPOINT_TABLE_WORK_PERIODS, array('service_id' => $id), array( '%d' ) );
      return true;
    }else{
      return false;
    }
  }

  public function should_be_active(){
    return $this->where(['status' => LATEPOINT_SERVICE_STATUS_ACTIVE]);
  }

  public function is_active(){
    return ($this->status == LATEPOINT_SERVICE_STATUS_ACTIVE);
  }



  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id', 
                            'name', 
                            'short_description', 
                            'category_id',
                            'selection_image_id',
                            'is_price_variable',
                            'price_min',
                            'price_max',
                            'charge_amount',
                            'deposit_amount',
                            'duration',
                            'buffer_before',
                            'buffer_after',
                            'bg_color',
                            'order_number',
                            'status',
                            'description_image_id');
    return $params_to_save;
  }

  public function available_bookings_count($date = false, $agent_id = false){
    if(!$date) $date = OsTimeHelper::today_date('Y-m-d');
    $timeblock_interval = OsSettingsHelper::get_timeblock_interval();
    $work_periods_arr = OsBookingHelper::get_work_periods(['custom_date' => $date]);
    $available_minutes = array();
    foreach($work_periods_arr as $work_period){
      list($work_start_minutes, $work_end_minutes) = explode(':', $work_period);
      for($minutes = $work_start_minutes; $minutes <= ($work_end_minutes - $this->duration); $minutes+= $timeblock_interval){
        $is_available = true;
        $minutes_start = $minutes - $this->buffer_before;
        $minutes_end = $minutes + $this->duration + $this->buffer_after;
        if(!OsBookingHelper::is_timeframe_in_periods($minutes_start, $minutes_end, $booked_periods_arr)){
          $available_minutes[] = $minutes;
        }
      }
    }
    return count($available_minutes);
  }

  protected function get_price_min_formatted(){
    if($this->price_min > 0){
      return OsMoneyHelper::format_price($this->price_min);
    }else{
      return 0;
    }
  }


  public function get_selection_image_url(){
    $default_service_image_url = LATEPOINT_IMAGES_URL . 'service-image.png';
    return OsImageHelper::get_image_url_by_id($this->selection_image_id, 'thumbnail', $default_service_image_url);
  }


  public function connect_to_agent($agent_id, $location_id){
    $agent_connection_row = $this->db->get_row($this->db->prepare('SELECT id FROM '.$this->services_agents_table_name.' WHERE service_id = %d AND agent_id = %d AND location_id = %d', array($this->id, $agent_id, $location_id)));
    if($agent_connection_row){
      // update
    }else{
      $insert_data = array('agent_id' => $agent_id, 'service_id' => $this->id, 'location_id' => $location_id);
      if($this->db->insert($this->services_agents_table_name, $insert_data)){
        return $this->db->insert_id;
      }
    }
  }


  public function save_agents(){
    foreach($this->agents as $agent){
      $agent_connection_row = $this->db->get_row($this->db->prepare('SELECT id FROM '.$this->services_agents_table_name.' WHERE service_id = %d AND agent_id = %d', array($this->id, $agent->id)));
      if($agent_connection_row){
        $update_data = array('is_custom_hours' => $agent->is_custom_hours, 'is_custom_price' => $agent->is_custom_price, 'is_custom_duration' => $agent->is_custom_duration);
        $this->db->update($this->services_agents_table_name, $update_data, array('id' => $agent_connection_row->id));
      }else{
        $insert_data = array('agent_id' => $agent->id, 'service_id' => $this->id, 'is_custom_hours' => $agent->is_custom_hours, 'is_custom_price' => $agent->is_custom_price, 'is_custom_duration' => $agent->is_custom_duration);
        if($this->db->insert($this->services_agents_table_name, $insert_data)){
          $agent_connection_row_id = $this->db->insert_id;
        }
      }
    }
    return true;
  }



  public function remove_agents_by_ids($ids_to_remove = array()){
    if($ids_to_remove){
      $query = $this->db->prepare('DELETE FROM '.$this->services_agents_table_name.' WHERE service_id = %d AND agent_id IN ' . OsModel::where_in_array_to_string($ids_to_remove), $this->id);
      $this->db->query( $query );
    }
  }



  public function get_agent_ids_to_remove($new_agents = array()){
    $current_agent_ids = $this->get_current_agent_ids_from_db();
    $new_agent_ids = array();
    foreach($new_agents as $agent){
      if($agent['connected'] == "yes") $new_agent_ids[] = $agent['id'];
    }
    $agent_ids_to_remove = array_diff($current_agent_ids, $new_agent_ids);
    return $agent_ids_to_remove;
  }


  public function save_agents_and_locations($agents){
    if(!$agents) return true;
    $connections_to_save = [];
    $connections_to_remove = [];
    foreach($agents as $agent_key => $locations){
      $agent_id = str_replace('agent_', '', $agent_key);
      foreach($locations as $location_key => $location){
        $location_id = str_replace('location_', '', $location_key);
        $connection = ['service_id' => $this->id, 'agent_id' => $agent_id, 'location_id' => $location_id];
        if($location['connected'] == 'yes'){
          $connections_to_save[] = $connection;
        }else{
          $connections_to_remove[] = $connection;
        }
      }
    }
    if(!empty($connections_to_save)){
      foreach($connections_to_save as $connection_to_save){
        OsConnectorHelper::save_connection($connection_to_save);
      }
    }
    if(!empty($connections_to_remove)){
      foreach($connections_to_remove as $connection_to_remove){
        OsConnectorHelper::remove_connection($connection_to_remove);
      }
    }
    return true;
  }

  public function get_current_agent_ids_from_db(){
    $query = $this->db->prepare('SELECT agent_id FROM '.$this->services_agents_table_name.' WHERE service_id = %d', $this->id);
    $agent_rows = $this->db->get_results( $query );

    $agent_ids = array();

    if($agent_rows){
      foreach($agent_rows as $agent_row){
        $agent_ids[] = $agent_row->agent_id;
      }
    }
    return $agent_ids;
  }


  public function get_current_agent_ids(){
    $agent_ids = array();
    foreach($this->agents as $agent){
      $agent_ids[] = $agent->id;
    }
    return $agent_ids;
  }


  public function get_agents(){
    if(!isset($this->agents)){
      $query = 'SELECT * FROM '.$this->services_agents_table_name.' WHERE service_id = %d GROUP BY agent_id';
      $query_args = array($this->id);
      $agents_rows = $this->get_query_results( $query, $query_args );

      $this->agents = array();

      if($agents_rows){
        foreach($agents_rows as $agent_row){
          $agent = new OsAgentModel($agent_row->agent_id);
          $agent->is_custom_hours = $agent_row->is_custom_hours;
          $agent->is_custom_price = $agent_row->is_custom_price;
          $agent->is_custom_duration = $agent_row->is_custom_duration;
          $this->agents[] = $agent;
        }
      }
    }
    return $this->agents;
  }

  public function set_agents($agent_datas){
    $this->agents = array();

    foreach($agent_datas as $agent_data){
      if($agent_data['connected'] == "yes"){
        $agent = new OsAgentModel();
        $agent->id = $agent_data['id'];
        $agent->is_custom_hours = $agent_data['is_custom_hours'];
        $agent->is_custom_price = $agent_data['is_custom_price'];
        $agent->is_custom_duration = $agent_data['is_custom_duration'];
        $this->agents[] = $agent;
      }
    }
    return $this;
  }


  public function has_agent($agent_id){
    return OsConnectorHelper::has_connection(['service_id' => $this->id, 'agent_id' => $agent_id]);
  }

  public function count_number_of_connected_locations($agent_id = false){
    if($this->is_new_record()) return 0;
    $args = ['service_id' => $this->id];
    if($agent_id) $args['agent_id'] = $agent_id;
    return OsConnectorHelper::count_connections($args, 'location_id');
  }


  protected function properties_to_validate(){
    $validations = array(
      'name' => array('presence'),
      'duration' => array('presence'),
    );
    return $validations;
  }
}