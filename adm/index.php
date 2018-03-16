<?php
setlocale(LC_ALL, 'ja_JP.UTF-8');
define('TABLE_NAME_PROGRESS', 'user_progress');

$progress = array();
$data_count = array();
$data_name = array(
0 => array("a"=>"とても良い","b"=>"普通	","c"=>"改善するべき"),
1 => array("a"=>"深層学習周辺の最新動向","b"=>"AI and Society","c"=>"無かった","d"=>"AIとデータ"),
2 => array("a"=>"研究会報告","b"=>"私のブックマーク","c"=>"グローバルアイ","d"=>"会議報告"),
3 => array("a"=>"楽しかった","b"=>"普通	","c"=>"楽しくなかった"),
);

$dbh = dbConnection::getConnection();
$sql = 'select count(*) from ' . TABLE_NAME_PROGRESS . ' where owner_name is NULL';
$sth = $dbh->query($sql);
// レコードが存在しなければNULL
$row = $sth->fetch();
echo('挑戦者数：'.$row["count"]);
echo("<br>");

//$dbh = dbConnection::getConnection();
$sql = "select progress,create_timestamp, update_timestamp from " . TABLE_NAME_PROGRESS . " where owner_name is NULL and progress LIKE '%TEXT07%'";
$sth = $dbh->query($sql);

$fc = 0;
while($row = $sth->fetch()){
  $progress[] = json_decode($row['progress']);
  if($progress[$fc][1]){
    $data_count[0][str_replace("TEXT04","",$progress[$fc][1])]++;
  }
  if($progress[$fc][2]){
    $data_count[1][str_replace("TEXT05","",$progress[$fc][2])]++;
  }
  if($progress[$fc][3]){
    $data_count[2][str_replace("TEXT06","",$progress[$fc][3])]++;
  }
  if($progress[$fc][4]){
    $data_count[3][str_replace("TEXT07","",$progress[$fc][4])]++;
  }
  $fc++;
}
echo("クリア者数：".count($progress));
echo("<br>");
ksort($data_count);
foreach($data_count as $key => $val){
  echo("問".($key+1)."<br>");
  ksort($val);
  foreach($val as $k => $v){
    echo("　".$data_name[$key][$k]."：".$v."<br>");
  }
}

// データベースへの接続を管理するクラス
class dbConnection {
  // インスタンス
  protected static $db;
  // コンストラクタ
  private function __construct() {

    try {
      // 環境変数からデータベースへの接続情報を取得し
      $url = parse_url(getenv('DATABASE_URL'));
      // データソース
      $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
      // 接続を確立
      self::$db = new PDO($dsn, $url['user'], $url['pass']);
      // エラー時例外を投げるように設定
      self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
    catch (PDOException $e) {
      error_log('Connection Error: ' . $e->getMessage());
    }
  }

  // シングルトン。存在しない場合のみインスタンス化
  public static function getConnection() {
    if (!self::$db) {
      new dbConnection();
    }
    return self::$db;
  }
}

?>