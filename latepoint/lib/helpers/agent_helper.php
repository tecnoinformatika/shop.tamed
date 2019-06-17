<?php 

class OsAgentHelper {

  public static function get_full_name($agent){
  	return join(' ', array($agent->first_name, $agent->last_name));
  }

  public static function get_agents_for_service_and_location($service_id = false, $location_id = false){
    return OsConnectorHelper::get_connected_object_ids('agent_id', ['service_id' => $service_id, 'location_id' => $location_id]);
  }

  public static function is_agent_available_on($agent_id, $start_date, $start_minutes, $duration, $service_id, $location_id){
    $work_periods_arr = OsBookingHelper::get_work_periods(['custom_date' => $start_date, 'service_id' => $service_id, 'agent_id' => $agent_id, 'location_id' => $location_id]);
    $booked_periods_arr = OsBookingHelper::get_bookings_times_for_date($start_date, $agent_id);
    $is_available = true;
    if(OsBookingHelper::is_timeframe_in_periods($start_minutes, $start_minutes + $duration, $booked_periods_arr)){
      $is_available = false;
    }
    if(!OsBookingHelper::is_timeframe_in_periods($start_minutes, $start_minutes + $duration, $work_periods_arr, true)){
      $is_available = false;
    }
    return $is_available;
  }

  public static function get_agents_list(){
    $agents = new OsAgentModel();
    $agents = $agents->get_results_as_models();
    $agents_list = [];
    foreach($agents as $agent){
      $agents_list[] = ['value' => $agent->id, 'label' => $agent->full_name];
    }
    return $agents_list;
  }

  public static function get_avatar_url($agent){
    $default_avatar = LATEPOINT_DEFAULT_AVATAR_URL;
    return OsImageHelper::get_image_url_by_id($agent->avatar_image_id, 'thumbnail', $default_avatar);
  }

  public static function get_bio_image_url($agent){
    $default_bio_image = LATEPOINT_DEFAULT_AVATAR_URL;
    return OsImageHelper::get_image_url_by_id($agent->bio_image_id, 'large', $default_bio_image);
  }

  public static function get_top_agents($date_from, $date_to, $limit = false, $location_id = false){
    $agents = new OsAgentModel();
    $bookings = new OsBookingModel();
    $query_params = array($date_from, $date_to);
    if($location_id){
      $location_query = ' AND location_id = %d ';
      $query_params[] = $location_id;
    }else{
      $location_query = '';
    }
    $query = 'select count('.$bookings->table_name.'.id) as total_appointments, SUM(end_time - start_time) as total_minutes, agent_id from '.$bookings->table_name.'  
              join '.$agents->table_name.' on agent_id = '.$agents->table_name.'.id 
              where start_date >= %s and start_date <= %s '.$location_query.'
              group by agent_id order by total_appointments desc';
    if($limit) {
    	$query.= ' LIMIT %d';
	    $query_params[] = $limit;
	  }
    $top_agents = $agents->get_query_results($query, $query_params);
    return $top_agents;
  }

  public static function count_agents_on_duty($date, $location_id = false){
    $agents = new OsAgentModel();
    return $agents->count();
  }

  public static function count_agents(){
    $agents = new OsAgentModel();
    return $agents->count();
  }

  public static function count_openings_for_date($agent, $service, $location, $target_date){
    if(!isset($target_date) || !isset($agent) || !isset($service) || !isset($location)) return 0;
    $work_start_end_time = OsBookingHelper::get_work_start_end_time_for_date(['custom_date' => $target_date, 'service_id' => $service->id, 'agent_id' => $agent->id, 'location_id' => $location->id]);
    $work_start_minutes = $work_start_end_time[0];
    $work_end_minutes = $work_start_end_time[1];
    $total_work_minutes = $work_end_minutes - $work_start_minutes;
    $timeblock_interval = OsSettingsHelper::get_timeblock_interval();

    $work_periods_arr = OsBookingHelper::get_work_periods(['custom_date' => $target_date, 'service_id' => $service->id, 'agent_id' => $agent->id, 'location_id' => $location->id]);
    $booked_periods_arr = OsBookingHelper::get_bookings_times_for_date($target_date, $agent->id);

    $openings = 0;
    for($current_minutes = $work_start_minutes; $current_minutes <= $work_end_minutes; $current_minutes+=$timeblock_interval){
      $is_available = true;
      if(OsBookingHelper::is_timeframe_in_periods($current_minutes, $current_minutes + $service->duration, $booked_periods_arr)){
        $is_available = false;
      }
      if(!OsBookingHelper::is_timeframe_in_periods($current_minutes, $current_minutes + $service->duration, $work_periods_arr, true)){
        $is_available = false;
      }
      if($is_available) $openings++;
    }
    return $openings;
  }

  public static function availability_timeline_off($off_label = false, $show_avatar = false, $agent = false){
    $off_label = $off_label ? $off_label : __('Not Available', 'latepoint');
    ?>
      <div class="agent-day-availability-w">
        <?php if($show_avatar && $agent){ ?><a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'edit_form'), array('id' => $agent->id) ) ?>" class="agent-avatar-w with-hover-name" style="background-image: url(<?php echo $agent->get_avatar_url(); ?>);"><span><?php echo $agent->full_name; ?></span></a><?php } ?>
        <div class="agent-timeslots">
          <div class="agent-timeslot full-day-off"><span class="agent-timeslot-label"><?php echo $off_label; ?></span></div>
        </div>
      </div>
    <?php
  }


  public static function availability_timeline($agent, $service, $location, $target_date, $settings = array()){
    if(isset($agent) && isset($service)){
      $default_settings = array(
        'show_avatar' => true, 
        'book_on_click' => true, 
        'show_ticks' => true, 
        'preset_work_start_end_time' => false);
      $settings = array_merge($default_settings, $settings);

      // check if connection exxists between location, agent and service
      $is_connected = OsConnectorHelper::has_connection(['agent_id' => $agent->id, 'service_id' => $service->id, 'location_id' => $location->id]);
      if(!$is_connected){
        self::availability_timeline_off(__('Not Available', 'latepoint'), $settings['show_avatar'], $agent);
        return;
      }


      if($settings['preset_work_start_end_time']){
        $work_start_minutes = $settings['preset_work_start_end_time'][0];
        $work_end_minutes = $settings['preset_work_start_end_time'][1];
      }else{
        $work_start_end_time = OsBookingHelper::get_work_start_end_time_for_date(['custom_date' => $target_date, 'service_id' => $service->id, 'agent_id' => $agent->id, 'location_id' => $location->id, 'flexible_search' => true]);
        $work_start_minutes = $work_start_end_time[0];
        $work_end_minutes = $work_start_end_time[1];
      }
      $total_work_minutes = $work_end_minutes - $work_start_minutes;
      $timeblock_interval = OsSettingsHelper::get_timeblock_interval();

      $work_periods_arr = OsBookingHelper::get_work_periods(['custom_date' => $target_date, 'service_id' => $service->id, 'agent_id' => $agent->id, 'location_id' => $location->id, 'flexible_search' => true]);
      $booked_periods_arr = OsBookingHelper::get_bookings_times_for_date($target_date, $agent->id);
      ?>
      <div class="agent-day-availability-w">
        <?php if($settings['show_avatar']){ ?>
          <a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'edit_form'), array('id' => $agent->id) ) ?>" class="agent-avatar-w with-hover-name" style="background-image: url(<?php echo $agent->get_avatar_url(); ?>);"><span><?php echo $agent->full_name; ?></span></a>
        <?php } ?>
        <div class="agent-timeslots">
          <?php 
          if($work_start_minutes == $work_end_minutes){
            echo '<div class="agent-timeslot full-day-off"><span class="agent-timeslot-label">'.__('Day Off', 'latepoint').'</span></div>';
          }else{
            for($current_minutes = $work_start_minutes; $current_minutes <= $work_end_minutes; $current_minutes+=$timeblock_interval){
              $ampm = OsTimeHelper::am_or_pm($current_minutes);

              $timeslot_class = 'agent-timeslot';
              $is_available = true;
              if(OsBookingHelper::is_timeframe_in_periods($current_minutes, $current_minutes + $service->duration, $booked_periods_arr)){
                $timeslot_class.= ' is-booked';
                $is_available = false;
              }
              if(!OsBookingHelper::is_timeframe_in_periods($current_minutes, $current_minutes + $service->duration, $work_periods_arr, true)){
                $timeslot_class.= ' is-off';
                $is_available = false;
              }
              $tick_html = '';
              if(($current_minutes % 60) == 0){
                $timeslot_class.= ' with-tick';
                $tick_html = '<span class="agent-timeslot-tick"><strong>'. OsTimeHelper::minutes_to_hours($current_minutes) .'</strong>'.' '.$ampm.'</span>';
              }
              $datas_attr = '';
              if($is_available){
                $timeslot_class.= ' is-available';
                if($settings['book_on_click']){
                  $datas_attr = OsBookingHelper::quick_booking_btn_html(false, array('start_time'=> $current_minutes, 'agent_id' => $agent->id, 'service_id' => $service->id, 'location_id' => $location->id, 'start_date' => $target_date));
                }else{
                  $datas_attr = 'data-date="'.$target_date.'" data-minutes="'.$current_minutes.'"';
                  $timeslot_class.= ' fill-booking-time';
                }
              }
              echo '<div '.$datas_attr.' class="'.$timeslot_class.'" data-minutes="' . $current_minutes . '"><span class="agent-timeslot-label">'.OsTimeHelper::minutes_to_hours_and_minutes($current_minutes).'</span>'.$tick_html.'</div>';
            }
          }
        ?>
        </div>
      </div><?php
    }else{ ?>
      <div class="no-results-w">
        <div class="icon-w"><i class="latepoint-icon latepoint-icon-users"></i></div>
        <?php if(!isset($agents)){ ?>
          <h2><?php _e('No Existing Agents Found', 'latepoint'); ?></h2>
          <a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'new_form') ) ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php _e('Add First Agent', 'latepoint'); ?></span></a>
        <?php }else{ ?>
          <div class="no-results-w">
            <div class="icon-w"><i class="latepoint-icon latepoint-icon-book"></i></div>
            <h2><?php _e('No Services Found', 'latepoint'); ?></h2>
            <a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('services', 'new_form') ) ?>" class="latepoint-btn">
              <i class="latepoint-icon latepoint-icon-plus"></i>
              <span><?php _e('Add Service', 'latepoint'); ?></span>
            </a>
          </div>
        <?php } ?>
      </div>
    <?php 
    }
  }
}