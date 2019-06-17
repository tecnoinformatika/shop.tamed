<?php 
class OsGoogleCalendarHelper {
     public static function is_enabled(){
		return OsSettingsHelper::is_on('enable_google_calendar');
	}

  public static function get_events_for_date($target_date, $agent_id = false){

    $events_model = new OsGoogleCalendarEventModel();

    if(!OsTimeHelper::is_valid_date($target_date)) return [];

    $target_date = OsWpDateTime::CreateFromFormat("Y-m-d", $target_date);
    if(!$target_date) return [];

    $weekday = OsTimeHelper::get_db_weekday_by_number($target_date->format('N'));
    $events_model->escape_by_ref($weekday);
    $weekday_relative = ceil($target_date->format('j') / 7).$weekday;

    if(($target_date->format('t') - $target_date->format('j')) < 7){
      $last_weekday_query = " OR (`weekday` = '-1${weekday}') ";
    }else{
      $last_weekday_query = '';
    }

    // clean 

    $formatted_date = $target_date->format('Y-m-d');
    $events_model->escape_by_ref($formatted_date);

    $query = "SELECT events.start_time, events.end_time, events.id, events.summary FROM ".LATEPOINT_TABLE_GCAL_EVENTS." as events
              LEFT JOIN ".LATEPOINT_TABLE_GCAL_RECURRENCES." as recs ON events.id = recs.lp_event_id
              WHERE
                (`start_date` = '${formatted_date}'
                  OR (`frequency` = 'daily' 
                    AND (DATEDIFF('${formatted_date}', `start_date`) % `interval`) = 0) 
                    AND (`count` IS NULL OR FLOOR(DATEDIFF('${formatted_date}', `start_date`) / `interval`) < `count`)
                  OR (`frequency` = 'weekly' 
                    AND `weekday` = '${weekday}' 
                    AND ((FLOOR(DATEDIFF('${formatted_date}', `start_date`)/7) % `interval`) = 0) 
                    AND (`count` IS NULL OR FLOOR(FLOOR(DATEDIFF('${formatted_date}', `start_date`)/7) / `interval`) < `count`)) 
                  OR (`frequency` = 'monthly'
                    AND ((DAYOFMONTH(`start_date`) = DAYOFMONTH('${formatted_date}') OR (`weekday` = '${weekday_relative}') ${last_weekday_query}) 
                    AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '${formatted_date}'), EXTRACT(YEAR_MONTH FROM `start_date`)) % `interval`) = 0) 
                    AND (`count` IS NULL OR FLOOR(PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '${formatted_date}'), EXTRACT(YEAR_MONTH FROM `start_date`)) / `interval`) < `count`))    
                  OR (`frequency` = 'yearly' 
                    AND date_format(`start_date`, '%m%d') = date_format('${formatted_date}', '%m%d') 
                    AND ((EXTRACT(YEAR FROM '${formatted_date}') - EXTRACT(YEAR FROM `start_date`)) % `interval` = 0) 
                    AND (`count` IS NULL OR FLOOR(EXTRACT(YEAR FROM '${formatted_date}') - EXTRACT(YEAR FROM `start_date`) / `interval`) < `count`))
                )AND (`start_date` <= '${formatted_date}' AND (`until` > '${formatted_date}' OR `until` IS NULL))";
    if($agent_id) $query.= " AND agent_id = ${agent_id}";
    $events = $events_model->get_query_results( $query );
    return $events;
  }

  public static function stop_watch($agent_id){
    $agent = new OsAgentModel($agent_id);
    if(!$agent->id) return false;
    $agent_watch_channel = $agent->get_meta_by_key('google_cal_agent_watch_channel');
    if(!$agent_watch_channel) return false;
    $agent_watch_channel = json_decode($agent_watch_channel);

    $client = OsGoogleCalendarHelper::get_authorized_client_for_agent($agent->id);
    if(!$client) return false;
    $g_service = new Google_Service_Calendar($client);

    $g_channel = new Google_Service_Calendar_Channel();
    $g_channel->setId($agent_watch_channel->id);
    $g_channel->setResourceId($agent_watch_channel->resourceId);
    try{
      $response = $g_service->channels->stop($g_channel);
    }catch(Exception $e){
      error_log($e->getMessage());
    }
    $agent->delete_meta_by_key('google_cal_agent_watch_channel');
  }

  public static function refresh_watch($agent_id){
    self::stop_watch($agent_id);
    self::start_watch($agent_id);
  }

  public static function translate_weekdays($weekday){
    $weekday = str_replace(',', ', ', $weekday);
    return str_replace(['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'], [__('Lun', 'latepoint'), __('Mar', 'latepoint'), __('Mier', 'latepoint'), __('Jue', 'latepoint'), __('Vie', 'latepoint'), __('Sab', 'latepoint'), __('Dom', 'latepoint')], $weekday);
  }

  public static function get_gcal_event_recurrences($gcal_event, $split_weekdays = true){
    $rrule = false;
    $gcal_recurrence = new OsGcalEventRecurrenceModel();
    foreach($gcal_event->getRecurrence() as $rec){
      if(!strstr($rec, 'RRULE:')) continue;
      $rrule = str_replace('RRULE:', '', $rec);
    }
    if(!$rrule) return false;
    $rrules = false;
    parse_str((str_replace(';', '&', $rrule)), $rrules);
    if(!$rrules) return false;
    //$rrules = explode(';', $rrule);
    $gcal_recurrence->start_date = $gcal_event->start_date;
    if(isset($rrules['FREQ'])) $gcal_recurrence->frequency = $rrules['FREQ'];
    if(isset($rrules['UNTIL'])){
      $rrules['UNTIL'] = strtok($rrules['UNTIL'], 'T');
      $gcal_recurrence->until = $rrules['UNTIL'];
    }
    if(isset($rrules['INTERVAL'])){
      $gcal_recurrence->interval = $rrules['INTERVAL'];
    }else{
      $gcal_recurrence->interval = 1;
    }
    if(isset($rrules['COUNT'])) $gcal_recurrence->count = $rrules['COUNT'];
    
    if(isset($rrules['BYDAY'])){
      $gcal_recurrences = [];
      $weekdays = explode(',', $rrules['BYDAY']);
      if($split_weekdays && (count($weekdays) > 1)){
        foreach($weekdays as $byday){
          $gcal_recurrence->weekday = $byday;
          $gcal_recurrences[] = clone $gcal_recurrence;
        }
      }else{
        $gcal_recurrence->weekday = $rrules['BYDAY'];
      }
    }
    if(empty($gcal_recurrences)){
      $gcal_recurrences[] = $gcal_recurrence;
    }
    return $gcal_recurrences;
  }

  public static function start_watch($agent_id){
    $agent = new OsAgentModel($agent_id);
    if(!$agent->id) return false;
    $agent_watch_channel = $agent->get_meta_by_key('google_cal_agent_watch_channel');

    // no watch channel exist yet for this agent
    if(!$agent_watch_channel){
      $client = OsGoogleCalendarHelper::get_authorized_client_for_agent($agent->id);
      if(!$client) return false;
      $g_service = new Google_Service_Calendar($client);
      $calendar_id = self::get_selected_calendar_id($agent->id);
      $g_channel = new Google_Service_Calendar_Channel();
      $channel_id = 'gcal_channel_'.uniqid('');
      $g_channel->setId($channel_id);
      $g_channel->setType('web_hook');
      // in reality it expires earlier (1 month is max limit for google api as of now)
      $g_channel->setExpiration(strtotime('+2 months') * 1000);
      $g_channel->setAddress(OsRouterHelper::build_admin_post_link(['google_calendar', 'event_watch_updated'], ['agent_id' => $agent_id]));
      

      try{
        $response = $g_service->events->watch($calendar_id, $g_channel);
        if(is_array($response) && isset($response['error'])){
          OsDebugHelper::log($response);
        }elseif(is_a($response, 'Google_Service_Calendar_Channel')){
          $agent_watch_channel = json_encode(['expiration' => $response->expiration, 
                                              'id' => $response->id, 
                                              'resourceId' => $response->resourceId,
                                              'resourceUri' => $response->resourceUri,
                                              'calendar_id' => $calendar_id,
                                              'next_sync_token' => ''
                                            ]);
          $agent->save_meta_by_key('google_cal_agent_watch_channel', $agent_watch_channel);
        }
      }catch(Exception $e){
        if($e->getCode() == 401){
          throw new Exception('Auto-sync failed. You need to verify your domain in Google Developer tools.');
        }else{
          error_log($e->getMessage());
        }
      }
    }

  }

  public static function get_selected_calendar_id($agent_id){
    $selected_calendar_id = 'tamed.global_ep5g1t32e41rrpjenqekrshb4s@group.calendar.google.com';
      OsMetaHelper::save_agent_meta_by_key('google_cal_selected_calendar_id', $selected_calendar_id, $agent_id);
    if(!$selected_calendar_id){
      $selected_calendar_id = 'tamed.global_ep5g1t32e41rrpjenqekrshb4s@group.calendar.google.com';
      OsMetaHelper::save_agent_meta_by_key('google_cal_selected_calendar_id', $selected_calendar_id, $agent_id);
    }
    return $selected_calendar_id;
  }

  public static function get_record_by_google_event_id($google_event_id){
    $google_event = new OsGoogleCalendarEventModel();
    return $google_event->where(['google_event_id' => $google_event_id])->set_limit(1)->get_results_as_models();
  }

  public static function is_agent_connected_to_gcal($agent_id, $verify_token = false){
    return self::get_access_token_for_agent($agent_id);
  }

  public static function remove_booking_from_gcal($booking_id){

    $booking = new OsBookingModel();
    if(!$booking->load_by_id($booking_id)) return true;
    $calendar_id = self::get_selected_calendar_id($booking->agent_id);
    $google_calendar_event_id = $booking->get_meta_by_key('google_calendar_event_id', false);
    if(!$google_calendar_event_id) return true;
    $g_client = self::get_authorized_client_for_agent($booking->agent_id);

    $g_service = new Google_Service_Calendar($g_client);
    try{
      if($g_service->events->delete($calendar_id, $google_calendar_event_id)){
        $booking->delete_meta_by_key('google_calendar_event_id');
      }
    }catch(Exception $e){
      if($e->getCode() == 410){
        $booking->delete_meta_by_key('google_calendar_event_id');
      }
      error_log($e->getMessage());
    }
    return true;
  }

  public static function unsync_google_event_from_db($google_event_id){
    if(!$google_event_id) return true;
    $event_in_db = new OsGoogleCalendarEventModel();
    $events_to_unsync = $event_in_db->where(['google_event_id' => $google_event_id])->get_results_as_models();
    if($events_to_unsync){
      foreach($events_to_unsync as $event_model){
        $event_model->delete();
      }
    }
    return true;
  }

  public static function get_google_event_from_gcal_by_id($google_event_id, $agent_id){
    $g_client = self::get_authorized_client_for_agent($agent_id);
    $g_service = new Google_Service_Calendar($g_client);
    $event_in_gcal = $g_service->events->get(self::get_selected_calendar_id($agent_id), $google_event_id);
    return $event_in_gcal;
  }

  public static function get_list_of_calendars_for_select($agent_id){
    $calendars = self::get_list_of_calendars($agent_id);
    $calendars_for_select = [];
    if(!empty($calendars)){
      foreach($calendars as $calendar){
        $calendars_for_select[] = ['value' => $calendar['id'], 'label' => $calendar['title']];
      }
    }
    return $calendars_for_select;
  }

  public static function get_list_of_calendars($agent_id){
    $g_service = OsGoogleCalendarHelper::get_g_service_for_agent($agent_id);
    if(!$g_service) return [];
    $calendars = [];
    $calendarList = $g_service->calendarList->listCalendarList();

    while(true) {
      foreach ($calendarList->getItems() as $calendarListEntry) {
        $calendars[] = ['id' => $calendarListEntry->getId(), 'title' => $calendarListEntry->getSummary(), 'description' => $calendarListEntry->getDescription()];
      }
      $pageToken = $calendarList->getNextPageToken();
      if ($pageToken) {
        $optParams = array('pageToken' => $pageToken);
        $calendarList = $g_service->calendarList->listCalendarList($optParams);
      } else {
        break;
      }
    }
    return $calendars;
  }

  // if booking was changed on google calendar - update it on our DB
  public static function create_or_update_booking_from_event_in_db($gcal_event, $booking_id){
    $booking = new OsBookingModel($booking_id);
    if(!$booking->id) return false;

    $start_date_obj = OsWpDateTime::os_get_start_of_google_event($gcal_event);
    $end_date_obj = OsWpDateTime::os_get_end_of_google_event($gcal_event);

    $booking->start_date = $start_date_obj->format('Y-m-d');
    $booking->start_time = OsTimeHelper::convert_time_to_minutes($start_date_obj->format('H:i'), false);
    $booking->end_date = $end_date_obj->format('Y-m-d');
    $booking->end_time = OsTimeHelper::convert_time_to_minutes($end_date_obj->format('H:i'), false);
    $booking->save();
  }



  // event object can be passed as well as event id
  public static function create_or_update_google_event_in_db($google_event_id, $agent_id){
    if(!$google_event_id || !$agent_id) return true;
    // load info from google about event
    if(is_a($google_event_id, 'Google_Service_Calendar_Event')){
      $event_in_gcal = $google_event_id;
      $google_event_id = $event_in_gcal->id;
    }else{
      $event_in_gcal = self::get_google_event_from_gcal_by_id($google_event_id, $agent_id);
    }

    $start_date_obj = OsWpDateTime::os_get_start_of_google_event($event_in_gcal);
    $end_date_obj = OsWpDateTime::os_get_end_of_google_event($event_in_gcal);

    // save event info to our database
    $google_calendar_event_in_db = new OsGoogleCalendarEventModel();
    $event_in_db = $google_calendar_event_in_db->where(['google_event_id' => $google_event_id])->set_limit(1)->get_results_as_models();

    if(!$event_in_db){
      // create new
      $event_in_db = new OsGoogleCalendarEventModel();
      $event_in_db->google_event_id = $google_event_id;
    }

    $event_in_db->agent_id = $agent_id;
    $event_in_db->summary = $event_in_gcal->getSummary();
    $event_in_db->html_link = $event_in_gcal->getHtmlLink();
    $event_in_db->start_date = $start_date_obj->format('Y-m-d');
    $event_in_db->start_time = OsTimeHelper::convert_time_to_minutes($start_date_obj->format('H:i'), false);
    $event_in_db->end_date = $end_date_obj->format('Y-m-d');
    $event_in_db->end_time = OsTimeHelper::convert_time_to_minutes($end_date_obj->format('H:i'), false);

    $result = $event_in_db->save();
    if($result && $event_in_gcal->getRecurrence()){
      $recurrences = self::get_gcal_event_recurrences($event_in_gcal);
      $event_in_db->update_recurrences($recurrences);
    }

    return $result;
  }

  public static function create_or_update_booking_in_gcal($booking_id){

    $booking = new OsBookingModel();
    if(!$booking->load_by_id($booking_id)) return false;
    $calendar_id = self::get_selected_calendar_id($booking->agent_id);
    $g_client = self::get_authorized_client_for_agent($booking->agent_id);


    if($g_client){
      $g_service = new Google_Service_Calendar($g_client);
      $google_calendar_event_id = $booking->get_meta_by_key('google_calendar_event_id', false);

      if($booking->status == LATEPOINT_BOOKING_STATUS_APPROVED){
        // Status Approved, add or update event in google calendar
        // 
        $attendees = [['email' => $booking->customer->email, 'displayName' => $booking->customer->full_name]];
        $custom_fields_for_customer = OsCustomFieldsHelper::get_custom_fields_arr('customer');
        $description = __('Phone: ', 'latepoint').$booking->customer->phone."\r\n";
        foreach($custom_fields_for_customer as $custom_field){
          $description.= $custom_field['label'].': '.$booking->customer->get_meta_by_key($custom_field['id'], '')."\r\n";
        }

        $event = new Google_Service_Calendar_Event(array(
          'summary' => $booking->service->name,
          'location' => $booking->location->full_address,
          'attendees' => $attendees,
          'description' => $description,
          'start' => array(
            'dateTime' => $booking->format_start_date_and_time_for_google(),
            'timeZone' => OsTimeHelper::get_wp_timezone_name(),
          ),
          'end' => array(
            'dateTime' => $booking->format_end_date_and_time_for_google(),
            'timeZone' => OsTimeHelper::get_wp_timezone_name(),
          ),
        ));
        if($google_calendar_event_id){
          // Existing google event
          $event = $g_service->events->update($calendar_id, $google_calendar_event_id, $event);
        }else{
          // new event in google cal
          $event = $g_service->events->insert($calendar_id, $event);
          $booking->save_meta_by_key('google_calendar_event_id', $event->getId());
        }
      }else{
        // Status Not Approved, remove event from calendar if exists and clean the booking meta
        if($google_calendar_event_id){
          $g_service->events->delete($calendar_id, $google_calendar_event_id);
          $booking->delete_meta_by_key('google_calendar_event_id');
        }
      }

      return true;
    }else{
      return false;
    }

  }

	public static function get_client(){
    $g_client = new Google_Client();
    // $g_client->setApplicationName("TODO");
    $g_client->setClientId(OsSettingsHelper::get_settings_value('google_calendar_client_id'));
    $g_client->setClientSecret(OsSettingsHelper::get_settings_value('google_calendar_client_secret'));
    $g_client->setAccessType("offline");        // offline access
    $g_client->setIncludeGrantedScopes(true);   // incremental auth
		$g_client->setApprovalPrompt('force');
    $g_client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
    $g_client->setRedirectUri('postmessage');
    return $g_client;
	}

  public static function get_access_token_for_agent($agent_id){
    $agent = new OsAgentModel($agent_id);
    return $agent->get_meta_by_key('google_cal_access_token', false);
  }

  public static function get_g_service_for_agent($agent_id){
    $g_client = self::get_authorized_client_for_agent($agent_id);
    $g_service = false;
    if($g_client){
      $g_service = new Google_Service_Calendar($g_client);
    }
    return $g_service;
  }

	public static function get_authorized_client_for_agent($agent_id){
    $access_token = self::get_access_token_for_agent($agent_id);
    if(!$access_token) return false;

    $g_client = OsGoogleCalendarHelper::get_client();


    $g_client->setAccessToken($access_token);

    if ($g_client->isAccessTokenExpired()) {
      // Refresh the token if possible, else fetch a new one.
      if ($g_client->getRefreshToken()) {
          $g_client->fetchAccessTokenWithRefreshToken($g_client->getRefreshToken());
      } else {
          // Request authorization from the user.
          $authUrl = $g_client->createAuthUrl();
          printf("Open the following link in your browser:\n%s\n", $authUrl);
          print 'Enter verification code: ';
          $authCode =fgets(STDIN);

          // Exchange authorization code for an access token.
          $accessToken = $g_client->fetchAccessTokenWithAuthCode($authCode);
          $g_client->setAccessToken($accessToken);

          // Check to see if there was an error.
          if (array_key_exists('error', $accessToken)) {
              throw new Exception(join(', ', $accessToken));
          }
      }
      $access_token = $g_client->getAccessToken();
      OsMetaHelper::save_agent_meta_by_key('google_cal_selected_calendar_id', self::get_selected_calendar_id($agent_id), $agent_id);
      OsMetaHelper::save_agent_meta_by_key('google_cal_access_token', json_encode($access_token), $agent_id);
    }

    return $g_client;

	}
}