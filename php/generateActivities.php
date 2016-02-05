<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 21.10.2015
 * Time: 11:58
 */
// mysql activities creation

include_once('../inc/config.php');
include_once('../inc/connectDataBase.php');

$contacts_quantity = 36;

if ( $_GET['action'] == 'create') {
//activities bank
    $actions = [];
    $actions[] = ['activity' => 'development', 'duration' => 3 * 60 * 60];
    $actions[] = ['activity' => 'support', 'duration' => 1 * 60 * 60];
    $actions[] = ['activity' => 'marketing', 'duration' => 2 * 60 * 60];
    $actions[] = ['activity' => 'documents', 'duration' => 1 * 60 * 60];
    $actions[] = ['activity' => 'negotiations', 'duration' => 1 * 60 * 60];

    date_default_timezone_set('Europe/Moscow');

    //date & time of work beginning
    $itime    = strtotime('2015-01-10 9:00:00');
    $end_time = strtotime('2015-12-31 19:00:00');

    $working_periods = [];
    $periods_quantity = 0;


    //work periods calculation
    while ( $itime <= $end_time ) {

        //number of week day determine
        $day = date('N', $itime);

        //Saturday(6) and Sunday(7) are not joined
        if ($day !== '6' && $day !== '7') {

            // four hours before lunch
            $end = $itime + 4 * 60 * 60;
            if ($end > $end_time) $end = $end_time;
            $working_periods[] = ['begin' => $itime, 'end' => $end];
            $periods_quantity++;

            // four hours after lunch
            $begin = $itime + 5 * 60 * 60;
            if ($begin >= $end_time) $begin = $end_time;
            $end = $itime + 9 * 60 * 60;
            if ($end > $end_time) $end = $end_time;
            $working_periods[] = ['begin' => $begin, 'end' => $end];
            $periods_quantity++;
        }

        //twenty four hours step to come into the next day
        $itime = $itime + 24 * 60 * 60;
    }

    $pr1 = TRUE;
    //activities table creation
    $activities_table = [];
    for ($i = 1; $i <= $contacts_quantity - 2; $i += 3) {

        for ($j = 0; $j <= $periods_quantity - 1; $j++) {

            $pr = TRUE;
            $sum = 0;
            $start_time = $working_periods[$j]['begin'];

            //working period length
            $interval = $working_periods[$j]['end'] - $working_periods[$j]['begin'];

            while ($pr) {
                //random choice of activity
                $k = rand(0, 4);
                //duration choice from the bank
                //random choice of duration
                $duration = rand(10 * 60, 2 * 60 * 60);

                //planned time to finish activity
                $end_time = $start_time + $duration;


                if ($end_time > $working_periods[$j]['end']) {
                    //duration correction to correspond working period end
                    $duration = $duration - ($end_time - $working_periods[$j]['end']);

                    $pr = FALSE;
                }
                $sum = $sum + $duration;

                if ($sum <= $interval) {
                    if ($k == 0 || $k == 3) {
                        //development & documents activities
                        $contact_id = NULL;
                    } else {
                        //switch between clients
                        if ($pr1) {
                            $contact_id = $i + 1;
                            $pr1 = FALSE;
                        } else {
                            $contact_id = $i + 2;
                            $pr1 = TRUE;
                        }
                    }
                    $date = date('Y-m-d H:i:s', $start_time);
                    if ( $duration > 2000 ) $duration = $duration - rand(600,900);
                    $activities_table[] = [
                        'contact_id' => $contact_id,
                        'duration' => $duration,
                        'employee_id' => $i,
                        'created_at' => $date,
                        'action' => $actions[$k]['activity']
                    ];

                }

                $start_time = $start_time + $duration;
            }
        }
    }

    // activities table sorting in ascending order according to the time
    // of the beginning of activity-created_at
    foreach ($activities_table as $key => $row) {

        $label[$key] = $row['created_at'];

    }

    array_multisort($label, SORT_ASC, $activities_table);

    // activities table insert into the activities data base
    foreach ($activities_table as $row) {

    // query forming to insert record
    $str = "INSERT INTO activities ( contact_id,
                                     duration,
                                     employee_id,
                                     created_at,
                                     action )
                      VALUES (
                          '" . $row['contact_id'] . "'" .
                        ",'" . $row['duration'] . "'" .
                        ",'" . $row['employee_id'] . "'" .
                        ",'" . $row['created_at'] . "'" .
                        ",'" . $row['action'] . "')";

    // insert record

    $query = mysql_query($str);
    if (!$query) {
        echo "</br>" . 'Something went wrong :' . mysql_error();
        die;
    }
    }
    echo "</br>" . 'Created :' . mysql_insert_id() . ' records.';
}
else if ( $_GET['action'] == 'select') {
    // all records selection

    $str = 'SELECT * FROM activities ';
    $query = mysql_query($str);
    if (!$query) {
        echo "</br>" . 'Something went wrong :' . mysql_error();
        die;
    }
    // selected records quantity output
    echo "</br>" . 'Selected : ' . mysql_num_rows($query). ' records.';

    for ( $i = 1; $i <= $contacts_quantity; $i++ ){

        // for every id_contact records selection

        // query to select  record from activities data base
        $str = 'SELECT * FROM activities WHERE contact_id='.$i;
        // select records
        $query = mysql_query($str);
        if (!$query) {
            echo "</br>" . 'Something went wrong :' . mysql_error();
            die;
        }
        // selected records quantity output
        echo "</br>" . $i . ' - Selected : ' . mysql_num_rows($query). ' records.';

    }

}
else
    echo "Called action : ' ".$_GET['action']. " ' is incorrect.";