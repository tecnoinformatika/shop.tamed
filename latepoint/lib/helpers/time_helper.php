<?php 
class OsTimeHelper {

  private static $timezone = false;

  public static function get_db_weekday_by_number($number){
    $weekdays = ['mo','tu','we','th','fr','sa','su'];
    return $weekdays[$number - 1];
  }

  public static function is_valid_date($date_string){
    return (bool)strtotime($date_string);
  }

  public static function nice_date($date){
    if($date == OsTimeHelper::today_date('Y-m-d')){
      $nice_date = __('Today', 'latepoint');
    }else{
      $nice_date = self::get_nice_date_no_year($date);
    }
    return $nice_date;
  }

  public static function today_date($date_format = 'Y-m-d'){
    return current_time($date_format);
  }

  public static function get_modified_now_object($modify_by){
    $now_datetime = self::now_datetime_object();
    return $now_datetime->modify($modify_by);
  }

  public static function now_datetime_object(){
    $now_time_object = OsWpDateTime::os_createFromFormat("Y-m-d H:i:s", current_time('mysql'));
    return $now_time_object;
  }

  public static function get_time_system(){
    global $latepoint_settings;
    return $latepoint_settings->time_system;
  }

  public static function is_army_clock(){
    return (self::get_time_system() == 24);
  }

  public static function get_time_systems_list_for_select(){
    return array( array( 'value' => '12', 'label' => __('12-hour clock', 'latepoint')), array( 'value' => '24', 'label' => __('24-hour clock', 'latepoint')));
  }

  
  public static function get_time_systems_list(){
    return array('12' => __('12-hour clock', 'latepoint'), '24' => __('24-hour clock', 'latepoint'));
  }

  
  public static function get_nice_date_no_year($date){
    $d = OsWpDateTime::os_createFromFormat("Y-m-d", $date);
    if($d->format('Y') == OsTimeHelper::today_date('Y')){
      return $d->format("F j");
    }else{
      return $d->format("M j, Y");
    }
  }

  public static function get_wp_timezone() {
    if(self::$timezone) return self::$timezone;
    $timezone_string = get_option( 'timezone_string' );
    if ( ! empty( $timezone_string ) ) {
      return new DateTimeZone( $timezone_string );
    }
    $offset  = get_option( 'gmt_offset' );
    $hours   = (int) $offset;
    $minutes = abs( ( $offset - (int) $offset ) * 60 );
    $offset  = sprintf( '%+03d:%02d', $hours, $minutes );
    self::$timezone = new DateTimeZone( $offset );
    return self::$timezone;
  }

  public static function get_wp_timezone_name() {
    $timezone_obj = self::get_wp_timezone();
    if($timezone_obj){
      return $timezone_obj->getName();
    }else{
      return 'America/New_York';
    }
  }


  public static function convert_datetime_to_minutes($datetime){
    return $datetime->format('i') + ($datetime->format('G') * 60);
  }

	public static function get_current_minutes(){
    $now = new OsWpDateTime('now');
    return $now->format('i') + ($now->format('G') * 60);
	}

  public static function convert_time_to_minutes($time, $ampm = false){
    if(strpos($time, ':') === false) return 0;

    list($hours, $minutes) = explode(':', $time);
    if($hours == '12' && $ampm == 'am'){
      // midnight
      $hours = '0';
    }
    if($ampm == 'pm' && $hours < 12){
      // convert to 24 hour format
      $hours = $hours + 12;
    }
    $minutes = ($hours * 60) + $minutes;
    return $minutes;
  }

  public static function am_or_pm($minutes) {
    if(self::is_army_clock()) return '';
    return ($minutes < 720) ? 'am' : 'pm';
  }

  public static function minutes_to_hours($time) {
    if($time){
      $hours = floor($time / 60);
      if(!self::is_army_clock() && $hours > 12) $hours = $hours - 12;
      return $hours;
    }else{
      return 0;
    }
  }


  public static function minutes_to_army_hours_and_minutes($time) {
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf('%02d:%02d', $hours, $minutes);
  }

  public static function minutes_to_hours_and_minutes($minutes, $format = '%02d:%02d', $add_ampm = true) {
    if(!$format) $format = '%02d:%02d';

    if ($minutes === '') {
        return;
    }
    $ampm = ($add_ampm) ? self::am_or_pm($minutes) : '';
    $hours = self::minutes_to_hours($minutes);
    $minutes = ($minutes % 60);

    return sprintf($format, $hours, $minutes).$ampm;
  }

}