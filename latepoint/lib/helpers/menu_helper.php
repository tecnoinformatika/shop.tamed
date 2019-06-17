<?php 

class OsMenuHelper {

  public static function get_side_menu_items() {
    $count_pending_bookings = OsBookingHelper::count_pending_bookings(OsAuthHelper::get_logged_in_agent_id(), OsLocationHelper::get_selected_location_id());
    if($count_pending_bookings){
      $pending_apps_count_html = '<span class="os-menu-badge">'.$count_pending_bookings.'</span>';
    }else{
      $pending_apps_count_html = '';
    }
    if(OsAuthHelper::get_logged_in_agent_id()){
      // ---------------
      // AGENT MENU
      // ---------------
      $menus = array(
        array( 'id' => 'dashboard',  'label' => __( 'My Dashboard', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-grid', 'link' => OsRouterHelper::build_link(['dashboard', 'for_agent'])),
        array( 'id' => 'calendar',  'label' => __( 'My Calendar', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-calendar', 'link' => OsRouterHelper::build_link(['bookings', 'daily_agent']),
          'children' => array(
                          array('label' => __( 'Daily View', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'daily_agent'])),
                          array('label' => __( 'Weekly View', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'weekly_agent'])),
                          array('label' => __( 'Monthly View', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'monthly_agents'])),
          )
        ),
        array( 'id' => 'appointments',  'label' => __( 'Appointments', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-book', 'link' => OsRouterHelper::build_link(['bookings', 'pending_approval']),
          'children' => array(
                          array('label' => __( 'All Appointments', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'index'])),
                          array('label' => __( 'Pending Approval', 'latepoint' ).$pending_apps_count_html, 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'pending_approval'])),
          )
        ),
        array( 'id' => 'customers',  'label' => __( 'My Customers', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-users', 'link' => OsRouterHelper::build_link(['customers', 'index']),
          'children' => array(
                          array('label' => __('Add Customer', 'latepoint'), 'icon' => '', 'link' => OsRouterHelper::build_link(['customers', 'new_form'])),
                          array('label' => __('List of Customers', 'latepoint'), 'icon' => '', 'link' => OsRouterHelper::build_link(['customers', 'index'])),
                        )
        ),
        array( 'id' => 'settings',  'label' => __( 'My Settings', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-settings', 'link' => OsRouterHelper::build_link(['agents', 'edit_form'], array('id' => OsAuthHelper::get_logged_in_agent_id()) ))
      );
    }elseif(current_user_can('manage_options')){
      // ---------------
      // ADMINISTRATOR MENU
      // ---------------
      $menus = array(
        array( 'id' => 'dashboard', 'label' => __( 'Dashboard', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-grid', 'link' => OsRouterHelper::build_link(['dashboard', 'index'])),
        array( 'id' => 'calendar', 'label' => __( 'Calendar', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-calendar', 'link' => OsRouterHelper::build_link(['bookings', 'daily_agent']),
          'children' => array(
                          array('label' => __( 'Daily View', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'daily_agent'])),
                          array('label' => __( 'Weekly Calendar', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'weekly_agent'])),
                          array('label' => __( 'Monthly View', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'monthly_agents'])),
          )
        ),
        array( 'id' => 'appointments', 'label' => __( 'Appointments', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-inbox', 'link' => OsRouterHelper::build_link(['bookings', 'index']),
          'children' => array(
                          array('label' => __( 'All Appointments', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'index'])),
                          array('label' => __( 'Pending Approval', 'latepoint' ).$pending_apps_count_html, 'icon' => '', 'link' => OsRouterHelper::build_link(['bookings', 'pending_approval'])),
                          array('label' => __( 'Transactions', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['transactions', 'index'])),
          )
        ),
        array('label' => ''),
        array( 'id' => 'services', 'label' => __( 'Services', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-package', 'link' => OsRouterHelper::build_link(['services', 'index']),
          'children' => array(
                          array('label' => __( 'List of Services', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['services', 'index'])),
                          array('label' => __( 'Service Categories', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['service_categories', 'index'])),
          )
        ),
        array( 'id' => 'agents', 'label' => __( 'Agents', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-user', 'link' => OsRouterHelper::build_link(['agents', 'index']),
          'children' => array(
                          array('label' => __( 'Dashboard', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['agents', 'dashboard'])),
                          array('label' => __( 'List of Agents', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['agents', 'index'])),
          )
        ),
        array( 'id' => 'customers', 'label' => __( 'Customers', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-users', 'link' => OsRouterHelper::build_link(['customers', 'index']),
          'children' => array(
                          array('label' => __('Add Customer', 'latepoint'), 'icon' => '', 'link' => OsRouterHelper::build_link(['customers', 'new_form'])),
                          array('label' => __('List of Customers', 'latepoint'), 'icon' => '', 'link' => OsRouterHelper::build_link(['customers', 'index'])),
                        )
        ),
        array( 'id' => 'locations', 'label' => __( 'Locations', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-map-pin', 'link' => OsRouterHelper::build_link(['locations', 'index'])),
        array('label' => ''),
        array( 'id' => 'settings', 'label' => __( 'Settings', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-settings', 'link' => OsRouterHelper::build_link(['settings', 'general']), 
          'children' => array(
                          array('label' => __( 'General Settings', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'general'])),
                          array('label' => __( 'Work Schedule', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'work_periods'])),
                          array('label' => __( 'Custom Fields', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['custom_fields', 'for_customer'])),
                          array('label' => __( 'Setup Wizard', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['wizard', 'setup'])),
                          // array('label' => __( 'Pages', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'pages'])),
                          array('label' => __( 'Steps', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'steps'])),
                          array('label' => __( 'Payments', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'payments'])),
                          array('label' => __( 'Activity', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-bell', 'link' => OsRouterHelper::build_link(['activities', 'index'])),
                          array('label' => __( 'System Status', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['debug', 'status'])),
                          array('label' => __( 'Add-ons', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['addons', 'index'])),
                          array('label' => __( 'Updates', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['updates', 'status'])),
          )
        ),
        array( 'id' => 'notifications', 'label' => __( 'Notifications', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-message-circle', 'link' => OsRouterHelper::build_link(['notifications', 'settings']),
          'children' => array(
                          array('label' => __( 'Notification Settings', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['notifications', 'settings'])),
                          array('label' => __( 'Reminders', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['reminders', 'index'])),
                          array('label' => __( 'SMS Templates', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['notifications', 'sms_templates'])),
                          array('label' => __( 'Email Templates', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['notifications', 'email_templates']))
          )
        ),
        // array( 'label' => __( 'Appearance', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-sliders', 'link' => OsRouterHelper::build_link(['appearance', 'index'])),
      );
      if(OsSettingsHelper::is_env_dev()){
        $menus[] = array( 'label' => __( 'Developer', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-server', 'link' => OsRouterHelper::build_link(['settings', 'generate_demo_data']), 
          'children' => array(
                          array('label' => __( 'Demo Data Install', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'generate_demo_data'])),
                          array('label' => __( 'Database Install', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'database_setup'])),
                        )
        );
      }
    }else{
      $menus = [];
    }
    $menus = apply_filters('latepoint_side_menu', $menus);
    return $menus;
  }

}