<?php 

class OsLocationHelper {

	static $locations;
	static $selected_location = false;

  public static function locations_selector_html($locations_for_select){
    $html = '';
    if((OsLocationHelper::count_locations() > 1) && (count($locations_for_select) > 1)){
      $html.= '<select class="os-main-location-selector" data-route="'.OsRouterHelper::build_route_name('locations', 'set_selected_location').'">';
      foreach($locations_for_select as $location_for_select){
        $selected = (OsLocationHelper::get_selected_location_id() == $location_for_select->id) ? 'selected="selected"' : '';
        $html.= '<option '.$selected.' value="'.$location_for_select->id.'">'.$location_for_select->name.'</option>';
      }
      $html.= '</select>';
    }
    return $html;
  }

  public static function get_locations($agent_id = false){
  	$locations = new OsLocationModel();
  	$locations = $locations->get_results_as_models();
  	return $locations;
  }

  public static function get_locations_list($agent_id = false){
    $locations = new OsLocationModel();
    $locations = $locations->get_results_as_models();
    $locations_list = [];
    foreach($locations as $location){
      $locations_list[] = ['value' => $location->id, 'label' => $location->name];
    }
    return $locations_list;
  }

  public static function count_locations($agent_id = false){
  	$locations = new OsLocationModel();
  	return $locations->count();
  }

  public static function set_selected_location($selected_location_id){
		$location_model = new OsLocationModel();
  	$location = $location_model->where(['id' => $selected_location_id])->set_limit(1)->get_results_as_models();
  	if($location){
	  	$_SESSION['selected_location_id'] = $selected_location_id;
	  	self::$selected_location = $location;
	  	return $location;
  	}else{
  		throw new Exception('Location ID does not exist');
  		return false;
  	}
  }

  public static function get_selected_location_id(){
  	$selected_location = self::get_selected_location();
  	return $selected_location->id;
  }

  public static function get_selected_location(){
  	if(self::$selected_location) return self::$selected_location;

    $selected_location = false;
		$location_model = new OsLocationModel();

    // try get from session
  	if(isset($_SESSION['selected_location_id'])){
  		$selected_location = $location_model->where(['id' => $_SESSION['selected_location_id']])->set_limit(1)->get_results_as_models();
    }
    // try get first location from db
		if(!$selected_location){
			// locatoin with ID stored in sessions does not exist
      $location_model = new OsLocationModel();
  		$selected_location = $location_model->set_limit(1)->get_results_as_models();
    }

    // no locations in db - create default one
		if(!$selected_location){
			$selected_location = self::create_default_location();
		}
  	self::set_selected_location($selected_location->id);
  	return $selected_location;
  }

  public static function create_default_location(){
		$location_model = new OsLocationModel();
  	$location_model->name = __('Main Location', 'latepoint');
  	if($location_model->save()){
	  	$connector = new OsConnectorModel();
	  	$incomplete_connections = $connector->where(['location_id' => 'IS NULL'])->get_results_as_models();
      if($incomplete_connections){
        foreach($incomplete_connections as $incomplete_connection){
          $incomplete_connection->update_attributes(['location_id' => $location_model->id]);
        }
      }
      $bookings = new OsBookingModel();
      $incomplete_bookings = $bookings->where(['location_id' => 'IS NULL'])->get_results_as_models();
      if($incomplete_bookings){
  	  	foreach($incomplete_bookings as $incomplete_booking){
  	  		$incomplete_booking->update_attributes(['location_id' => $location_model->id]);
  	  	}
      }
  	}
  	return $location_model;
  }
}