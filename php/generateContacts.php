<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 19.10.2015
 * Time: 18:58
 */
// mysql contacts creation

include_once('../inc/config.php');
include_once('../inc/connectDataBase.php');


if ( $_GET['action'] == 'create')
{
 // contacts table creation
    for ( $i=1; $i<= 6; $i++) {
        // first_name creating
        switch ($i) {
            case 1 :
                $name = 'Ivan';
                break;
            case 2 :
                $name = 'Semen';
                break;
            case 3 :
                $name = 'Petr';
                break;
            case 4 :
                $name = 'Feodor';
                break;
            case 5 :
                $name = 'Vladimir';
                break;
            case 6 :
                $name = 'Victor';
                break;
        }
        for ($j = 1; $j <= 6; $j++) {
            // last_name creating
            switch ($j) {
                case 1 :
                    $surname = 'Ivanov';
                    break;
                case 2 :
                    $surname = 'Semenov';
                    break;
                case 3 :
                    $surname = 'Petrov';
                    break;
                case 4 :
                    $surname = 'Feodorov';
                    break;
                case 5 :
                    $surname = 'Vladimirov';
                    break;
                case 6 :
                    $surname = 'Voronov';
                    break;
            }
            // query to insert record into contacts table
            $str = "INSERT INTO contacts (first_name, last_name)  VALUES ('" . $name . "'" .
                ",'" . $surname . "')";
            // insert record
            $query = mysql_query($str);
            if ( !$query ) {
                echo 'Something went wrong :' . mysql_error();
                die;
            }

        }
    }
    // inserted records quantity output
    echo "</br>". 'Created :' . mysql_insert_id() . ' records.' ;

}

else if ( $_GET['action'] == 'show') {
    // all records selection
    $query = mysql_query('SELECT * FROM contacts ORDER BY last_name, first_name');
    // selected records quantity output
    echo "</br>" . 'Shown : ' . mysql_num_rows($query). ' records.';
    // all selected records output
    while ($row = mysql_fetch_array($query)) {
        //  selected record output
        echo "</br>" . $row['id'] . '-' . $row['first_name'] . ' ' . $row['last_name'];
    }
}
else
echo "Called action : ' ".$_GET['action']. " ' is incorrect.";