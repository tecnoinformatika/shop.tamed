<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsGoogleCalendarController' ) ) :


  class OsGoogleCalendarController extends OsController {



    function __construct(){
      parent::__construct();
      
      $this->views_folder = plugin_dir_path( __FILE__ ) . '../views/google_calendar/';
      $this->vars['page_header'] = __('Agents', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Agents', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'index') ) );
    }


    function connect(){
      $client = OsGoogleCalendarHelper::get_client();
      $auth_result = $client->authenticate($this->params['code']);
      $access_token = $client->getAccessToken();

      $agent = new OsAgentModel($this->params['agent_id']);
      $agent->save_meta_by_key('google_cal_access_token', json_encode($access_token));

      $status = LATEPOINT_STATUS_SUCCESS;
      $response_html = __('Google Calendar Connected', 'latepoint');

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }

    }

    public function disconnect(){
      $agent_id = $this->params['agent_id'];
      $agent = new OsAgentModel($this->params['agent_id']);
      if(!$agent->id) return false;
      OsGoogleCalendarHelper::stop_watch($this->params['agent_id']);
      $agent->delete_meta_by_key('google_cal_access_token');
      $agent->delete_meta_by_key('google_cal_selected_calendar_id');
      $gcal_event_model = new OsGoogleCalendarEventModel();
      $gcal_events = $gcal_event_model->where(['agent_id' => $agent->id])->get_results_as_models();
      if($gcal_events){
        foreach($gcal_events as $gcal_event){
          $gcal_event->delete();
        }
      }
      // Delete meta of connected google events (do we need it??? danger is that they accidentially disconnect and then end up with duplicate events on their gcal)
      // $bookings = new OsBookingModel();
      // $booking_ids_for_agent = $bookings->select('id')->where(['agent_id' => $agent->id])->get_results();
      // foreach($booking_ids_for_agent as $booking_to_remove){
      //   OsMetaHelper::delete_booking_meta('google_calendar_event_id', $booking_to_remove->id);
      // }
      $status = LATEPOINT_STATUS_SUCCESS;
      $response_html = __('Google Calendar Disconnected', 'latepoint');
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function remove_booking(){
      if(!$this->params['booking_id']) return;
      if(OsGoogleCalendarHelper::remove_booking_from_gcal($this->params['booking_id'])){
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Booking #'.$this->params['booking_id'].' Removed from Google Calendar Successfully', 'latepoint');
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Booking #'.$this->params['booking_id'].' Removal Failed', 'latepoint');
      }
      

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function sync_booking(){
      if(!$this->params['booking_id']) return;
      if(OsGoogleCalendarHelper::create_or_update_booking_in_gcal($this->params['booking_id'])){
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Booking #'.$this->params['booking_id'].' Synced to Google Calendar Successfully', 'latepoint');
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Booking #'.$this->params['booking_id'].' Sync Failed', 'latepoint');
      }
      

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function event_watch_updated(){
      // $_SERVER['HTTP_X_GOOG_CHANNEL_ID'] => gcal_watch_5c9bf57b20967\n    
      // $_SERVER['HTTP_X_GOOG_CHANNEL_EXPIRATION'] => Wed, 03 Apr 2019 22:13:16 GMT\n    
      // $_SERVER['HTTP_X_GOOG_RESOURCE_STATE'] => exists\n    
      // $_SERVER['HTTP_X_GOOG_MESSAGE_NUMBER'] => 6619118\n    
      // $_SERVER['HTTP_X_GOOG_RESOURCE_ID'] => YVll20HrjgCU7eUXYajS8aMn2qo\n    
      // $_SERVER['HTTP_X_GOOG_RESOURCE_URI'] => https://www.googleapis.com/calendar/v3/calendars/primary/events?maxResults=250&alt=json\n  

      $agent = new OsAgentModel($this->params['agent_id']);
      if(!$agent) exit;
      $agent_watch_channel = $agent->get_meta_by_key('google_cal_agent_watch_channel');
      if(!$agent_watch_channel) exit;
      $agent_watch_channel = json_decode($agent_watch_channel);
      // if($_SERVER['HTTP_X_GOOG_CHANNEL_ID'] != $agent_watch_channel->id) exit;
      $client = OsGoogleCalendarHelper::get_authorized_client_for_agent($agent->id);
      if($client){
        $g_service = new Google_Service_Calendar($client);
        $calendar_id = OsGoogleCalendarHelper::get_selected_calendar_id($agent->id);

        $optParams = array(
          'timeZone' => OsTimeHelper::get_wp_timezone_name(),
        );

        if(!empty($agent_watch_channel->next_sync_token)){
          $optParams['syncToken'] = $agent_watch_channel->next_sync_token;
        }else{
          $optParams['timeMin'] = OsTimeHelper::today_date('c');
        }
        $gcal_events = $g_service->events->listEvents($calendar_id, $optParams);
        $total_events = 0;
        while(true){
          // loop through all pages of events
          foreach ($gcal_events->getItems() as $gcal_event){
            // echo '<pre>';
            // print_r($gcal_event);
            // echo '</pre>';
            // is latepoint booking?
            $booking_id = OsMetaHelper::get_booking_id_by_meta_value('google_calendar_event_id', $gcal_event->getId());
            if($booking_id){
              if($gcal_event->status == 'cancelled'){
                // unsync from our db
                $booking = new OsBookingModel($booking_id);
                if($booking->id) $booking->delete_meta_by_key('google_calendar_event_id');
              }else{
                OsGoogleCalendarHelper::create_or_update_booking_from_event_in_db($gcal_event, $booking_id);
              }
            }else{
              if($gcal_event->status == 'confirmed' && $gcal_event->transparency != 'transparent'){
                OsGoogleCalendarHelper::create_or_update_google_event_in_db($gcal_event, $this->params['agent_id']);
              }elseif($gcal_event->status == 'cancelled' || $gcal_event->transparency == 'transparent'){
                // if cancelled or is set to slot in gcal set to "FREE" unsync it
                OsGoogleCalendarHelper::unsync_google_event_from_db($gcal_event->getId());
              }
            }

            $total_events++;
            if($total_events >= 500) break;
          }
          $pageToken = $gcal_events->getNextPageToken();
          $syncToken = $gcal_events->getNextSyncToken();
          if(!empty($syncToken)){
            // save synctoken
            $agent_watch_channel->next_sync_token = $syncToken;
            $agent->save_meta_by_key('google_cal_agent_watch_channel', json_encode($agent_watch_channel));
          }
          if ($pageToken) {
            // not last page, get next page
            $optParams['pageToken'] = $pageToken;
            $gcal_events = $g_service->events->listEvents(OsGoogleCalendarHelper::get_selected_calendar_id($agent->id), $optParams);
          } else {
            // last page - break
            break;
          }
        }
      }
    }


    public function unsync_event(){
      if(!$this->params['google_event_id']) return;
      if(OsGoogleCalendarHelper::unsync_google_event_from_db($this->params['google_event_id'])){
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Event Unsynced Successfully', 'latepoint');
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Event Unsync Failed', 'latepoint');
      }
      

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


    public function sync_event(){
      if(!$this->params['google_event_id'] || !$this->params['agent_id']) return;
      if(OsGoogleCalendarHelper::create_or_update_google_event_in_db($this->params['google_event_id'], $this->params['agent_id'])){
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Event Synced Successfully', 'latepoint');
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Event Sync Failed', 'latepoint');
      }
      

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function start_watch(){
      try{
        OsGoogleCalendarHelper::start_watch($this->params['agent_id']);
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Auto-sync with Google Calendar Enabled', 'latepoint');
      }catch(Exception $e){
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = $e->getMessage();
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function refresh_watch(){
      try{
        OsGoogleCalendarHelper::refresh_watch($this->params['agent_id']);
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Token Refreshed', 'latepoint');
      }catch(Exception $e){
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = $e->getMessage();
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function stop_watch(){
      OsGoogleCalendarHelper::stop_watch($this->params['agent_id']);
      $status = LATEPOINT_STATUS_SUCCESS;
      $response_html = __('Auto-sync Disabled', 'latepoint');      
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function load_events_for_sync(){
      $agent = new OsAgentModel($this->params['agent_id']);
      $this->vars['agent'] = $agent;
      $this->vars['page_header'] = [['label' => __('Bookings in LatePoint', 'latepoint'), 'link' => OsRouterHelper::build_link(['google_calendar', 'list_bookings_for_sync'], ['agent_id' => $agent->id])], 
                                    ['label' => __('Google Calendar Events', 'latepoint'), 'active' => true, 'link' => OsRouterHelper::build_link(['google_calendar', 'load_events_for_sync'], ['agent_id' => $agent->id])]];
      $this->vars['breadcrumbs'][] = array('label' => $agent->full_name, 'link' => OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => $agent->id] ) );
      $this->vars['breadcrumbs'][] = array('label' => __('Load Google Calendar Events', 'latepoint'), 'link' => false );
      $client = OsGoogleCalendarHelper::get_authorized_client_for_agent($this->params['agent_id']);
      if($client){
        $g_service = new Google_Service_Calendar($client);
        $calendar_id = OsGoogleCalendarHelper::get_selected_calendar_id($agent->id);

        $optParams = array(
          'timeZone' => OsTimeHelper::get_wp_timezone_name(),
          'timeMin' => OsTimeHelper::today_date('c'),
        );
        // https://developers.google.com/calendar/v3/reference/events/list
        $this->vars['events'] = $g_service->events->listEvents($calendar_id, $optParams);
        $this->vars['g_service'] = $g_service;
        $this->vars['optParams'] = $optParams;
        $this->vars['is_google_calendar_connected'] = true;
      }else{
        $this->vars['is_google_calendar_connected'] = false;
      }

      $this->format_render(__FUNCTION__);
    }

    public function list_bookings_for_sync(){
      $agent = new OsAgentModel($this->params['agent_id']);

      $this->vars['page_header'] = [['label' => __('Bookings in LatePoint', 'latepoint'), 'active' => true, 'link' => OsRouterHelper::build_link(['google_calendar', 'list_bookings_for_sync'], ['agent_id' => $agent->id])], 
                                    ['label' => __('Google Calendar Events', 'latepoint'), 'link' => OsRouterHelper::build_link(['google_calendar', 'load_events_for_sync'], ['agent_id' => $agent->id])]];

      $this->vars['breadcrumbs'][] = array('label' => $agent->full_name, 'link' => OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => $agent->id] ) );
      $this->vars['breadcrumbs'][] = array('label' => __('Sync upcoming bookings', 'latepoint'), 'link' => false );
      $this->vars['agent'] = $agent;

      if(OsGoogleCalendarHelper::is_agent_connected_to_gcal($this->params['agent_id'])){
        $this->vars['future_bookings'] = $agent->future_bookings;
        $this->vars['total_future_bookings'] = $agent->total_future_bookings;
        $this->vars['total_synced_future_bookings'] = $agent->total_synced_future_bookings;
        $this->vars['is_google_calendar_connected'] = true;
        $this->vars['synced_bookings_percent'] = ($this->vars['total_future_bookings']) ? round(($this->vars['total_synced_future_bookings'] / $this->vars['total_future_bookings']) * 100) : 0;
      }else{
        $this->vars['is_google_calendar_connected'] = false;
      }
      $this->format_render(__FUNCTION__);
    }

  }


endif;