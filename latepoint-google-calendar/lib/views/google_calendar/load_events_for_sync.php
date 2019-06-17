<?php 

$agent_watch_channel = $agent->get_meta_by_key('google_cal_agent_watch_channel');
if($agent_watch_channel){
  $agent_watch_channel = json_decode($agent_watch_channel);
  echo '<div class="channel-watch-status watch-status-on">';
    echo '<div class="status-watch-label">';
      echo '<i class="latepoint-icon latepoint-icon-check"></i>';
      echo '<span class="cw-status">'.__('Google Calendar Auto-Sync is Enabled', 'latepoint').'</span>';
    echo '</div>';

    $seconds_left = ($agent_watch_channel->expiration / 1000) - time();
    $days_left = round($seconds_left / 86400);

    echo '<span class="cw-expires">'.sprintf(__('Token Expires in %d days', 'latepoint'), $days_left).'</span>';
    echo '<a href="#" class="latepoint-link" data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'refresh_watch').'" 
                      data-os-params="'.OsUtilHelper::build_os_params(['agent_id' => $agent->id]).'" 
                      data-os-success-action="reload"><span class="latepoint-icon latepoint-icon-grid-18"></span><span>'.__('Refresh Token', 'latepoint').'</span></a>';
    echo '<a href="#" class="latepoint-link cw-danger" 
                      data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'stop_watch').'" 
                      data-os-params="'.OsUtilHelper::build_os_params(['agent_id' => $agent->id]).'" 
                      data-os-success-action="reload"><span class="latepoint-icon latepoint-icon-bell-off"></span><span>'.__('Disable Auto-Sync', 'latepoint').'</span></a>';
  echo '</div>';
}else{
  echo '<div class="channel-watch-status watch-status-off">';
    echo '<div class="status-watch-label">';
      echo '<i class="latepoint-icon latepoint-icon-bell-off"></i>';
      echo '<span class="cw-status">'.__('Auto-Sync from Google Calendar is disabled', 'latepoint').'</span>';
    echo '</div>';
    echo '<a href="#" class="latepoint-link cw-enable" 
                      data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'start_watch').'" 
                      data-os-params="'.OsUtilHelper::build_os_params(['agent_id' => $agent->id]).'" 
                      data-os-success-action="reload"><span class="latepoint-icon latepoint-icon-grid-18"></span><span>'.__('Enable Auto-Sync', 'latepoint').'</span></a>';
  echo '</div>';
}
?>
<?php
if($is_google_calendar_connected){
  $prev_date = false;
  $total_events = 0;
  $total_synced_events = 0;
  $events_html = '';
  $dated_events = [];
  $recurring_events = [];
  while(true) {
    foreach ($events->getItems() as $gcal_event) {
      // if its an event set to "free" skip it
      if($gcal_event->getTransparency() == 'transparent') continue;
      // recurring connected event, skip it for now
      if(!empty($gcal_event->getRecurringEventId())) continue;
      // if its a latepoint connected booking not a google event - skip to next record
      $google_event_id = $gcal_event->getId();
      $connected_booking_id = OsMetaHelper::get_booking_id_by_meta_value('google_calendar_event_id', $google_event_id);
      if($connected_booking_id) continue;
      $total_events++;
      if($total_events >= 500) break;

      $saved_event = OsGoogleCalendarHelper::get_record_by_google_event_id($google_event_id);
      if($saved_event){
        $total_synced_events++;
        $saved_event_id = $saved_event->id;
        $saved_db_event_ids[] = $saved_event_id;
      }else{
        $saved_event_id = false;
      }
      $start_date_obj = OsWpDateTime::os_get_start_of_google_event($gcal_event);
      $end_date_obj = OsWpDateTime::os_get_end_of_google_event($gcal_event);

      if(!empty($gcal_event->getRecurrence())){
        $recurrence_info = OsGoogleCalendarHelper::get_gcal_event_recurrences($gcal_event, false);
        $recurring_events[$recurrence_info[0]->frequency][] = ['summary' => $gcal_event->getSummary(), 
                                'google_event_id' => $gcal_event->getId(), 
                                'recurrence_info' => $recurrence_info[0], 
                                'saved_event_id' => $saved_event_id, 
                                'start_date' => $start_date_obj->format('Y-m-d'), 
                                'recurrence_code' => $gcal_event->getRecurrence(),
                                'time' => $start_date_obj->format('g:i a') . ' - '. $end_date_obj->format('g:i a')];
      }else{
        $dated_events[$start_date_obj->format('Ymd')]['day'] = $start_date_obj->format('j');
        $dated_events[$start_date_obj->format('Ymd')]['month'] = $start_date_obj->format('F');
        $dated_events[$start_date_obj->format('Ymd')]['events'][] = ['summary' => $gcal_event->getSummary(), 
                                                          'google_event_id' => $gcal_event->getId(), 
                                                          'saved_event_id' => $saved_event_id, 
                                                          'start_date' => $start_date_obj->format("M j, Y"), 
                                                          'time' => $start_date_obj->format('g:i a') . ' - '. $end_date_obj->format('g:i a')];
      }
    }
    $pageToken = $events->getNextPageToken();
    if ($pageToken) {
      $optParams['pageToken'] = $pageToken;
      $events = $g_service->events->listEvents(OsGoogleCalendarHelper::get_selected_calendar_id($agent->id), $optParams);
    } else {
      break;
    }
  }
  ksort($dated_events);
  foreach($dated_events as $events_for_date){
    $events_html.= '<div class="os-booking-tiny-boxes-w">
                      <div class="os-booking-tiny-box-date">
                        <div class="os-day">'.$events_for_date['day'].'</div>
                        <div class="os-month">'.$events_for_date['month'].'</div>
                      </div>
                      <div class="os-booking-tiny-boxes-i">';
                        foreach($events_for_date['events'] as $event){
                          $synced_class = $event['saved_event_id'] ? 'is-synced' : 'not-synced';
                          $events_html.= '<div class="os-booking-tiny-box '.$synced_class.'">
                            <div class="os-booking-unsync-google-trigger" data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'unsync_event').'"
                                                                        data-os-after-call="latepoint_booking_unsynced" 
                                                                        data-os-pass-this="yes" 
                                                                        data-os-params="'.OsUtilHelper::build_os_params(['google_event_id' => $event['google_event_id'], 'agent_id' => $agent->id]).'"></div>
                            <div class="os-booking-sync-google-trigger" data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'sync_event').'"
                                                                        data-os-after-call="latepoint_booking_synced" 
                                                                        data-os-pass-this="yes" 
                                                                        data-os-params="'.OsUtilHelper::build_os_params(['google_event_id' => $event['google_event_id'], 'agent_id' => $agent->id]).'"></div>
                            <div class="os-name">'.$event['summary'].'</div>
                            <div class="os-date">'.$event['start_date'].'</div>
                            <div class="os-date">'.$event['time'].'</div>
                          </div>';
                        }
                      $events_html.= '</div>';
                    $events_html.= '</div>'; 
  }
  if(!empty($recurring_events)){
    $events_html.= '<div class="os-form-sub-header"><h3>'.__('Recurring Events', 'latepoint').'</h3></div>';
    foreach($recurring_events as $frequency => $events_for_frequency){
      $events_html.= '<div class="os-booking-tiny-boxes-w">
                        <div class="os-booking-tiny-box-date">
                          <div class="os-month">'.ucwords(strtolower($frequency)).'</div>
                        </div>
                        <div class="os-booking-tiny-boxes-i">';
                          foreach($events_for_frequency as $event){
                            $synced_class = $event['saved_event_id'] ? 'is-synced' : 'not-synced';

                            $recurrence_info = $event['recurrence_info'];
                            $interval = ($recurrence_info->interval > 1) ? $recurrence_info->interval : '';
                            $weekday = OsGoogleCalendarHelper::translate_weekdays($recurrence_info->weekday);
                            switch($recurrence_info->frequency){
                              case 'YEARLY';
                                $interval = ($interval) ? $interval.__(' years') : 'year';
                                $when = __('Every', 'latepoint').' '.$interval.' '.__('on', 'latepoint').' '.date_i18n("F j", strtotime($event['start_date']));
                                break;
                              case 'MONTHLY':
                                $interval = ($interval) ? $interval.__(' months') : 'month';
                                $when = ($weekday) ? $weekday : __('day', 'latepoint').' '.date_i18n("j", strtotime($event['start_date']));
                                switch(substr($when, 0, 1)){
                                  case '-':
                                    $when = __('last','latepoint').' '.str_replace('-1', '', $weekday);
                                    break;
                                  case '1':
                                    $when = __('first').' '.str_replace('1', '', $weekday);
                                    break;
                                  case '2':
                                    $when = __('second').' '.str_replace('2', '', $weekday);
                                    break;
                                  case '3':
                                    $when = __('third').' '.str_replace('3', '', $weekday);
                                    break;
                                  case '4':
                                    $when = __('fourth').' '.str_replace('4', '', $weekday);
                                    break;
                                }
                                $when = __('Every', 'latepoint').' '.$interval.' '.__('on', 'latepoint').' '.$when;
                                break;
                              case 'WEEKLY':
                                $interval = ($interval) ? $interval.__(' weeks') : 'week';
                                $when = __('Every', 'latepoint').' '.$interval.' '.__('on', 'latepoint').' '.$weekday;
                                break;
                              case 'DAILY';
                                $interval = ($interval) ? $interval.__(' days') : 'day';
                                $when = __('Every', 'latepoint').' '.$interval.' '.__('starting', 'latepoint').' '.date_i18n("F j, Y", strtotime($event['start_date']));
                                break;
                            }
                            $events_html.= '<div class="os-booking-tiny-box '.$synced_class.'">
                              <div class="os-booking-unsync-google-trigger" data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'unsync_event').'"
                                                                          data-os-after-call="latepoint_booking_unsynced" 
                                                                          data-os-pass-this="yes" 
                                                                          data-os-params="'.OsUtilHelper::build_os_params(['google_event_id' => $event['google_event_id'], 'agent_id' => $agent->id]).'"></div>
                              <div class="os-booking-sync-google-trigger" data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'sync_event').'"
                                                                          data-os-after-call="latepoint_booking_synced" 
                                                                          data-os-pass-this="yes" 
                                                                          data-os-params="'.OsUtilHelper::build_os_params(['google_event_id' => $event['google_event_id'], 'agent_id' => $agent->id]).'"></div>
                              <div class="os-name">'.$event['summary'].'</div>
                              <div class="os-date">'.$when.'</div>
                              <div class="os-date">'.$event['time'].'</div>
                            </div>';
                          }
                        $events_html.= '</div>';
                      $events_html.= '</div>'; 
    }
  }
  $deleted_events = new OsGoogleCalendarEventModel(); 
  if(!empty($saved_db_event_ids)) $deleted_events->where(['id NOT IN ' => $saved_db_event_ids]);
  $deleted_events = $deleted_events->where(['start_date >' => OsTimeHelper::today_date(), 'agent_id' => $agent->id])->get_results_as_models();

  $deleted_recurring_events = new OsGoogleCalendarEventModel(); 
  if(!empty($saved_db_event_ids)) $deleted_recurring_events->where(['lp_event_id NOT IN ' => $saved_db_event_ids]);
  $deleted_recurring_events = $deleted_recurring_events->join(LATEPOINT_TABLE_GCAL_RECURRENCES, ['lp_event_id' => LATEPOINT_TABLE_GCAL_EVENTS.'.id'])->group_by(LATEPOINT_TABLE_GCAL_EVENTS.'.id')->where(['start_date <=' => OsTimeHelper::today_date(), 'agent_id' => $agent->id, 'until >=' => OsTimeHelper::today_date()])->get_results_as_models();

  if($deleted_recurring_events){
    if($deleted_events){
      $deleted_events = array_merge($deleted_events, $deleted_recurring_events);
    }else{
      $deleted_events = $deleted_recurring_events;
    }
  }

  if($deleted_events){
    $events_html.= '<div class="os-form-sub-header"><h3>'.__('Not in Google Calendar anymore', 'latepoint').'</h3></div>';
    $events_html.= '<div class="os-booking-tiny-boxes-w">
                    <div class="os-booking-tiny-box-date">
                      <div class="os-month">'.__('Not Found', 'latepoint').'</div>
                    </div>
                    <div class="os-booking-tiny-boxes-i">';
                      foreach($deleted_events as $event){
                        $events_html.= '<div class="os-booking-tiny-box is-synced-not-exist">
                          <div class="os-booking-unsync-google-trigger" data-os-action="'.OsRouterHelper::build_route_name('google_calendar', 'unsync_event').'"
                                                                      data-os-after-call="latepoint_gcal_event_deleted" 
                                                                      data-os-pass-this="yes" 
                                                                      data-os-params="'.OsUtilHelper::build_os_params(['google_event_id' => $event->google_event_id]).'"></div>
                          <div class="os-name">'.$event->summary.'</div>
                          <div class="os-date">'.$event->nice_start_date.'</div>
                          <div class="os-date">'.$event->nice_start_time.'</div>
                        </div>';
                      }
                    $events_html.= '</div>';
                  $events_html.= '</div>';
  }
  $synced_bookings_percent = ($total_events) ? round(($total_synced_events / $total_events) * 100) : 0;
  ?>

  <div class="os-sync-stat-tiles">
    <div class="os-info-tile os-tile-with-progress">
      <div class="os-tile-value"><?php echo '<span>'.$total_synced_events.'</span>'.__(' of ', 'latepoint').$total_events; ?></div>
      <div class="os-tile-label"><?php _e('Events Synced', 'latepoint'); ?></div>
      <a href="#" data-label-sync="<?php _e('Sync All Events Now', 'latepoint'); ?>" data-label-cancel-sync="<?php _e('Stop Syncing Now', 'latepoint'); ?>" class="sync-all-bookings-to-google-trigger latepoint-btn latepoint-btn-outline latepoint-btn-sm">
        <i class="latepoint-icon latepoint-icon-grid-18"></i>
        <span><?php _e('Sync All Events Now', 'latepoint'); ?></span>
      </a>
      <div class="os-tile-hor-progress-chart" data-total="<?php echo $total_events; ?>" data-value="<?php echo $total_synced_events; ?>"><div class="os-tile-hor-progress-chart-value" style="width: <?php echo $synced_bookings_percent; ?>%"></div></div>
    </div>
  </div>


  <div class="os-booking-tiny-boxes-container">
    <?php echo $events_html; ?>
  </div><?php
}else{
  _e('Google Calendar is Not Connected to this agent', 'latepoint');
}