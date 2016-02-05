<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 21.10.2015
 * Time: 15:27
 */
$messages = [];
include_once('../inc/config.php');
include_once('../inc/connectDataBase.php');
$option = $_GET['option'];
$begin_date = $_GET['start'];
$end_date = $_GET['finish'];
//barChart
if (  $option == 'barChart' ) {
    $str = "CREATE TEMPORARY TABLE extract  SELECT employee_id, first_name
        , last_name, action, duration
        FROM $activities_table  LEFT JOIN $contacts_table
        ON $activities_table.employee_id = $contacts_table.id
        WHERE DATE(created_at) >= '"
        . $begin_date .
        "'AND DATE(created_at) <= '"
        . $end_date . "'"
        . ' ORDER BY employee_id';
    $query = mysql_query($str);
    if (!$query) {
        $messages[] = ['place' => 'getActivities.php', 'message' =>
            'SELECT - something went wrong :' . mysql_error()];
        echo json_encode($messages);
        die;
    }
    $str = "SELECT employee_id, first_name, last_name,action,SUM(duration)
        FROM extract GROUP BY employee_id, action ";
    $query = mysql_query($str);
    if (!$query) {
        $messages[] = ['place' => 'getActivities.php', 'message' =>
            'SELECT - something went wrong :' . mysql_error()];
        echo json_encode($messages);
        die;
    }
    if (mysql_num_rows( $query ) == 0) {
        $messages[] = ['place' => '', 'message' =>
            'For the period from '. $begin_date . ' 00:00 till '
            . $end_date . ' 24:00 there are no activities.'];
        echo json_encode($messages);
        die;
    }
    $records_quantity = mysql_num_rows($query);
    $selected = [];
    while ($row = mysql_fetch_array($query)) {
        $selected[] = ['employee_id' => $row['employee_id']
            ,'first_name' => $row['first_name']
            , 'last_name' => $row['last_name']
            , 'action' => $row['action']
            , 'duration' => $row['SUM(duration)']
        ];
    }
    $first_entrance = TRUE;
    $result = [];
    $actions = [];
    $sum_employee_duration = 0;
    foreach ($selected as $row_selected) {
        $records_quantity--; //counter
        if ($first_entrance) {
            $employee = $row_selected ['employee_id'];
            $first_name = $row_selected ['first_name'];
            $last_name = $row_selected ['last_name'];
            $first_entrance = FALSE;
        }
        if ($employee != $row_selected ['employee_id'] || $records_quantity == 0) {
            //saving employee_id record in array
            if ($records_quantity == 0) {
                //last record duration addition
                $sum_employee_duration += $row_selected ['duration'];
                //saving one activity duration for last record
                $actions[] = [$row_selected ['action']
                => round($row_selected ['duration']/3600,2)] ;
            }
            $result[] = [
                  'name' => substr($first_name, 0, 1) . '. ' . $last_name
                , 'sum_employee_duration' => round($sum_employee_duration / (3600), 2)
                , 'activities' => $actions
            ];
            if ($records_quantity != 0) {
                //transition to the following employee
                $employee = $row_selected ['employee_id'];
                $first_name = $row_selected ['first_name'];
                $last_name = $row_selected ['last_name'];
                $sum_employee_duration = $row_selected['duration'];
                $actions = [];
                $actions[] = [$row_selected ['action'] => round($row_selected ['duration']/3600,2)] ;
            }
        } else {
            // summation of all activities durations for  employee_id
            $sum_employee_duration += $row_selected ['duration'];
            //saving one activity duration
            $actions[] = [$row_selected ['action'] => round($row_selected ['duration']/3600,2)];
        }
    }
//preparing for sorting associative array result
    $label = [];
    foreach ($result as $key => $row) {
        $label[$key] = $row['sum_employee_duration'];
    }
// result  array sorting in sum_employee_duration descending order
    array_multisort($label, SORT_DESC, $result);
//list of all activities getting
    $str = "SELECT action FROM $activities_table
        WHERE DATE(created_at) >= '"
        . $begin_date .
        "' AND DATE(created_at) <= '"
        . $end_date . "'"
        . 'GROUP BY action';
    $query = mysql_query($str);
    $selected = [];
    while ($row = mysql_fetch_array($query)) {
        $selected[] =  $row['action'] ;
    }
    array_unshift($result,['actions'=> $selected]);
    echo json_encode($result);
}
//pieChart
else {
    $str = "SELECT action,SUM(duration) FROM $activities_table
        WHERE DATE(created_at) >= '"
        . $begin_date .
        "' AND DATE(created_at) <= '"
        . $end_date . "'"
        . 'GROUP BY action';
    $query = mysql_query($str);
    if (!$query) {
        $messages[] = ['place' => 'getActivities.php', 'message' =>
            'SELECT - something went wrong :' . mysql_error()];
        echo json_encode($messages);
        die;
    }
    if (mysql_num_rows($query) == 0) {
        $messages[] = ['place' => '', 'message' =>
            'For the period from '. $begin_date . ' 00:00 till '
            . $end_date . ' 24:00 there are no activities.'];
        echo json_encode($messages);
        die;
    }
    $records_quantity = mysql_num_rows($query);
    $selected = [];
    while ($row = mysql_fetch_array($query)) {
        $selected[] = [
            'action' => $row['action']
           ,'sum_action' => round($row['SUM(duration)'] / 3600, 2)
        ];
    }
    echo json_encode($selected);
}
//#######################################
