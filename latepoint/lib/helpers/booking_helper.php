<?php 

class OsBookingHelper {

  public static function get_payment_total_info_html($booking){
    $html = '<div class="payment-total-info"><div class="payment-total-price-w"><span>'.__('Total booking price: ', 'latepoint').'</span><span class="lp-price-value">'.$booking->formatted_full_price().'</span></div></div>';
    $html = apply_filters('latepoint_filter_payment_total_info', $html, $booking);
    return $html;
  }

  public static function process_actions_after_save($booking_id){
  }

  public static function get_quick_availability_days($start_date, $agent, $service, $location, $work_start_end = false, $number_of_days = 30){
    $html = '';
    $date_obj = new OsWpDateTime($start_date);

    // check if connection exxists between location, agent and service
    $is_connected = OsConnectorHelper::has_connection(['agent_id' => $agent->id, 'service_id' => $service->id, 'location_id' => $location->id]);

    for($i = 0; $i < $number_of_days; $i++){
      if($date_obj->format('j') == '1'){
        $html.= '<div class="ma-month-label">'.OsUtilHelper::get_month_name_by_number($date_obj->format('n')).'</div>';
      }
      $html.= '<div class="ma-day ma-day-number-'.$date_obj->format('N').'">';
        $html.= '<div class="ma-day-info">';
          $html.= '<span class="ma-day-number">'.$date_obj->format('j').'</span>';
          $html.= '<span class="ma-day-weekday">'.OsUtilHelper::get_weekday_name_by_number($date_obj->format('N'), true).'</span>';
        $html.= '</div>';
        ob_start();
        if($is_connected){
          OsAgentHelper::availability_timeline($agent, $service, $location, $date_obj->format('Y-m-d'), array('show_avatar' => false, 'book_on_click' => false, 'preset_work_start_end_time' => $work_start_end));
        }else{
          OsAgentHelper::availability_timeline_off(__('Not Available', 'latepoint'));
        }
        $html.= ob_get_clean();
      $html.= '</div>';
      $date_obj->modify('+1 day');
    }
    return $html;
  }

  public static function count_pending_bookings($agent_id = false, $location_id = false){
    $bookings = new OsBookingModel();
    if($agent_id){
      $bookings->where(['agent_id' => $agent_id]);
    }
    if($location_id){
      $bookings->where(['location_id' => $location_id]);
    }
    return $bookings->where(['status IN' => [LATEPOINT_BOOKING_STATUS_PENDING, LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING]])->count();
  }

  public static function generate_services_list($services = false){
    if($services && is_array($services) && !empty($services)){ ?>
      <ul class="os-services">
        <?php foreach($services as $service){ ?>
          <li class="<?php if($service->short_description) echo 'with-description'; ?>">
            <a href="#" data-service-id="<?php echo $service->id; ?>">
              <?php if($service->selection_image_id){ ?>
                <span class="service-img-w" style="background-image: url(<?php echo $service->selection_image_url; ?>);"></span>
              <?php } ?>
              <span class="service-name-w">
                <span class="service-name"><?php echo $service->name; ?></span>
                <?php if($service->short_description){ ?>
                  <span class="service-desc"><?php echo $service->short_description; ?></span>
                <?php } ?>
              </span>
              <?php if($service->price_min > 0){ ?>
                <span class="service-price-w">
                  <span class="service-price">
                    <?php echo $service->price_min_formatted; ?>
                  </span>
                  <?php if($service->price_min != $service->price_max){ ?>
                    <span class="service-price-label"><?php _e('Starts From', 'latepoint'); ?></span>
                  <?php } ?>
                </span>
              <?php } ?>
            </a>
          </li>
        <?php } ?>
      </ul>
    <?php } 
  }

  public static function generate_services_and_categories_list($parent_id = false, $show_selected_categories = false, $show_selected_services = false, $preselected_category = false){
    $service_categories = new OsServiceCategoryModel();
    $args = array();
    if($show_selected_categories && is_array($show_selected_categories)){
      if($parent_id){
        $service_categories->where(['parent_id' => $parent_id]);
      }else{
        if($preselected_category){
          $service_categories->where(['id' => $preselected_category]);
        }else{
          $service_categories->where_in('id', $show_selected_categories);
          $service_categories->where(['parent_id' => ['OR' => ['IS NULL', ' NOT IN' => $show_selected_categories] ]]);
        }
      }
    }else{
      if($preselected_category){
        $service_categories->where(['id' => $preselected_category]);
      }else{
        $args['parent_id'] = $parent_id ? $parent_id : 'IS NULL';
      }
    }
    $service_categories = $service_categories->where($args)->order_by('order_number asc')->get_results_as_models();
    if(!is_array($service_categories)) return;
    $main_parent_class = ($parent_id) ? '': 'os-service-categories-main-parent';
    if(!$preselected_category) echo '<div class="os-service-categories-holder '.$main_parent_class.'">';
    foreach($service_categories as $service_category){ ?>
      <?php 
      $services = [];
      $category_services = $service_category->active_services;
      if(is_array($category_services)){
        // if show selected services restriction is set - filter
        if($show_selected_services){
          foreach($category_services as $category_service){
            if(in_array($category_service->id, $show_selected_services)) $services[] = $category_service;
          }
        }else{
          $services = $category_services;
        }  
      }
      $child_categories = new OsServiceCategoryModel();
      $count_child_categories = $child_categories->where(['parent_id' => $service_category->id])->count();
      // show only if it has either at least one child category or service
      if($count_child_categories || count($services)){ 
        // preselected category, just show contents, not the wrapper
        if($service_category->id == $preselected_category){
          OsBookingHelper::generate_services_list($services);
          OsBookingHelper::generate_services_and_categories_list($service_category->id, $show_selected_categories, $show_selected_services);
        }else{ ?>
          <div class="os-service-category-w" data-id="<?php echo $service_category->id; ?>">
            <div class="os-service-category-info-w">
              <a href="#" class="os-service-category-info">
                <span class="os-service-category-img-w" style="background-image: url(<?php echo $service_category->selection_image_url; ?>);"></span>
                <span class="os-service-category-name"><?php echo $service_category->name; ?></span>
                <?php if(count($services)){ ?>
                  <span class="os-service-category-services-count"><span><?php echo count($services); ?></span> <?php _e('Services', 'latepoint'); ?></span>
                <?php } ?>
              </a>
            </div>
            <?php OsBookingHelper::generate_services_list($services); ?>
            <?php OsBookingHelper::generate_services_and_categories_list($service_category->id, $show_selected_categories, $show_selected_services); ?>
          </div><?php
        }
      }
    }
    if(!$preselected_category) echo '</div>';
  }

  public static function quick_booking_btn_html($booking_id = false, $params = array()){
    $html = '';
    if($booking_id){
      $params['id'] = $booking_id;
      $route = OsRouterHelper::build_route_name('bookings', 'quick_edit_form');
    }else{
      $route = OsRouterHelper::build_route_name('bookings', 'quick_new_form');
    }
    $params_str = http_build_query($params);
    $html = 'data-os-params="'.$params_str.'" 
    data-os-action="'.$route.'" 
    data-os-output-target="side-panel"
    data-os-after-call="latepoint_init_quick_booking_form"';
    return $html;
  }

  public static function get_services_count_by_type_for_date($date, $agent_id = false){
    $bookings = new OsBookingModel();
    $where_args = array('start_date' => $date);
    if($agent_id) $where_args['agent_id'] = $agent_id;
    return $bookings->select(LATEPOINT_TABLE_SERVICES.".name, count(".LATEPOINT_TABLE_BOOKINGS.".id) as count, bg_color")->join(LATEPOINT_TABLE_SERVICES, array(LATEPOINT_TABLE_SERVICES.".id" => 'service_id'))->where($where_args)->group_by('service_id')->get_results(ARRAY_A);
  }

  public static function get_any_agent_for_booking_by_rule($booking){
    // ANY AGENT SELECTED
    // get available agents 
    $connected_ids = OsConnectorHelper::get_connected_object_ids('agent_id', ['service_id' => $booking->service_id, 'location_id' => $booking->location_id]);

    // If date/time is selected - filter agents who are available at that time
    if($booking->start_date && $booking->start_time){
      $available_agent_ids = [];
      foreach($connected_ids as $agent_id){
        if(OsAgentHelper::is_agent_available_on($agent_id, $booking->start_date, $booking->start_time, $booking->duration, $booking->service_id, $booking->location_id)){
          $available_agent_ids[] = $agent_id;
        }
      }
      $connected_ids = (!empty($available_agent_ids) && !empty($connected_ids)) ? array_intersect($available_agent_ids, $connected_ids) : $connected_ids;
    }


    $agents_model = new OsAgentModel();
    if(!empty($connected_ids)) $agents_model->where_in('id', $connected_ids);
    $agents = $agents_model->should_be_active()->get_results_as_models();

    if(empty($agents)){
      return false;
    }


    $selected_agent_id = false;
    switch(OsSettingsHelper::get_any_agent_order()){
      case LATEPOINT_ANY_AGENT_ORDER_RANDOM:
        $selected_agent_id = $connected_ids[rand(0, count($connected_ids) - 1)];
      break;
      case LATEPOINT_ANY_AGENT_ORDER_PRICE_HIGH:
        $highest_price = false;
        foreach($agents as $agent){
          $booking->agent_id = $agent->id;
          $price = OsMoneyHelper::calculate_full_amount_to_charge($booking);
          if($highest_price === false && $selected_agent_id === false){
            $highest_price = $price;
            $selected_agent_id = $agent->id;
          }else{
            if($highest_price < $price){
              $highest_price = $price;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
      case LATEPOINT_ANY_AGENT_ORDER_PRICE_LOW:
        $lowest_price = false;
        foreach($agents as $agent){
          $booking->agent_id = $agent->id;
          $price = OsMoneyHelper::calculate_full_amount_to_charge($booking);
          if($lowest_price === false && $selected_agent_id === false){
            $lowest_price = $price;
            $selected_agent_id = $agent->id;
          }else{
            if($lowest_price > $price){
              $lowest_price = $price;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
      case LATEPOINT_ANY_AGENT_ORDER_BUSY_HIGH:
        $max_bookings = false;
        foreach($agents as $agent){
          $agent_total_bookings = OsBookingHelper::total_bookings_for_date($booking->start_date, ['agent_id' => $agent->id]);
          if($max_bookings === false && $selected_agent_id === false){
            $max_bookings = $agent_total_bookings;
            $selected_agent_id = $agent->id;
          }else{
            if($max_bookings < $agent_total_bookings){
              $max_bookings = $agent_total_bookings;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
      case LATEPOINT_ANY_AGENT_ORDER_BUSY_LOW:
        $min_bookings = false;
        foreach($agents as $agent){
          $agent_total_bookings = OsBookingHelper::total_bookings_for_date($booking->start_date, ['agent_id' => $agent->id]);
          if($min_bookings === false && $selected_agent_id === false){
            $min_bookings = $agent_total_bookings;
            $selected_agent_id = $agent->id;
          }else{
            if($min_bookings > $agent_total_bookings){
              $min_bookings = $agent_total_bookings;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
    }
    $booking->agent_id = $selected_agent_id;
    return $selected_agent_id;
  }

  public static function total_bookings_for_date($date, $conditions = []){
    $args = ['start_date' => $date];
    if(isset($conditions['agent_id']) && $conditions['agent_id']) $args['agent_id'] = $conditions['agent_id'];
    if(isset($conditions['service_id']) && $conditions['service_id']) $args['service_id'] = $conditions['service_id'];
    if(isset($conditions['location_id']) && $conditions['location_id']) $args['location_id'] = $conditions['location_id'];

    $bookings = new OsBookingModel();
    $bookings = $bookings->where($args);
    return $bookings->count();
  }

  public static function get_default_booking_status(){
    $default_status = OsSettingsHelper::get_settings_value('default_booking_status');
    if($default_status){
      return $default_status;
    }else{
      return LATEPOINT_BOOKING_STATUS_APPROVED;
    }
  }



  // Returns step names in order
  public static function get_step_names_in_order($show_all_steps = false){
    $default_steps = array( 'locations', 'services', 'agents', 'datepicker', 'contact', 'payment', 'verify', 'confirmation');

    $steps_model = new OsStepModel();

    $query = "SELECT * FROM ".$steps_model->table_name." WHERE label = 'order_number' ORDER BY value ASC";
    $items = $steps_model->get_query_results( $query, array(), ARRAY_A );
    if($items && (count($items) == count($default_steps))){
      $steps = array_map(function($item){ return $item['step']; }, $items);
      $steps = array_values(array_intersect($steps, $default_steps));
      if(empty($steps)) $steps = $default_steps;
    }else{
      $steps = $default_steps;
    }
    if(!$show_all_steps){
      // If we only want to show steps that have been setup correctly
      if(!OsSettingsHelper::is_accepting_payments()){
        // Check if payment processing is setup, if not - remove step payments
        $payment_step_index_key = array_search('payment', $steps);
        if (false !== $payment_step_index_key) {
          unset($steps[$payment_step_index_key]);
          $steps = array_values($steps);
        }
      }
      if(OsLocationHelper::count_locations() <= 1){
        // Check if only one location exist - remove step locations
        $locations_step_index_key = array_search('locations', $steps);
        if (false !== $locations_step_index_key) {
          unset($steps[$locations_step_index_key]);
          $steps = array_values($steps);
        }
      }
    }
    return $steps;
  }


  public static function is_timeframe_in_periods($timeframe_start, $timeframe_end, $periods_arr, $should_be_fully_inside = false){
    for($i=0;$i<count($periods_arr);$i++){
      $period_info = explode(':', $periods_arr[$i]);
      if(count($period_info) == 2){
        list($period_start, $period_end) = $period_info;
      }
      if(count($period_info) == 4){
        list($period_start, $period_end, $buffer_before, $buffer_after) = $period_info;
        $period_start = $period_start - $buffer_before;
        $period_end = $period_end + $buffer_after;
      }
      if($should_be_fully_inside){
        if(self::is_period_inside_another($timeframe_start, $timeframe_end, $period_start, $period_end)){
          return true;
        }
      }else{
        if(self::is_period_overlapping($timeframe_start, $timeframe_end, $period_start, $period_end)){
          return true;
        }
      }
    }
    return false;
  }

  public static function is_period_overlapping($period_one_start, $period_one_end, $period_two_start, $period_two_end){
    // https://stackoverflow.com/questions/325933/determine-whether-two-date-ranges-overlap/
    return (($period_one_start < $period_two_end) && ($period_two_start < $period_one_end));
  }

  public static function is_period_inside_another($period_one_start, $period_one_end, $period_two_start, $period_two_end){
    return (($period_one_start >= $period_two_start) && ($period_one_end <= $period_two_end));
  }



  public static function get_bookings_times_for_date($date, $agent_id, $location_id = false, $approved_only = true){
    if(!$location_id) $location_id = OsLocationHelper::get_selected_location_id();
    $bookings = new OsBookingModel();

    $query = "SELECT start_time, end_time, buffer_before, buffer_after FROM ". $bookings->table_name ." WHERE start_date = %s AND agent_id = %d AND location_id = %d";
    $args = array( $date, $agent_id, $location_id );

    if($approved_only){
      $query.= ' AND status = %s';
      $args[] = LATEPOINT_BOOKING_STATUS_APPROVED;
    }

    $booked_periods = $bookings->get_query_results($query, $args);

    $booked_periods_arr = array();

    foreach($booked_periods as $booked_period){
      $start_time = $booked_period->start_time;
      $end_time = $booked_period->end_time;
      $booked_periods_arr[] = $start_time. ':' .$end_time. ':' .$booked_period->buffer_before. ':' .$booked_period->buffer_after;
    }

    $booked_periods_arr = apply_filters('latepoint_filter_booked_periods', $booked_periods_arr, $date, $agent_id);

    return $booked_periods_arr;
  }


  // args = [agent_id, 'service_id', 'location_id']
  public static function get_bookings_for_date($date, $args = []){
    $bookings = new OsBookingModel();
    $args['start_date'] = $date;
    $bookings->should_not_be_cancelled()->where($args);
    return $bookings->get_results_as_models();
  }


  public static function generate_monthly_calendar($target_date_string = 'today', $settings = []){
    $defaults = [
    'service_id' => false,
    'agent_id' => false, 
    'location_id' => false, 
    'number_of_months_to_preload' => 1, 
    'allow_full_access' => false, 
    'highlight_target_date' => false ];

    $settings = OsUtilHelper::merge_default_atts($defaults, $settings);

    if($settings['location_id'] === false) $settings['location_id'] = OsLocationHelper::get_selected_location_id();
    $target_date = new OsWpDateTime($target_date_string);
    $weekdays = OsBookingHelper::get_weekdays_arr();

    ?>
    <div class="os-current-month-label-w">
      <div class="os-current-month-label"><?php echo OsUtilHelper::get_month_name_by_number($target_date->format('n')); ?></div>
      <button type="button" class="os-month-prev-btn <?php if(!$settings['allow_full_access']) echo 'disabled'; ?>" data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'load_monthly_calendar_days') ?>"><i class="latepoint-icon latepoint-icon-arrow-left"></i></button>
      <button type="button" class="os-month-next-btn" data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'load_monthly_calendar_days') ?>"><i class="latepoint-icon latepoint-icon-arrow-right"></i></button>
    </div>
    <div class="os-weekdays">
    <?php foreach($weekdays as $weekday_number => $weekday_name){
      echo '<div class="weekday weekday-'.($weekday_number + 1).'">'.$weekday_name.'</div>';
    } ?>
    </div>
    <div class="os-months">
      <?php 
      $days_settings = ['service_id' => $settings['service_id'], 
                        'agent_id' => $settings['agent_id'], 
                        'location_id' => $settings['location_id'], 
                        'active' => true, 
                        'highlight_target_date' => $settings['highlight_target_date']];

      
      // if it's not from admin - blackout dates that are not available to select due to date restrictions in settings
      if(!$settings['allow_full_access']){
        $days_settings['earliest_possible_booking'] = OsSettingsHelper::get_settings_value('earliest_possible_booking', false);
        $days_settings['latest_possible_booking'] = OsSettingsHelper::get_settings_value('latest_possible_booking', false);
      }

      OsBookingHelper::generate_monthly_calendar_days($target_date_string, $days_settings); 
      for($i = 1; $i <= $settings['number_of_months_to_preload']; $i++){
        $target_date->modify('first day of next month');
        $days_settings['active'] = false;
        $days_settings['highlight_target_date'] = false;
        OsBookingHelper::generate_monthly_calendar_days($target_date->format('Y-m-d'), $days_settings);
      }
      ?>
    </div><?php
  }

  public static function generate_monthly_calendar_days($target_date_string = 'today', $settings = []){
    $defaults = [
    'service_id' => false, 
    'agent_id' => false, 
    'location_id' => false, 
    'active' => false, 
    'highlight_target_date' => false, 
    'earliest_possible_booking' => false,
    'latest_possible_booking' => false ];
    $settings = OsUtilHelper::merge_default_atts($defaults, $settings);

    $service = new OsServiceModel($settings['service_id']);

    if($settings['location_id'] === false) $settings['location_id'] = OsLocationHelper::get_selected_location_id();
    if(($settings['agent_id'] == LATEPOINT_ANY_AGENT)){
      $agent_ids = OsAgentHelper::get_agents_for_service_and_location($service->id, $settings['location_id']);
    }else{
      $agent_ids = [$settings['agent_id']];
    }

    $target_date = new OsWpDateTime($target_date_string);
    $calendar_start = clone $target_date;
    $calendar_start->modify('first day of this month');
    $calendar_end = clone $target_date;
    $calendar_end->modify('last day of this month');

    $interval = OsSettingsHelper::get_timeblock_interval();


    $weekday_for_first_day_of_month = $calendar_start->format('N') - 1;
    $weekday_for_last_day_of_month = $calendar_end->format('N') - 1;


    if($weekday_for_first_day_of_month > 0){
      $calendar_start->modify('-'.$weekday_for_first_day_of_month.' days');
    }

    if($weekday_for_last_day_of_month < 7){
      $days_to_add = 7 - $weekday_for_last_day_of_month;
      $calendar_end->modify('+'.$days_to_add.' days');
    }

    $active_class = $settings['active'] ? 'active' : '';
    echo '<div class="os-monthly-calendar-days-w '.$active_class.'" data-calendar-year="' . $target_date->format('Y') . '" data-calendar-month="' . $target_date->format('n') . '" data-calendar-month-label="' . OsUtilHelper::get_month_name_by_number($target_date->format('n')) . '"><div class="os-monthly-calendar-days">';

      // DAYS LOOP START
      for($day_date=clone $calendar_start; $day_date<$calendar_end; $day_date->modify('+1 day')){
        $is_today = ($day_date->format('Y-m-d') == OsTimeHelper::today_date()) ? true : false;
        $is_day_in_past = ($day_date->format('Y-m-d') < OsTimeHelper::today_date()) ? true : false;
        $is_target_month = ($day_date->format('m') == $target_date->format('m')) ? true : false;
        $is_next_month = ($day_date->format('m') > $target_date->format('m')) ? true : false;
        $is_prev_month = ($day_date->format('m') < $target_date->format('m')) ? true : false;
        $not_in_allowed_period = false;

        if($settings['earliest_possible_booking']){
          $earliest_possible_booking = new OsWpDateTime($settings['earliest_possible_booking']);
          if($day_date->format('Y-m-d') < $earliest_possible_booking->format('Y-m-d')) $not_in_allowed_period = true;
        }
        if($settings['latest_possible_booking']){
          $latest_possible_booking = new OsWpDateTime($settings['latest_possible_booking']);
          if($day_date->format('Y-m-d') > $latest_possible_booking->format('Y-m-d')) $not_in_allowed_period = true;
        }

        $work_periods_arr = [];
        $booked_periods_arr = [];

        $booked_minutes = [];
        $not_working_minutes = [];
        $available_minutes = [];
        $day_minutes = [];

        if(!$is_day_in_past && !$not_in_allowed_period){
          if($agent_ids){
            foreach($agent_ids as $agent_id){
              $work_periods_arr = self::get_work_periods(['custom_date' => $day_date->format('Y-m-d'), 'service_id' => $settings['service_id'], 'agent_id' => $agent_id, 'location_id' => $settings['location_id']]);
              $booked_periods_arr = self::get_bookings_times_for_date($day_date->format('Y-m-d'), $agent_id, $settings['location_id']);
              foreach($work_periods_arr as $work_period){
                list($period_start, $period_end) = explode(':', $work_period);
                if($period_start == $period_end) continue;

                for($minutes = $period_start; $minutes <= $period_end; $minutes+= $service->get_timeblock_interval()){
                  $day_minutes[] = $minutes;
                  $is_available = true;
                  if(OsBookingHelper::is_timeframe_in_periods($minutes, $minutes + $service->duration, $booked_periods_arr)){
                    $booked_minutes[] = $minutes;
                    $is_available = false;
                  }
                  if(!OsBookingHelper::is_timeframe_in_periods($minutes, $minutes + $service->duration, $work_periods_arr, true)){
                    $not_working_minutes[] = $minutes;
                    $is_available = false;
                  }
                  if($is_available) $available_minutes[] = $minutes;
                }
              }
            }
          }
        }

        $available_minutes = array_unique($available_minutes, SORT_NUMERIC);
        $booked_minutes = array_unique($booked_minutes, SORT_NUMERIC);
        $not_working_minutes = array_unique($not_working_minutes, SORT_NUMERIC);
        $day_minutes = array_unique($day_minutes, SORT_NUMERIC);

        if($is_today){
          // if today - block already passed time slots
          $booked_periods_arr[] = '0:'.OsTimeHelper::get_current_minutes().':0:0';
        }

        $work_periods_str = implode(',', $work_periods_arr);
        $booked_periods_str = implode(',', $booked_periods_arr);

        if(empty($day_minutes)){
          $work_start_minutes = 0;
          $work_end_minutes = 0;
        }else{
          $work_start_minutes = min($day_minutes);
          $work_end_minutes = max($day_minutes);
        }
        $total_work_minutes = $work_end_minutes - $work_start_minutes;


        $day_class = 'os-day os-day-current week-day-'.strtolower($day_date->format('N')); 
        if($is_today) $day_class.= ' os-today';
        if($is_day_in_past) $day_class.= ' os-day-passed';
        if($is_target_month) $day_class.= ' os-month-current';
        if($is_next_month) $day_class.= ' os-month-next';
        if($is_prev_month) $day_class.= ' os-month-prev';
        if($not_in_allowed_period) $day_class.= ' os-not-in-allowed-period';
        if(($day_date->format('Y-m-d') == $target_date->format('Y-m-d')) && $settings['highlight_target_date']) $day_class.= ' selected';
        ?>

        <div class="<?php echo $day_class; ?>" 
          data-date="<?php echo $day_date->format('Y-m-d'); ?>" 
          data-nice-date="<?php echo OsUtilHelper::get_month_name_by_number($day_date->format('n')).' '.$day_date->format('d'); ?>"
          data-service-duration="<?php echo $service->duration; ?>" 
          data-total-work-minutes="<?php echo $total_work_minutes; ?>" 
          data-work-start-time="<?php echo $work_start_minutes; ?>" 
          data-work-end-time="<?php echo $work_end_minutes ?>" 
          data-available-minutes="<?php echo implode(',', $available_minutes); ?>" 
          data-interval="<?php echo $interval; ?>">
          <div class="os-day-box">
            <div class="os-day-number"><?php echo $day_date->format('j'); ?></div>
            <?php if(!$is_day_in_past && $work_periods_str && !$not_in_allowed_period){ ?>
              <div class="os-day-status">
                <?php 
                if($total_work_minutes > 0){
                  $interval_width = $interval / $total_work_minutes * 100;
                  $started = false;
                  foreach($day_minutes as $minute){
                    if(in_array($minute, $available_minutes)){
                      if(!$started) $started = $minute;
                    }else{
                      if($started){
                        $period_start_percent = (($started - $work_start_minutes) / $total_work_minutes) * 100;
                        $period_width_percent = (($minute - $started) / $total_work_minutes) * 100;
                        echo '<div class="day-available" style="left:'.$period_start_percent.'%;width:'.$period_width_percent.'%;"></div>';
                        $started = false;
                      }
                    }
                  }
                }
                ?>
              </div>
            <?php } ?>
          </div>
        </div>

        <?php

        // DAYS LOOP END
      }
    echo '</div></div>';
  }

  // Used on holiday/custom schedule generator lightbox
  public static function generate_monthly_calendar_days_only($target_date_string = 'today', $highlight_target_date = false){
    $target_date = new OsWpDateTime($target_date_string);
    $calendar_start = clone $target_date;
    $calendar_start->modify('first day of this month');
    $calendar_end = clone $target_date;
    $calendar_end->modify('last day of this month');

    $weekday_for_first_day_of_month = $calendar_start->format('N') - 1;
    $weekday_for_last_day_of_month = $calendar_end->format('N') - 1;


    if($weekday_for_first_day_of_month > 0){
      $calendar_start->modify('-'.$weekday_for_first_day_of_month.' days');
    }

    if($weekday_for_last_day_of_month < 7){
      $days_to_add = 7 - $weekday_for_last_day_of_month;
      $calendar_end->modify('+'.$days_to_add.' days');
    }

    echo '<div class="os-monthly-calendar-days-w" data-calendar-year="' . $target_date->format('Y') . '" data-calendar-month="' . $target_date->format('n') . '" data-calendar-month-label="' . OsUtilHelper::get_month_name_by_number($target_date->format('n')) . '">
            <div class="os-monthly-calendar-days">';
              for($day_date=clone $calendar_start; $day_date<$calendar_end; $day_date->modify('+1 day')){
                $is_today = ($day_date->format('Y-m-d') == OsTimeHelper::today_date()) ? true : false;
                $is_day_in_past = ($day_date->format('Y-m-d') < OsTimeHelper::today_date()) ? true : false;
                $day_class = 'os-day os-day-current week-day-'.strtolower($day_date->format('N'));

                if($day_date->format('m') > $target_date->format('m')) $day_class.= ' os-month-next';
                if($day_date->format('m') < $target_date->format('m')) $day_class.= ' os-month-prev';

                if($is_today) $day_class.= ' os-today';
                if($highlight_target_date && ($day_date->format('Y-m-d') == $target_date->format('Y-m-d'))) $day_class.= ' selected';
                if($is_day_in_past) $day_class.= ' os-day-passed'; ?>
                <div class="<?php echo $day_class; ?>" data-date="<?php echo $day_date->format('Y-m-d'); ?>">
                  <div class="os-day-box">
                    <div class="os-day-number"><?php echo $day_date->format('j'); ?></div>
                  </div>
                </div><?php
              }
    echo '</div></div>';
  }

  public static function get_nice_status_name($status){
    $statuses_list = OsBookingHelper::get_statuses_list();
    if($status && isset($statuses_list[$status])){
      return $statuses_list[$status];
    }else{
      return __('Undefined Status', 'latepoint');
    }
  }

  public static function get_statuses_list(){
    return array( LATEPOINT_BOOKING_STATUS_APPROVED => __('Aprobado', 'latepoint'), 
                  LATEPOINT_BOOKING_STATUS_PENDING => __('Pendiente de aprobacion', 'latepoint'), 
                  LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING => __('Pendiente de pago', 'latepoint'), 
                  LATEPOINT_BOOKING_STATUS_CANCELLED => __('Cancelado', 'latepoint'));
  }



  public static function get_weekdays_arr($full_name = false) {
    if($full_name){
      $weekdays = array(__('Lunes', 'latepoint'), 
                        __('Martes', 'latepoint'), 
                        __('Miercoles', 'latepoint'), 
                        __('Jueves', 'latepoint'), 
                        __('Viernes', 'latepoint'), 
                        __('Sabado', 'latepoint'), 
                        __('Domingo', 'latepoint'));
    }else{
      $weekdays = array(__('Lun', 'latepoint'), 
                        __('Mar', 'latepoint'), 
                        __('Mier', 'latepoint'), 
                        __('Jue', 'latepoint'), 
                        __('Vie', 'latepoint'), 
                        __('Sab', 'latepoint'), 
                        __('Dom', 'latepoint'));
    }
    return $weekdays;
  }

  public static function get_weekday_name_by_number($weekday_number, $full_name = false) {
    $weekdays = OsBookingHelper::get_weekdays_arr($full_name);
    if(!isset($weekday_number) || $weekday_number < 1 || $weekday_number > 7) return '';
    else return $weekdays[$weekday_number - 1];
  }





  public static function generate_work_and_booked_periods_html($work_periods_arr, $booked_periods_arr, $duration_minutes){
    list($work_start_minutes, $work_end_minutes) = OsBookingHelper::get_work_start_end_time($work_periods_arr);

    $periods_html = '';

    $timeblock_interval = OsSettingsHelper::get_timeblock_interval();

    for($current_minutes = $work_start_minutes; $current_minutes <= $work_end_minutes; $current_minutes+=$timeblock_interval){

      $is_available = true;
      if(OsBookingHelper::is_timeframe_in_periods($current_minutes, $current_minutes + $duration_minutes, $booked_periods_arr)){
        $is_available = false;
      }
      if(!OsBookingHelper::is_timeframe_in_periods($current_minutes, $current_minutes + $duration_minutes, $work_periods_arr, true)){
        $is_available = false;
      }

      if($is_available){
        $period_start = $current_minutes;
        $period_end = $current_minutes + $timeblock_interval;
        $period = $period_start.':'.$period_end;
        $periods_html.= OsBookingHelper::generate_period_block_html($period, $work_start_minutes, $work_end_minutes, 'day-available');
      }else{

      }
    }
    return $periods_html;
  }




  public static function get_bookings_per_day_for_period($date_from, $date_to, $service_id = false, $agent_id = false, $location_id = false){
    $bookings = new OsBookingModel();
    $query_args = array($date_from, $date_to);
    $query = 'SELECT count(id) as bookings_per_day, start_date FROM '.$bookings->table_name.' WHERE start_date >= %s AND start_date <= %s';
    if($service_id){
      $query.= ' AND service_id = %d';
      $query_args[] = $service_id;
    }
    if($agent_id){
      $query.= ' AND agent_id = %d';
      $query_args[] = $agent_id;
    }
    if($location_id){
      $query.= ' AND location_id = %d';
      $query_args[] = $location_id;
    }
    $query.= ' GROUP BY start_date';
    return $bookings->get_query_results($query, $query_args);
  }



  public static function get_work_periods($args = array()){
    $work_periods = OsWorkPeriodsHelper::load_work_periods($args);
    $work_periods_formatted_arr = array();
    if($work_periods){
      foreach($work_periods as $work_period){
        $work_periods_formatted_arr[] = $work_period->start_time. ':' .$work_period->end_time;
      }
    }
    return $work_periods_formatted_arr;
  }

  public static function get_min_max_work_periods($specific_weekdays = false, $service_id = false, $agent_id = false){
    $select_string = 'MIN(start_time) as start_time, MAX(end_time) as end_time';
    $work_periods = new OsWorkPeriodModel();
    $work_periods = $work_periods->select($select_string);
    $query_args = array('service_id' => 0, 'agent_id' => 0);
    if($service_id) $query_args['service_id'] = $service_id;
    if($agent_id) $query_args['agent_id'] = $agent_id;
    if($specific_weekdays && !empty($specific_weekdays)) $query_args['week_day'] = $specific_weekdays;
    $results = $work_periods->set_limit(1)->where($query_args)->get_results(ARRAY_A);
    if(($service_id || $agent_id) && empty($results['min_start_time'])){
      if($service_id && empty($results['min_start_time'])){
        $query_args['service_id'] = 0;
        $work_periods = new OsWorkPeriodModel();
        $work_periods = $work_periods->select($select_string);
        $results = $work_periods->set_limit(1)->where($query_args)->get_results(ARRAY_A);
      }
      if($agent_id && empty($results['min_start_time'])){
        $query_args['agent_id'] = 0;
        $work_periods = new OsWorkPeriodModel();
        $work_periods = $work_periods->select($select_string);
        $results = $work_periods->set_limit(1)->where($query_args)->get_results(ARRAY_A); 
      }
    }
    if($results){
      return array($results['start_time'], $results['end_time']);
    }else{
      return false;
    }
  }




  public static function get_work_start_end_time_for_multiple_dates($dates = false, $service_id = false, $agent_id = false){
    $specific_weekdays = array();
    if($dates){
      foreach($dates as $date){
        $target_date = new OsWpDateTime($date);
        $weekday = $target_date->format('N');
        if(!in_array($weekday, $specific_weekdays)) $specific_weekdays[] = $weekday;
      }
    }
    $work_minmax_start_end = self::get_min_max_work_periods($specific_weekdays, $service_id, $agent_id);
    return $work_minmax_start_end;
  }

  public static function get_work_start_end_time_for_date_multi_agent($agent_ids = array(), $args = array()){
    $work_start_times = [];
    $work_end_times = [];
    foreach($agent_ids as $agent_id){
      $args['agent_id'] = $agent_id;
      $work_times = self::get_work_start_end_time_for_date($args);
      if($work_times[0] == 0 && $work_times[1] == 0){
        // day off, do not count
      }else{
        $work_start_times[] = $work_times[0];
        $work_end_times[] = $work_times[1];
      }
    }
    if(empty($work_start_times)) $work_start_times = [0];
    if(empty($work_end_times)) $work_end_times = [0];
    return array(min($work_start_times), max($work_end_times));
  }

  public static function get_work_start_end_time_for_date($args = array()){
    $work_periods_arr = self::get_work_periods($args);
    return self::get_work_start_end_time($work_periods_arr);
  }

  public static function is_minute_in_work_periods($minute, $work_periods_arr){
    // print_r($work_periods_arr);
    if(empty($work_periods_arr)) return false;
    foreach($work_periods_arr as $work_period){
      list($period_start, $period_end) = explode(':', $work_period);
      if($period_start <= $minute && $period_end >= $minute){
        return true;
      }
    }
    return false;
  }

  public static function get_work_start_end_time($work_periods_arr){
    $work_start_minutes = 0;
    $work_end_minutes = 0;
    foreach($work_periods_arr as $work_period){
      list($period_start, $period_end) = explode(':', $work_period);
      if($period_start == $period_end) continue;
      $work_start_minutes = ($work_start_minutes > 0) ? min($period_start, $work_start_minutes) : $period_start;
      $work_end_minutes = ($work_end_minutes > 0) ? max($period_end, $work_end_minutes) : $period_end;
    }
    return array($work_start_minutes, $work_end_minutes);
  }






  public static function generate_period_block_html($period, $work_start_minutes, $work_end_minutes, $css_class = 'day-booking'){
    $total_work_minutes = $work_end_minutes - $work_start_minutes;
    list($period_start, $period_end) = explode(':', $period, 2);
    if(($period_start < $work_start_minutes) || ($total_work_minutes <= 0)) return '';

    $period_start_percent = (($period_start - $work_start_minutes) / $total_work_minutes) * 100;
    $period_width_percent = (($period_end - $period_start) / $total_work_minutes) * 100;
    return '<div class="'.$css_class.'" style="left:'.$period_start_percent.'%;width:'.$period_width_percent.'%;"></div>';
  }




}
