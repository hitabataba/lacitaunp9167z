<?php
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=scenario.csv");
header("Content-Transfer-Encoding: binary");

setlocale(LC_ALL, 'ja_JP.UTF-8');
define('TABLE_NAME_PROGRESS', 'user_progress');
define('TABLE_NAME_SCENARIO', 'scenario_data');


require_once(__DIR__.'/../lib/dbconnect.php');

define('IMAGE_DIR', __DIR__.'/../img/');
define('AUDIO_DIR', __DIR__.'/../audio/');
$filedir = __DIR__ ;

$err_msg = array();
$cau_msg = array();
$msg = array();

$dbh = dbConnection::getConnection();

/*
  define csv file information
*/
$file_path = $filedir."/scenario.csv";


$export_csv_title = ["顧客ID", "名前", "性別", "電話番号"];
$export_sql = "SELECT * FROM ".TABLE_NAME_SCENARIO ;

/*
  Create Database Connection
 */
try{
    $dbh = dbConnection::getConnection();
}catch(PDOException $e){
    print('Connection failed:'.$e->getMessage());
    die();
}

// encoding title into SJIS-win
foreach( $export_csv_title as $key => $val ){

    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
}
/*
    Make CSV content
 */
// query database
$stmt = $dbh->query($export_sql);

// create csv sentences
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
  foreach($row as $v){
    echo("\"".mb_convert_encoding($v, 'SJIS-win', 'UTF-8')."\",");
  }
  echo("\n");
}

// close database connection
$dbh = null;



?>