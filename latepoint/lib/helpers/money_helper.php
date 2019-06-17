<?php 

class OsMoneyHelper {


  public static function get_currencty_symbol(){
    return '$';
  }

  public static function calculate_full_amount_to_charge($booking,  $apply_coupons = true){
  	$service = new OsServiceModel($booking->service_id);
    $amount = $service->charge_amount;
    if($apply_coupons) $amount = apply_filters('latepoint_full_amount', $amount, $booking);
  	return $amount;
  }

  public static function calculate_deposit_amount_to_charge($booking, $apply_coupons = true){
  	$service = new OsServiceModel($booking->service_id);
    $amount = $service->deposit_amount;
    if($apply_coupons) $amount = apply_filters('latepoint_deposit_amount', $amount, $booking);
  	return $amount;
  }

  public static function format_price($price){
  	$price = $price + 0;
  	global $latepoint_settings;
  	return implode('', array($latepoint_settings->currency_symbol_before, $price, $latepoint_settings->currency_symbol_after));
  }

}