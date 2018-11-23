<?php
header("Content-Type: text/html; charset=UTF-8");
setlocale(LC_ALL, 'ja_JP.UTF-8');
define('TABLE_NAME_PROGRESS', 'user_progress');
define('TABLE_NAME_SCENARIO', 'scenario_data');
define('TABLE_NAME_KEYWORD', 'message_data');

define('IMAGE_DIR', __DIR__.'/../img/');
define('AUDIO_DIR', __DIR__.'/../audio/');
$filepath = __DIR__ ;

$err_msg = array();
$cau_msg = array();
$msg = array();

$dbh = dbConnection::getConnection();

if (is_uploaded_file($_FILES["csvfile"]["tmp_name"])) {
  $file_tmp_name = $_FILES["csvfile"]["tmp_name"];
  $file_name = $_FILES["csvfile"]["name"];

  $filekind = $_POST["filekind"];
  switch($filekind){
    case "keyword":
      $filekind_name = "キーワードメッセージ";
      $first_column = "keyword";
      $target_table  = TABLE_NAME_KEYWORD;
      break;
    case "senario":
    default:
      $filekind_name = "シナリオ";
      $first_column = "label";
      $target_table  = TABLE_NAME_SCENARIO;
      break;
  }

  //拡張子を判定
  if (pathinfo($file_name, PATHINFO_EXTENSION) != 'csv') {
    $err_msg[] = 'CSVファイルのみ対応しています。';
  } else {
    //ファイルをdataディレクトリに移動
    if (move_uploaded_file($file_tmp_name, $filepath.$file_name)) {
      //後で削除できるように権限を644に
      chmod($filepath.$file_name, 0644);
      file_put_contents($filepath.$file_name, mb_convert_encoding(file_get_contents($filepath.$file_name), 'UTF-8', 'SJIS'));
      $msg[] = $file_name . "を".$filekind_name."データとしてアップロードしました。";
      $file = $filepath.$file_name;
      $fp   = fopen($file, "r");

      $i=0;
      //配列に変換する
      while (($line = fgetcsv($fp, 0, ",")) !== FALSE) {
        switch($i){
        case 0:
          $header = $line;
          break;
        case 1:
          break;
        default:
          $fc = 0;
          if(!is_null($line[0])){
            $form_line = array();
            foreach ($line as $cel) {
              if(!is_null($cel)){
                if($cel != ''){
                  if($header[$fc]!="no"){
                    $form_line[$header[$fc]] = $cel;
                  }else{
                    $form_line[$header[$fc]] = $cel-1;
                  }
                }else{
                  $form_line[$header[$fc]] = null;
                }
              }else{
                  $form_line[$header[$fc]] = null;
              }
              $fc++;
            }
            if(trim($form_line['format'])){
              $data[$line[0]][] = $form_line;
            }
          }
          break;
        }
        $i++;
      }
      fclose($fp);
      //ファイルの削除
      unlink($filepath.$file_name);
    } else {
      $err_msg[] = "ファイルをアップロードできません。";
    }
  }
} else {
  $err_msg[] = "ファイルを選択してください。";
}

if($data){
  foreach($data as $text){
    $lc = 1;
    foreach($text as $t){

      $vali_head = $first_column.":「".$t[$first_column]."」の";

/*
      if(is_null($t[$first_column]) || trim($t[$first_column]) == ""){
        $err_msg[] = $vali_head.$lc."行目→formatがtextですが、textがありません。";
      }
*/
      if($t['format']=="text" && is_null($t['text'])){
        $err_msg[] = $vali_head.$lc."行目→formatがtextですが、textがありません。";
      }
      if($t['format']=="image" && is_null($t['file_name'])){
        $err_msg[] = $vali_head.$lc."行目→formatがimgですが、画像ファイル名がありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && is_null($t['button_text_1'])){
        $err_msg[] = $vali_head.$lc."行目→formatがbuttonですが、1つ目のボタンテキスト button_text_1 がありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && is_null($t['button_flg_1'])){
        $err_msg[] = $vali_head.$lc."行目→formatがbuttonですが、1つ目のボタンID button_flg_1 がありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && (is_null($t['button_text_2']) xor is_null($t['button_flg_2']))){
        $err_msg[] = $vali_head.$lc."行目→2つ目のボタンテキスト button_text_2 かボタンID button_flg_2 かのどちらかがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && (is_null($t['button_text_3']) xor is_null($t['button_flg_3']))){
        $err_msg[] = $vali_head.$lc."行目→3つ目のボタンテキスト button_text_3 かボタンID button_flg_3 かのどちらかがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && (is_null($t['button_text_4']) xor is_null($t['button_flg_4']))){
        $err_msg[] = $vali_head.$lc."行目→4つ目のボタンテキスト button_text_4 かボタンID button_flg_4 かのどちらかがありません。";
      }
/*
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_1']) && !isScenarioLabel($data,trim($t['button_flg_1']))){
        $err_msg[] = $vali_head.$lc."行目→1つ目の遷移先ID button_flg_1 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_2']) && !isScenarioLabel($data,trim($t['button_flg_2']))){
        $err_msg[] = $vali_head.$lc."行目→2つ目の遷移先ID button_flg_2 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_3']) && !isScenarioLabel($data,trim($t['button_flg_3']))){
        $err_msg[] = $vali_head.$lc."行目→3つ目の遷移先ID button_flg_3 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_4']) && !isScenarioLabel($data,trim($t['button_flg_4']))){
        $err_msg[] = $vali_head.$lc."行目→4つ目の遷移先ID button_flg_4 の遷移先になるデータがありません。";
      }
*/
      if( ($t['format']=="button" || $t['format']=="button_q") && mb_strlen($t['button_text_1']) > 20 ){
        $err_msg[] = $vali_head.$lc."行目→button_text_1が長すぎです。20字以内にしてください。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && mb_strlen($t['button_text_2']) > 20 ){
        $err_msg[] = $vali_head.$lc."行目→button_text_2が長すぎです。20字以内にしてください。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && mb_strlen($t['button_text_3']) > 20 ){
        $err_msg[] = $vali_head.$lc."行目→button_text_3が長すぎです。20字以内にしてください。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && mb_strlen($t['button_text_4']) > 20 ){
        $err_msg[] = $vali_head.$lc."行目→button_text_4が長すぎです。20字以内にしてください。";
      }
      if($t['format']=="nazo" && is_null($t['nazo_seikai'])){
        $err_msg[] = $vali_head.$lc."行目→formatがnazoですが、正解のテキスト nazo_seikai がありません。";
      }
      if($t['format']=="nazo" && is_null($t['nazo_flg_1'])){
        $err_msg[] = $vali_head.$lc."行目→formatがnazoですが、正解時の遷移先ID nazo_flg_1 がありません。";
      }
      if($t['format']=="nazo" && is_null($t['nazo_flg_2'])){
        $err_msg[] = $vali_head.$lc."行目→formatがnazoですが、誤答時の遷移先ID nazo_flg_2 がありません。";
      }
      if($t['format']=="nazo" && !isScenarioLabel($data,trim($t['nazo_flg_1']))){
        $err_msg[] = $vali_head.$lc."行目→正解時の遷移先ID nazo_flg_1 の遷移先になるデータがありません。";
      }
      if($t['format']=="nazo" && !isScenarioLabel($data,trim($t['nazo_flg_2']))){
        $err_msg[] = $vali_head.$lc."行目→誤答時の遷移先ID nazo_flg_2 の遷移先になるデータがありません。";
      }
      if($t['format']=="stamp" && is_null($t['stamp_package_id'])){
        $err_msg[] = $vali_head.$lc."行目→formatがstampですが、スタンプのパッケージID stamp_package_id がありません。";
      }else if($t['format']=="stamp" && (!is_int((int)$t['stamp_package_id']) || (int)$t['stamp_package_id'] < 1)){
        $err_msg[] = $vali_head.$lc."行目→formatがstampですが、スタンプのパッケージID stamp_package_id は正の整数にしてください。";
      }
      if($t['format']=="stamp" && is_null($t['stamp_id'])){
        $err_msg[] = $vali_head.$lc."行目→formatがstampですが、スタンプID stamp_id がありません。";
      }else if($t['format']=="stamp" && (!is_numeric((int)$t['stamp_id']) || (int)$t['stamp_id'] < 1)){
        $err_msg[] = $vali_head.$lc."行目→formatがstampですが、スタンプのパッケージID stamp_id は正の整数にしてください。";
      }


      if($t['format']=="image" && !file_exists(IMAGE_DIR.$t['img_name']) ){
        $cau_msg[] = $vali_head.$lc."行目→formatがimageですが、画像ファイル ".$t['img_name']."が無いようです。";
      }
      if($t['format']=="image" && !file_exists(IMAGE_DIR."s_".$t['img_name']) ){
        $cau_msg[] = $vali_head.$lc."行目→formatがimageですが、サムネイル画像ファイル s_".$t['img_name']."が無いようです。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_1']) > 36 ){
        $cau_msg[] = $vali_head.$lc."行目→button_text_1が長すぎです。全角12字以内が目安です。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_2']) > 36 ){
        $cau_msg[] = $vali_head.$lc."行目→button_text_2が長すぎです。全角12字以内が目安です。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_3']) > 36 ){
        $cau_msg[] = $vali_head.$lc."行目→button_text_3が長すぎです。全角12字以内が目安です。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_4']) > 36 ){
        $cau_msg[] = $vali_head.$lc."行目→button_text_4が長すぎです。全角12字以内が目安です。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_1']) && !isScenarioLabel($data,trim($t['button_flg_1']))){
        $cau_msg[] = $vali_head.$lc."行目→1つ目の遷移先ID button_flg_1 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_2']) && !isScenarioLabel($data,trim($t['button_flg_2']))){
        $cau_msg[] = $vali_head.$lc."行目→2つ目の遷移先ID button_flg_2 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_3']) && !isScenarioLabel($data,trim($t['button_flg_3']))){
        $cau_msg[] = $vali_head.$lc."行目→3つ目の遷移先ID button_flg_3 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_4']) && !isScenarioLabel($data,trim($t['button_flg_4']))){
        $cau_msg[] = $vali_head.$lc."行目→4つ目の遷移先ID button_flg_4 の遷移先になるデータがありません。";
      }


      $lc++;
    }
  }
}

if(!$err_msg){
  $dbh = dbConnection::getConnection();
  try{
    $dbh->beginTransaction();
    $sql = 'TRUNCATE '. $target_table ;
    $sth = $dbh->prepare($sql);
    $sth->execute();

    foreach($data as $text){
      foreach($text as $t){
        $sql = "insert into ". $target_table .
        " (".$first_column.",no,format,text,file_name,file_property,button_text_1,button_flg_1,button_condition_1,button_text_2,button_flg_2,button_condition_2,button_text_3,button_flg_3,button_condition_3,button_text_4,button_flg_4,button_condition_4,nazo_seikai,nazo_flg_1,nazo_flg_2,stamp_package_id,stamp_id,target_flg_flg,add_flg) ".
        " values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ";
        $sth = $dbh->prepare($sql);
        $sth->execute(array(trim($t[$first_column]),$t['no'],$t['format'],$t['text'],$t['file_name'],$t['file_property'],$t['button_text_1'],trim($t['button_flg_1']),trim($t['button_condition_1']),$t['button_text_2'],trim($t['button_flg_2']),trim($t['button_condition_2']),$t['button_text_3'],trim($t['button_flg_3']),trim($t['button_condition_3']),$t['button_text_4'],trim($t['button_flg_4']),trim($t['button_condition_4']),$t['nazo_seikai'],trim($t['nazo_flg_1']),trim($t['nazo_flg_2']),(int)$t['stamp_package_id'],(int)$t['stamp_id'],trim($t['target_flg']),trim($t['add_flg'])));
      }
    }
    $dbh->commit();
    $msg[] = $filekind_name."データをデータベース登録しました。";
  }catch(PDOException $e){
    error_log('Error:'.$e->getMessage());
    $err_msg[] = $filekind_name."データのデータベース登録に失敗しました。";
    $dbh->rollBack();
  }
}else{
  if($_FILES){
    $err_msg[] = $filekind_name."データにエラーがあるため、データベース登録処理は中止されました。";
  }
}

function isScenarioLabel($data,$label){
  if($data[$label][0]['format']){
    return true;
  }else{
    return false;
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

<form action="https://<?php echo($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
  シナリオCSVファイル：<br />
  <input type="file" name="csvfile" size="30" /><br />
  <input type="submit" value="シナリオcsv アップロード" />
  <input type="hidden" name="filekind" value="senario" />
</form>
<hr>
<form action="https://<?php echo($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
  キーワードメッセージCSVファイル：<br />
  <input type="file" name="csvfile" size="30" /><br />
  <input type="submit" value="キーワードメッセージcsv アップロード" />
  <input type="hidden" name="filekind" value="keyword" />
</form>
<hr>

<ul>
<?php
foreach($msg as $m){
  echo("<li>");
  echo($m);
  echo("</li>");
}
?>
</ul>
<ul style="color:#ff0000">
<?php
foreach($err_msg as $em){
  echo("<li>");
  echo($em);
  echo("</li>");
}
?>
</ul>
<ul style="color:#0094ff">
<?php
if($cau_msg){
  if(!$err_msg){
    echo("notice:これらがあってもシナリオデータは登録されます。");
  }
  foreach($cau_msg as $cm){
    echo("<li>");
    echo($cm);
    echo("</li>");
  }
}
?>
</ul>
