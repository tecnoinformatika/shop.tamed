<?php 
class OsWpDateTime extends DateTime {
  function __construct($time = 'now'){
      parent::__construct($time, OsTimeHelper::get_wp_timezone());
  }

  public static function os_createFromFormat($format, $datetime_string){
  	return DateTime::createFromFormat($format, $datetime_string, OsTimeHelper::get_wp_timezone());
  }

  public static function os_get_start_of_google_event($google_event){
  	if(!empty($google_event->start->dateTime)){
  		$date_string = $google_event->start->dateTime;
  		$date_format = \DateTime::RFC3339;
  	}else{
  		$date_string = $google_event->start->date;
  		$date_format = 'Y-m-d';
  	}
		return self::os_createFromFormat($date_format, $date_string);
  }

  public static function os_get_end_of_google_event($google_event){
  	if(!empty($google_event->end->dateTime)){
  		$date_string = $google_event->end->dateTime;
  		$date_format = \DateTime::RFC3339;
  	}else{
  		$date_string = $google_event->end->date;
  		$date_format = 'Y-m-d';
  	}
		return self::os_createFromFormat($date_format, $date_string);
  }
}