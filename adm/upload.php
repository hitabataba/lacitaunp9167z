<?php
header("Content-Type: text/html; charset=UTF-8");
setlocale(LC_ALL, 'ja_JP.UTF-8');
define('TABLE_NAME_PROGRESS', 'user_progress');
define('TABLE_NAME_SCENARIO', 'scenario_data');



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

  //拡張子を判定
  if (pathinfo($file_name, PATHINFO_EXTENSION) != 'csv') {
    $err_msg[] = 'CSVファイルのみ対応しています。';
  } else {
    //ファイルをdataディレクトリに移動
    if (move_uploaded_file($file_tmp_name, $filepath.$file_name)) {
      //後で削除できるように権限を644に
      chmod($filepath.$file_name, 0644);
      file_put_contents($filepath.$file_name, mb_convert_encoding(file_get_contents($filepath.$file_name), 'UTF-8', 'SJIS'));
      $msg[] = $file_name . "をアップロードしました。";
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
//                  $form_line[$header[$fc]] = '';
                }
              }else{
                  $form_line[$header[$fc]] = null;
//                  $form_line[$header[$fc]] = '';
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

      if(is_null($t['label']) || trim($t['label']) == ""){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがtextですが、textがありません。";
      }
      if($t['format']=="text" && is_null($t['text'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがtextですが、textがありません。";
      }
      if($t['format']=="image" && is_null($t['img_name'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがimgですが、画像ファイル名がありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && is_null($t['button_text_1'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがbuttonですが、1つ目のボタンテキスト button_text_1 がありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && is_null($t['button_flg_1'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがbuttonですが、1つ目のボタンID button_flg_1 がありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && (is_null($t['button_text_2']) xor is_null($t['button_flg_2']))){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→2つ目のボタンテキスト button_text_2 かボタンID button_flg_2 かのどちらかがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && (is_null($t['button_text_3']) xor is_null($t['button_flg_3']))){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→3つ目のボタンテキスト button_text_3 かボタンID button_flg_3 かのどちらかがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && (is_null($t['button_text_4']) xor is_null($t['button_flg_4']))){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→4つ目のボタンテキスト button_text_4 かボタンID button_flg_4 かのどちらかがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_1']) && !isScenarioLabel($data,$t['button_flg_1'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→1つ目の遷移先ID button_flg_1 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_2']) && !isScenarioLabel($data,$t['button_flg_2'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→2つ目の遷移先ID button_flg_2 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_3']) && !isScenarioLabel($data,$t['button_flg_3'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→3つ目の遷移先ID button_flg_3 の遷移先になるデータがありません。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && !is_null($t['button_text_4']) && !isScenarioLabel($data,$t['button_flg_4'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→4つ目の遷移先ID button_flg_4 の遷移先になるデータがありません。";
      }
      if($t['format']=="nazo" && is_null($t['nazo_seikai'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがnazoですが、正解のテキスト nazo_seikai がありません。";
      }
      if($t['format']=="nazo" && is_null($t['nazo_flg_1'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがnazoですが、正解時の遷移先ID nazo_flg_1 がありません。";
      }
      if($t['format']=="nazo" && is_null($t['nazo_flg_2'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがnazoですが、誤答時の遷移先ID nazo_flg_2 がありません。";
      }
      if($t['format']=="nazo" && !isScenarioLabel($data,$t['nazo_flg_1'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→正解時の遷移先ID nazo_flg_1 の遷移先になるデータがありません。";
      }
      if($t['format']=="nazo" && !isScenarioLabel($data,$t['nazo_flg_2'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→誤答時の遷移先ID nazo_flg_2 の遷移先になるデータがありません。";
      }
      if($t['format']=="stamp" && is_null($t['stamp_package_id'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがstampですが、スタンプのパッケージID stamp_package_id がありません。";
      }else if($t['format']=="stamp" && (!is_int((int)$t['stamp_package_id']) || (int)$t['stamp_package_id'] < 1)){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがstampですが、スタンプのパッケージID stamp_package_id は正の整数にしてください。";
      }
      if($t['format']=="stamp" && is_null($t['stamp_id'])){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがstampですが、スタンプID stamp_id がありません。";
      }else if($t['format']=="stamp" && (!is_numeric((int)$t['stamp_id']) || (int)$t['stamp_id'] < 1)){
        $err_msg[] = "label:".$t['label']." ".$lc."行目→formatがstampですが、スタンプのパッケージID stamp_id は正の整数にしてください。";
      }


      if($t['format']=="image" && !file_exists(IMAGE_DIR.$t['img_name']) ){
        $cau_msg[] = "label:".$t['label']." ".$lc."行目→formatがimageですが、画像ファイル ".$t['img_name']."が無いようです。";
      }
      if($t['format']=="image" && !file_exists(IMAGE_DIR."s_".$t['img_name']) ){
        $cau_msg[] = "label:".$t['label']." ".$lc."行目→formatがimageですが、サムネイル画像ファイル s_".$t['img_name']."が無いようです。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_1']) > 36 ){
        $cau_msg[] = "label:".$t['label']." ".$lc."行目→button_text_1が長すぎです。全角12字以内が目安です。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_2']) > 36 ){
        $cau_msg[] = "label:".$t['label']." ".$lc."行目→button_text_2が長すぎです。全角12字以内が目安です。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_3']) > 36 ){
        $cau_msg[] = "label:".$t['label']." ".$lc."行目→button_text_3が長すぎです。全角12字以内が目安です。";
      }
      if( ($t['format']=="button" || $t['format']=="button_q") && strlen($t['button_text_4']) > 36 ){
        $cau_msg[] = "label:".$t['label']." ".$lc."行目→button_text_4が長すぎです。全角12字以内が目安です。";
      }

      $lc++;
    }
  }
}

if(!$err_msg){
  $dbh = dbConnection::getConnection();
  try{
    $dbh->beginTransaction();
    $sql = 'TRUNCATE '. TABLE_NAME_SCENARIO ;
    $sth = $dbh->prepare($sql);
    $sth->execute();

    foreach($data as $text){
      foreach($text as $t){
        $sql = "insert into ". TABLE_NAME_SCENARIO .
        " (label,no,format,text,img_name,button_text_1,button_flg_1,button_text_2,button_flg_2,button_text_3,button_flg_3,button_text_4,button_flg_4,nazo_seikai,nazo_flg_1,nazo_flg_2,stamp_package_id,stamp_id) ".
        " values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ";
        $sth = $dbh->prepare($sql);
        $sth->execute(array($t['label'],$t['no'],$t['format'],$t['text'],$t['img_name'],$t['button_text_1'],$t['button_flg_1'],$t['button_text_2'],$t['button_flg_2'],$t['button_text_3'],$t['button_flg_3'],$t['button_text_4'],$t['button_flg_4'],$t['nazo_seikai'],$t['nazo_flg_1'],$t['nazo_flg_2'],(int)$t['stamp_package_id'],(int)$t['stamp_id']));
      }
    }
    $dbh->commit();
    $msg[] = "シナリオをデータベース登録しました。";
  }catch(PDOException $e){
    error_log('Error:'.$e->getMessage());
    $err_msg[] = "シナリオのデータベース登録に失敗しました。";
    $dbh->rollBack();
  }
}else{
  if($_FILES){
    $err_msg[] = "シナリオデータにエラーがあるため、データベース登録処理は中止されました。";
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

<form action="upload.php" method="post" enctype="multipart/form-data">
  CSVファイル：<br />
  <input type="file" name="csvfile" size="30" /><br />
  <input type="submit" value="アップロード" />
</form>

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
