<?php
header('Content-type: application/json; charset=utf-8');

setlocale(LC_ALL, 'ja_JP.UTF-8');
define('TABLE_NAME_PROGRESS', 'user_progress');
define('TABLE_NAME_SCENARIO', 'scenario_data');
define('TABLE_NAME_SCENARIO_ORDER', 'scenario_order');

require_once(__DIR__.'/../lib/dbconnect.php');


/*
  Create Database Connection
 */
try{
    $dbh = dbConnection::getConnection();
}catch(PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

$export_sql =
"SELECT * FROM ".TABLE_NAME_SCENARIO ." as SC".
" INNER JOIN ".TABLE_NAME_SCENARIO_ORDER ." as SC_ORDER".
" ON SC.label = SC_ORDER.label"
" ORDER BY SC_ORDER.order_no";

// query database
$stmt = $dbh->query($export_sql);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
// close database connection
$dbh = null;
?>