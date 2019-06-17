<?php 
$add_string_to_id = '_'.OsUtilHelper::random_text('hexdec', 8);
echo OsFormHelper::hidden_field('booking[customer_id]', $booking->customer_id, [ 'class' => 'latepoint_customer_id', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[service_id]', $booking->service_id, [ 'class' => 'latepoint_service_id', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[agent_id]', $booking->agent_id, [ 'class' => 'latepoint_agent_id', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[location_id]', $booking->location_id, [ 'class' => 'latepoint_location_id', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[start_date]', $booking->start_date, [ 'class' => 'latepoint_start_date', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[start_time]', $booking->start_time, [ 'class' => 'latepoint_start_time', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[payment_method]', $booking->payment_method, [ 'class' => 'latepoint_payment_method', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[payment_token]', $booking->payment_token, [ 'class' => 'latepoint_payment_token', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('booking[payment_portion]', $booking->payment_portion, [ 'class' => 'latepoint_payment_portion', 'add_string_to_id' => $add_string_to_id]);

echo OsFormHelper::hidden_field('restrictions[show_locations]', $restrictions['show_locations'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[show_agents]', $restrictions['show_agents'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[show_services]', $restrictions['show_services'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[show_service_categories]', $restrictions['show_service_categories'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[selected_service]', $restrictions['selected_service'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[selected_service_category]', $restrictions['selected_service_category'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[selected_location]', $restrictions['selected_location'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[selected_agent]', $restrictions['selected_agent'], ['add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('restrictions[calendar_start_date]', $restrictions['calendar_start_date'], ['add_string_to_id' => $add_string_to_id]);

echo OsFormHelper::hidden_field('current_step', $current_step, ['class' => 'latepoint_current_step', 'add_string_to_id' => $add_string_to_id]);
echo OsFormHelper::hidden_field('step_direction', 'next', ['class' => 'latepoint_step_direction', 'add_string_to_id' => $add_string_to_id]);
?>