<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsDashboardController' ) ) :


  class OsDashboardController extends OsController {

    private $booking;

    function __construct(){
      parent::__construct();

      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'dashboard/';
      $this->vars['page_header'] = __('Dashboard', 'latepoint');
    }

    public function for_agent(){

      ob_start();
      $this->widget_agents_availability_timeline();
      $this->vars['widget_agents_availability_timeline'] = ob_get_clean();

      ob_start();
      $this->widget_agents_bookings_timeline();
      $this->vars['widget_agents_bookings_timeline'] = ob_get_clean();

      ob_start();
      $wdb_date_from = new OsWpDateTime('-1 month');
      $wdb_date_to = new OsWpDateTime('now');
      $this->widget_daily_bookings_chart($wdb_date_from, $wdb_date_to);
      $this->vars['widget_daily_bookings_chart'] = ob_get_clean();

      ob_start();
      $this->widget_upcoming_appointments();
      $this->vars['widget_upcoming_appointments'] = ob_get_clean();

      $services = new OsServiceModel();
      $agents = new OsAgentModel();

      $services_models = $services->get_results_as_models();
      $this->vars['services'] = $services_models;
      $this->vars['agents'] = $agents->where(['id' => $this->logged_in_agent_id])->get_results_as_models();

      $today_date = new OsWpDateTime('today');

      if(isset($this->params['target_date'])){
        $target_date = new OsWpDateTime($this->params['target_date']);
      }else{
        $target_date = new OsWpDateTime('today');
      }
      $services_count_by_types = OsBookingHelper::get_services_count_by_type_for_date($target_date->format('Y-m-d'), $this->logged_in_agent_id);


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

      $bookings = OsBookingHelper::get_bookings_for_date($target_date->format('Y-m-d'), ['agent_id' => $this->logged_in_agent_id]);
      $this->vars['total_bookings'] = $bookings ? count($bookings) : 0;
      $this->vars['total_openings'] = OsAgentHelper::count_openings_for_date($this->logged_in_agent, $services_models[0], OsLocationHelper::get_selected_location(), $target_date->format('Y-m-d'));
      $this->vars['total_pending_bookings'] = OsBookingHelper::count_pending_bookings($this->logged_in_agent_id, OsLocationHelper::get_selected_location_id());


      $this->vars['nice_selected_date'] = OsTimeHelper::nice_date($target_date->format('Y-m-d'));

      $this->vars['today_date'] = $today_date;
      $this->vars['target_date'] = $target_date;

      $this->set_layout('admin');
      $this->format_render(__FUNCTION__);
    }



    /*
      Index
    */

    public function index(){

      $services = new OsServiceModel();
      $agents = new OsAgentModel();


      $time = new OsWpDateTime('now');
      $date_to = $time->format('Y-m-d');
      $date_from = $time->modify('-1 week')->format('Y-m-d');

      $services_models = $services->get_results_as_models();
      $this->vars['services'] = $services_models;
      $this->vars['agents'] = $agents->get_results_as_models();

      $this->vars['selected_service'] = $services_models[0];

      ob_start();
      $this->widget_top_agents();
      $this->vars['widget_top_agents'] = ob_get_clean();

      ob_start();
      $this->widget_agents_availability_timeline();
      $this->vars['widget_agents_availability_timeline'] = ob_get_clean();

      ob_start();
      $this->widget_agents_bookings_timeline();
      $this->vars['widget_agents_bookings_timeline'] = ob_get_clean();

      ob_start();
      $this->widget_daily_bookings_chart();
      $this->vars['widget_daily_bookings_chart'] = ob_get_clean();

      ob_start();
      $this->widget_upcoming_appointments();
      $this->vars['widget_upcoming_appointments'] = ob_get_clean();

      $this->set_layout('admin');
      $this->format_render(__FUNCTION__);
    }


    public function widget_upcoming_appointments(){
      $agents = new OsAgentModel();
      $services = new OsServiceModel();
      $bookings = new OsBookingModel();

      $agent_id = isset($this->params['agent_id']) ? $this->params['agent_id'] : false;
      if($this->logged_in_agent_id) $agent_id = $this->logged_in_agent_id;

      $service_id = isset($this->params['service_id']) ? $this->params['service_id'] : false;

      $this->vars['upcoming_bookings'] = $bookings->get_upcoming_bookings($agent_id, false, $service_id, OsLocationHelper::get_selected_location_id());

      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);

      $this->vars['agents'] = $agents->get_results_as_models();
      $this->vars['services'] = $services->get_results_as_models();

      $this->vars['agent_id'] = $agent_id;
      $this->vars['service_id'] = $service_id;


      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }



    public function widget_daily_bookings_chart($date_from = false, $date_to = false){
      if($date_from == false){
        $date_from = isset($this->params['date_from']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_from']) : new OsWpDateTime('-10 days');
      }
      if($date_to == false){
        $date_to = isset($this->params['date_to']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_to']) : new OsWpDateTime('now');
      }

      $agent_id = isset($this->params['agent_id']) ? $this->params['agent_id'] : false;
      $service_id = isset($this->params['service_id']) ? $this->params['service_id'] : false;

      $daily_bookings = OsBookingHelper::get_bookings_per_day_for_period($date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $service_id, $agent_id, OsLocationHelper::get_selected_location_id());
      $daily_bookings_chart_labels = array();
      $daily_bookings_chart_data_values = array();
      foreach($daily_bookings as $bookings_for_day){
        $daily_bookings_chart_labels[] = date( 'M j', strtotime($bookings_for_day->start_date));
        $daily_bookings_chart_data_values[] = $bookings_for_day->bookings_per_day;
      }


      $agents = new OsAgentModel();
      $services = new OsServiceModel();
      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);

      $this->vars['agents'] = $agents->get_results_as_models();
      $this->vars['services'] = $services->get_results_as_models();

      $this->vars['agent_id'] = $agent_id;
      $this->vars['service_id'] = $service_id;

      $this->vars['date_from'] = $date_from->format('Y-m-d');
      $this->vars['date_to'] = $date_to->format('Y-m-d');

      $this->vars['daily_bookings_chart_labels_string'] = implode(',', $daily_bookings_chart_labels);
      $this->vars['daily_bookings_chart_data_values_string'] = implode(',', $daily_bookings_chart_data_values);

      $this->vars['date_period_string'] = $date_from->format('M j, Y').' - '.$date_to->format('M j, Y');

      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }


    public function widget_agents_availability_timeline(){
      $target_date = isset($this->params['date_from']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_from']) : new OsWpDateTime('now');

      $agents = new OsAgentModel();
      $services = new OsServiceModel();

      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);
      $agents = $agents->get_results_as_models();
      $services = $services->get_results_as_models();

      if($services){
        $selected_service_id = isset($this->params['service_id']) ? $this->params['service_id'] : $services[0]->id;
      }else{
        $selected_service_id = false;
      }
      $this->vars['service_id'] = $selected_service_id;

      $selected_service = new OsServiceModel($selected_service_id);
      $this->vars['selected_service'] = $selected_service;

      $this->vars['target_date'] = $target_date->format('Y-m-d');
      $this->vars['target_date_string'] = $target_date->format('M j, Y');

      $this->set_layout('none');
      $this->vars['agents'] = $agents;
      $this->vars['services'] = $services;

      $this->format_render(__FUNCTION__);
    }


    public function widget_agents_bookings_timeline(){
      $target_date = isset($this->params['date_from']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_from']) : new OsWpDateTime('now');

      $agents = new OsAgentModel();
      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);
      $this->vars['agents'] = $agents->get_results_as_models();

      $this->vars['target_date'] = $target_date->format('Y-m-d');
      $this->vars['target_date_string'] = $target_date->format('M j, Y');

      $this->set_layout('none');

      $this->format_render(__FUNCTION__);
    }


    public function widget_top_agents(){
      $date_from = isset($this->params['date_from']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_from']) : new OsWpDateTime('-1 week');
      $date_to = isset($this->params['date_to']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_to']) : new OsWpDateTime('now');

      $this->vars['top_agents'] = OsAgentHelper::get_top_agents($date_from->format('Y-m-d'), $date_to->format('Y-m-d'), 4, OsLocationHelper::get_selected_location_id());
      $this->vars['date_from'] = $date_from->format('Y-m-d');
      $this->vars['date_to'] = $date_to->format('Y-m-d');
      $this->vars['date_period_string'] = $date_from->format('M j, Y').' - '.$date_to->format('M j, Y');

      $bookings = new OsBookingModel();
      $this->vars['total_bookings'] = $bookings->should_be_active()->where(['start_date >=' => $date_from->format('Y-m-d'), 'start_date <=' => $date_to->format('Y-m-d')])->count();

      $this->set_layout('none');

      $this->format_render(__FUNCTION__);
    }


  }

endif;