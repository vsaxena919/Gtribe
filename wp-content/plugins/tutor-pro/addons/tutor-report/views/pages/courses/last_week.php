<?php
global $wpdb;


/**
 * Getting the last week
 */
$previous_week = strtotime("-1 week +1 day");
$start_week = strtotime("last sunday midnight",$previous_week);
$end_week = strtotime("next saturday",$start_week);
$start_week = date("Y-m-d",$start_week);
$end_week = date("Y-m-d",$end_week);


/**
 * Format Date Name
 */
$begin = new DateTime($start_week);
$end = new DateTime($end_week.' + 1 day');
$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);

$datesPeriod = array();
foreach ($period as $dt) {
	$datesPeriod[$dt->format("Y-m-d")] = 0;
}

/**
 * Query last week
 */
$single_course_query = '';
if ($course_id){
	$single_course_query = "AND post_parent = {$course_id}";
}

$enrolledQuery = $wpdb->get_results( "
              SELECT COUNT(ID) as total_enrolled, 
              DATE(post_date)  as date_format 
              from {$wpdb->posts} 
              WHERE post_type = 'tutor_enrolled' 
              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
              {$single_course_query}
              GROUP BY date_format
              ORDER BY post_date ASC ;");

$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
foreach ($chartData as $key => $enrolledCount){
    unset($chartData[$key]);
    $formatDate = date('d M', strtotime($key));
	$chartData[$formatDate] = $enrolledCount;
}

/**
 * Getting enrolled courses within this time period
 */
if ( ! $course_id) {
	$enrolledProduct = $wpdb->get_results( "
              SELECT COUNT(enrolled.ID) as total_enrolled, 
              DATE(enrolled.post_date)  as date_format,
              course.ID,
              course.post_title 
              
              from {$wpdb->posts} enrolled
              LEFT JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
              WHERE enrolled.post_type = 'tutor_enrolled' 
              AND (enrolled.post_date BETWEEN '{$start_week}' AND '{$end_week}')
              GROUP BY course.ID
              ORDER BY total_enrolled DESC LIMIT 0,50 ;" );
}

include TUTOR_REPORT()->path.'views/pages/courses/body.php';
?>
