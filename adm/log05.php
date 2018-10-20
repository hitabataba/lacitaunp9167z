<?php
header("Content-Type: text/html; charset=UTF-8");
setlocale(LC_ALL, 'ja_JP.UTF-8');

define('TABLE_NAME_PROGRESS', 'user_progress');
define('TABLE_NAME_SCENARIO', 'scenario_data');
define('TABLE_NAME_QUESTIONNAIRE', 'user_questionnaire');

////////////////////
//参加者カウント
////////////////////
$dbh = dbConnection::getConnection();
$sql = 'select count(*) from ' . TABLE_NAME_PROGRESS . ' where owner_name is NULL';
$sth = $dbh->query($sql);
// レコードが存在しなければNULL
$row = $sth->fetch();
$challenger_count = $row["count"];

////////////////////
//進捗確認
////////////////////
$sql = 'select progress from ' . TABLE_NAME_PROGRESS . ' where owner_name is NULL';
$sth = $dbh->query($sql);
$progress_total = 0;
while($row = $sth->fetch()){
  $progress = json_decode($row['progress']);
  if($current_count[$progress[0]]){
    $current_count[$progress[0]] ++;
  }else{
    $current_count[$progress[0]] = 1;
  }
}

$sql = "select M.label,M.no,M.text from " . TABLE_NAME_SCENARIO . " as M RIGHT JOIN 
   (select label,min(no) as minno from  " . TABLE_NAME_SCENARIO . " where text is not null group by label) as B 
    ON M.no = B.minno AND M.label = B.label";
$sth = $dbh->query($sql);
while($row = $sth->fetch()){
  $scenario_ary[$row['label']] = $row['text'];
}

////////////////////
//アンケート結果
////////////////////
$sql = "select M.label,M.text from " . TABLE_NAME_SCENARIO . " as M RIGHT JOIN 
   (select label,min(no) as minno from  " . TABLE_NAME_SCENARIO . " where format = 'button_q' group by label) as B 
  ON M.no = B.minno AND M.label = B.label";
$sth = $dbh->query($sql);
while($row = $sth->fetch()){
  $questscenario_ary[$row['label']] = $row['text'];
}

$sql = "select answer from " . TABLE_NAME_QUESTIONNAIRE . " where owner_name is NULL";
$sth = $dbh->query($sql);
while($row = $sth->fetch()){
  $answer = json_decode($row['answer']);
  foreach($answer as $q => $a){
    if($answer_count[$q][$a]){
      $answer_count[$q][$a] ++;
    }else{
      $answer_count[$q][$a] = 1;
    }
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
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UFT-8">
<style>
table,tr,td{
  border:solid 1px black;
  border-collapse: collapse;
}
</style>
</head>
<body>
3月号からこれまでの挑戦者数：<?php echo $challenger_count; ?>
<br>
<br>
5月号以降の進捗：
<table>
<?php foreach($scenario_ary as $label => $text): ?>
<tr>
<td><?php echo $label; ?></td>
<td><?php echo mb_substr($text,0,40);?></td>
<td><?php echo $current_count[$label]; ?></td>
</tr>
<?php $progress_total += $current_count[$label]; ?>
<?php endforeach; ?>
<td colspan="2" align="right">合計</td>
<td><?php echo $progress_total; ?></td>
</table>

<br>
5月号以降のアンケート：
<table>
<?php foreach($questscenario_ary as $label => $text): ?>
<tr>
<td><?php echo $label; ?></td>
<td><?php echo mb_substr($text,0,40);?></td>
  <?php foreach($answer_count[$label] as $ans => $count): ?>
  <td><?php echo $ans; ?></td>
  <td><?php echo $count; ?></td>
  <?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>


</body>
