<?php
setlocale(LC_ALL, 'ja_JP.UTF-8');

// Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';


// テーブル名を定義
define('TABLE_NAME_PROGRESS', 'user_progress');
define('TABLE_NAME_SCENARIO', 'scenario_data');
define('TABLE_NAME_MESSAGE', 'message_data');
define('TABLE_NAME_SERIALLIST', 'seriallist');
define('TABLE_NAME_QUESTIONNAIRE', 'user_questionnaire');

//画像格納ディレクトリ
define('IMAGE_DIR', 'https://'.$_SERVER['HTTP_HOST'].'/img/');
define('AUDIO_DIR', 'https://'.$_SERVER['HTTP_HOST'].'/audio/');


// アクセストークンを使いCurlHTTPClientをインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
// LINE Messaging APIがリクエストに付与した署名を取得
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

// 署名が正当かチェック。正当であればリクエストをパースし配列へ
// 不正であれば例外の内容を出力
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}

//シナリオデータ
$text = array();

$messages = array();
// 配列に格納された各イベントをループで処理

if(!$events){
  error_log('No Events Access');
  exit();
}

foreach ($events as $event) {
//error_log(var_export($event,true));

  $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
//    error_log('userid:'. $event->getUserId());

//ユーザー確認
  $progress = getProgressDataByUserId($event->getUserId());

  if($progress === PDO::PARAM_NULL) {
    $progress= array(); //進捗を初期状態に
    $progress[0] = 'WELCOME'; //進捗を初期状態に

    registerUser($event->getUserId(), json_encode($progress));
//    foreach($text['TEXT00'] as $val){
//      $messages[] = $val;
//    }
  }

//初期登録時
  if (($event instanceof \LINE\LINEBot\Event\FollowEvent)) {
    $progress[0] ='WELCOME'; //進捗を初期状態に

    updateUser($event->getUserId(), json_encode($progress));

    $text = getSenarioRows($text,$progress[0]);
    foreach($text[$progress[0]] as $val){
      $messages[] = $val;
    }
//ビーコンイベント
/*
  }else if (($event instanceof \LINE\LINEBot\Event\BeaconDetectionEvent)) {
error_log("ビーコン");

    if($progress[0] == "TXT06_06" || $progress[0] == "TXT06_07X"){
      $step = "TXT06_beacon";
      $text = getSenarioRows($text,$step);
      if($text[$step]){
error_log($progress[0]);
error_log($step);
        $progress[0] = $step;
error_log($progress[0]);
        updateUser($event->getUserId(), json_encode($progress));
        foreach($text[$progress[0]] as $val){
          $messages[] = $val;
        }
      }
      error_log('Log--Get Beacon Message');
    }
*/
//選択肢入力
  }else if (($event instanceof \LINE\LINEBot\Event\PostbackEvent)) {
    $steps = explode('$', $event->getPostbackData(), 3);
    $past_step = $steps[0];
    $step = $steps[1];
    $q_rec = ($steps[2] != "") ? $steps[2] : false;

    if($q_rec != false){
      updateUserQuestionnaire($event->getUserId(), $past_step, $q_rec);
    }
    error_log('Log--past_step;'.$past_step.', $next_step:'.$step);

    switch($past_step){
    case 'ResetYes':
      $progress[0] ="WELCOME"; //進捗を初期状態に

      updateUser($event->getUserId(), json_encode($progress));

      $text = getSenarioRows($text,$progress[0]);
      foreach($text[$progress[0]] as $val){
        $messages[] = $val;
      }
      break;

    default:
      if($progress[0] == $past_step){
        $text = getSenarioRows($text,$step);
        if($text[$step]){
          $progress[0] = $step;
          updateUser($event->getUserId(), json_encode($progress));
          foreach($text[$progress[0]] as $val){
            $messages[] = $val;
          }

//該当するシナリオデータが無い場合
        }else{
          error_log('Log--DELETE SenarioData - paststep'.$past_step.', nextstep:'.$step);
          $step = "WELCOME";
          $text = getSenarioRows($text,$step);
          $progress[0] = $step;
          updateUser($event->getUserId(), json_encode($progress));
          foreach($text[$progress[0]] as $val){
            $messages[] = $val;
          }
        }
//クリアLog取得
/*
        if($step == "TXT05_04"){
          error_log('Log--May Clear');
        }
*/
      }
      break;

    }
//自由記入があった場合
  }else if($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
//自由メッセージ応答チェック
    if($text = getMessageRows($text,$event->getText())){
        foreach($text as $val){
          $messages[] = $val;
        }
    }else if($event->getText()=='受信' || $event->getText()=='【受信】' || $event->getText()=='別の未来' || $event->getText()=='【別の未来】'){
      $progress[0] = "TXT10_99";
      updateUser($event->getUserId(), json_encode($progress));

      $text = getSenarioRows($text,$progress[0]);
      foreach($text[$progress[0]] as $val){
        $messages[] = $val;
      }
    }else{
      $nazoline_check = false;

      $text = getSenarioRows($text,$progress[0]);
//該当するシナリオデータが無い場合
      if(!$text[$progress[0]]){
        error_log('Log--DELETE SenarioData - paststep'.$step.', nextstep:'.$progress[0]);
        $step = "WELCOME";
        $text = getSenarioRows($text,$step);
        $progress[0] = $step;
        updateUser($event->getUserId(), json_encode($progress));
        foreach($text[$progress[0]] as $val){
          $messages[] = $val;
        }
      }else{
        foreach($text[$progress[0]] as $l){
          if($l['format']=='nazo'){
            $correct_check = false;
            $corrects = explode(' ',$l['nazo_seikai']);
            foreach($corrects as $correct){
              if($event->getText()==$correct){
                $correct_check = true;
              }
            }
            if($correct_check){
              $progress[0] = $l['nazo_flg_1'];
            }else{
              $progress[0] = $l['nazo_flg_2'];
            }
            updateUser($event->getUserId(), json_encode($progress));
            $text = getSenarioRows($text,$progress[0]);
            foreach($text[$progress[0]] as $val){
              $messages[] = $val;
            }
            $nazoline_check = true;
            break;

          }else if($l['format']=='branch'){
            $correct_check = 0;
            $progress[0] = $l['button_flg_4'];  //初期値は誤答
            if($l['button_text_1']){$corrects_ary[1] = explode(' ',$l['button_text_1']);}
            if($l['button_text_2']){$corrects_ary[2] = explode(' ',$l['button_text_2']);}
            if($l['button_text_3']){$corrects_ary[3] = explode(' ',$l['button_text_3']);}
            foreach($corrects_ary as $key => $corrects){
              foreach($corrects as $correct){
                if($event->getText()==$correct){
                  $progress[0] = $l['button_flg_'.$key];
                }
              }
            }
            updateUser($event->getUserId(), json_encode($progress));
            $text = getSenarioRows($text,$progress[0]);
            foreach($text[$progress[0]] as $val){
              $messages[] = $val;
            }
            $nazoline_check = true;
            break;
          }
        }
        if(!$nazoline_check){
          error_log('Log--No nazo jump destination');
        }
      }
/*
      switch($progress[0]){
      case 'TEXT01':
      case 'TEXT02':
        if($event->getText()=='シンギュラリティ'){
          $progress[0] ='TEXT03';
          updateUser($event->getUserId(), json_encode($progress));
          error_log('Log--March Succeed');
          foreach($text['TEXT03'] as $val){
            $messages[] = $val;
          }
        }else{
          $progress[0] ='TEXT02';
          updateUser($event->getUserId(), json_encode($progress));
          error_log('Log--March failed:'.$event->getText());
          foreach($text['TEXT02'] as $val){
            $messages[] = $val;
          }
        }
        break;
      default:
        break;
      }
*/
    }
  }
  if($messages){
    break;
  }

}

//メッセージの送信
if($messages){
  replyMultiMessage($bot, $event->getReplyToken(), $messages, $profile);
}





// テキストを返信。引数はLINEBot、返信先、テキスト
function replyTextMessage($bot, $replyToken, $text) {
  // 返信を行いレスポンスを取得
  // TextMessageBuilderの引数はテキスト
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
  // レスポンスが異常な場合
  if (!$response->isSucceeded()) {
    // エラー内容を出力
    error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

// 画像を返信。引数はLINEBot、返信先、画像URL、サムネイルURL
function replyImageMessage($bot, $replyToken, $originalImageUrl, $previewImageUrl) {
  // ImageMessageBuilderの引数は画像URL、サムネイルURL
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

// 複数のメッセージをまとめて返信。引数はLINEBot、
// 返信先、メッセージ(可変長引数)
//function replyMultiMessage($bot, $replyToken, ...$msgs) {
function replyMultiMessage($bot, $replyToken, $msgs, $profile) {
  // MultiMessageBuilderをインスタンス化
  $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
  // ビルダーにメッセージを全て追加
  foreach($msgs as $json_msg) {
//    $value = json_decode($json_msg,true);
    $value = $json_msg;
    $msg = null;

    switch($value['format']){
    case "text":
    case "nazo":
    case "branch":
      $value['text'] = str_replace('[player_name]', $profile['displayName'], $value['text']);
      $msg = new LINE\LINEBot\MessageBuilder\TextMessageBuilder($value['text']);
      break;
    case "stamp":
      $msg = new LINE\LINEBot\MessageBuilder\StickerMessageBuilder($value['stamp_package_id'],$value['stamp_id']);
      break;
    case "image":
      $value['thumimg_name']= 's_'.$value['img_name'];
      $msg = new LINE\LINEBot\MessageBuilder\ImageMessageBuilder(IMAGE_DIR.$value['img_name'],IMAGE_DIR.$value['thumimg_name']);
      break;
    case "audio":
      $msg = new LINE\LINEBot\MessageBuilder\AudioMessageBuilder(AUDIO_DIR.$value['img_name'],$value['stamp_id']);
      break;
    case "button":
    case "button_q":
      $postback = array();
      if(trim($value['button_text_1']) && trim($value['button_flg_1'])){
        if($value['format'] == "button_q"){$q_txt = $value['button_text_1'];}else{$q_txt = "";}
        $value['button_text_1'] = str_replace('[player_name]', $profile['displayName'], $value['button_text_1']);
        $postback[] = new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($value['button_text_1'],$value['label']."$".$value['button_flg_1']."$".$q_txt);
      }
      if(trim($value['button_text_2']) && trim($value['button_flg_2'])){
        if($value['format'] == "button_q"){$q_txt = $value['button_text_2'];}else{$q_txt = "";}
        $value['button_text_2'] = str_replace('[player_name]', $profile['displayName'], $value['button_text_2']);
        $postback[] = new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($value['button_text_2'],$value['label']."$".$value['button_flg_2']."$".$q_txt);
      }
      if(trim($value['button_text_3']) && trim($value['button_flg_3'])){
        if($value['format'] == "button_q"){$q_txt = $value['button_text_3'];}else{$q_txt = "";}
        $value['button_text_3'] = str_replace('[player_name]', $profile['displayName'], $value['button_text_3']);
        $postback[] = new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($value['button_text_3'],$value['label']."$".$value['button_flg_3']."$".$q_txt);
      }
      if(trim($value['button_text_4']) && trim($value['button_flg_4'])){
        if($value['format'] == "button_q"){$q_txt = $value['button_text_4'];}else{$q_txt = "";}
        $value['button_text_4'] = str_replace('[player_name]', $profile['displayName'], $value['button_text_4']);
        $postback[] = new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($value['button_text_4'],$value['label']."$".$value['button_flg_4']."$".$q_txt);
      }
//      foreach($value['button'] as $key => $val) {
//        $postback[] = new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($key,$val);
//      }
      if(!isset($value['text'])){
        $value['text'] = '_';
      }
      if(!isset($value['imagefile'])){
        $value['imagefile'] = null;
      }else{
        $value['imagefile'] = IMAGE_DIR.$value['imagefile'];
      }
      $value['text'] = str_replace('[player_name]', $profile['displayName'], $value['text']);
      $msg = new LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
            $value['text'],new LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder (null, $value['text'], $value['imagefile'], $postback)
            );
      break;
    case "carousel":
error_log($value['title']);
      $columns = []; // カルーセル型カラムを5つ追加する配列
      foreach ($value['column'] as $column) {
        // カルーセルに付与するボタンを作る
        $action = array();
        $action[] = new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($column['text'],$column['postback']);
        // カルーセルのカラムを作成する
        $column = new LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("a", "a", IMAGE_DIR.$value['imagefile'], $action);
        $columns[] = $column;
      }
      $msg = new LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
            $value['title'],new CarouselTemplateBuilder($columns)
            );
      break;
    default:
      break;
    }
    if($msg){
      $builder->add($msg);
    }
  }
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}


// Buttonsテンプレートを返信。引数はLINEBot、返信先、代替テキスト、
// 画像URL、タイトル、本文、アクション(可変長引数)
function replyButtonsTemplate($bot, $replyToken, $alternativeText, $imageUrl, $title, $text, ...$actions) {
  // アクションを格納する配列
  $actionArray = array();
  // アクションを全て追加
  foreach($actions as $value) {
    array_push($actionArray, $value);
  }
  // TemplateMessageBuilderの引数は代替テキスト、ButtonTemplateBuilder
  $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
    $alternativeText,
    // ButtonTemplateBuilderの引数はタイトル、本文、
    // 画像URL、アクションの配列
    new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder ($title, $text, $imageUrl, $actionArray)
  );
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

// ユーザーをデータベースに登録する
function registerUser($userId,$progress) {
  $dbh = dbConnection::getConnection();
  $sql = 'insert into '. TABLE_NAME_PROGRESS .' (userid,progress) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId,$progress));
}

// ユーザーの情報を更新
function updateUser($userId, $progress) {
  $dbh = dbConnection::getConnection();
  $sql = 'update ' . TABLE_NAME_PROGRESS . ' set progress = ? , update_timestamp = now() where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($progress, $userId));
}

// アンケート情報を更新
function updateUserQuestionnaire($userId, $quest, $answer) {
  $answer_data = array();

  $dbh = dbConnection::getConnection();

  $sql = 'select answer from ' . TABLE_NAME_QUESTIONNAIRE . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId));
  // レコードが存在しなければ新規登録
  if (!($row = $sth->fetch())) {
    $answer_data = array($quest => $answer);
    $sql = 'insert into '. TABLE_NAME_QUESTIONNAIRE .' (userid,answer) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?) ';
    $sth = $dbh->prepare($sql);
    $sth->execute(array($userId,json_encode($answer_data)));

  }else{
    $answer_data = json_decode($row['answer'],true);
    $answer_data[$quest] = $answer;

    $sql = 'update ' . TABLE_NAME_QUESTIONNAIRE . ' set answer = ? , update_timestamp = now() where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
    $sth->execute(array(json_encode($answer_data), $userId));
  }
}



// ユーザーIDを元にデータベースから進捗情報を取得
function getProgressDataByUserId($userId) {
  $dbh = dbConnection::getConnection();
  $sql = 'select progress from ' . TABLE_NAME_PROGRESS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId));
  // レコードが存在しなければNULL
  if (!($row = $sth->fetch())) {
    return PDO::PARAM_NULL;
  } else {
//     進捗状況を返す
    return json_decode($row['progress']);
  }
}

//シナリオデータ取得
function getSenarioRows($text,$label) {
  $dbh = dbConnection::getConnection();
  $sql = 'select * from ' . TABLE_NAME_SCENARIO . ' where label = ? order by no';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($label));
  while($row = $sth->fetch()){
    $text[$label][$row['no']] = $row;
  }
  return $text;
}

//メッセージ（ヒント）データ取得
function getMessageRows($text,$keyword) {
  $dbh = dbConnection::getConnection();
  $sql = 'select * from ' . TABLE_NAME_MESSAGE . ' where keyword = ? order by no';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($keyword));

  $text = array();
  while($row = $sth->fetch()){
    $text[$row['no']] = $row;
  }
  if(!$text){
    return false;
  }else{
    return $text;
  }
}

// ユーザーIDを元にデータベースからシリアル登録状況を取得
function getSerialExistProgressDataByUserId($userId) {
  $dbh = dbConnection::getConnection();
  $sql = 'select progress from ' . TABLE_NAME_PROGRESS . ' where serial is not null and ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId));
  // レコードが存在しなければfalse
  if (!($row = $sth->fetch())) {
    return false;
  } else {
    return true;
  }
}

// シリアル番号のチェック＆登録
function checkSerialList($userId,$serialno) {
  $dbh = dbConnection::getConnection();
  $sql = 'select serial,check_flg from ' . TABLE_NAME_SERIALLIST . ' where serial = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array(strtolower(mb_convert_kana($serialno,"a"))));
  if ($row = $sth->fetch()) {
    if($row['check_flg']==false){
      error_log('Log--check serial: ' . $row[serial]);


      try{
        $dbh->beginTransaction();

        $sql = 'update ' . TABLE_NAME_SERIALLIST . ' set check_flg = true ,check_time = current_timestamp where serial = ?';
        $sth = $dbh->prepare($sql);
        $sth->execute(array(strtolower(mb_convert_kana($serialno,"a"))));

        $sql = 'update ' . TABLE_NAME_PROGRESS . ' set serial = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
        $sth = $dbh->prepare($sql);
        $sth->execute(array(strtolower(mb_convert_kana($serialno,"a")),$userId));
        $dbh->commit();
        error_log('Log--Certification :input- ' . $serialno);

        return 1;
;     }catch(PDOException $e){
        error_log('Error:'.$e->getMessage());
        $dbh->rollBack();
        return -2;
      }

  //すでに使われているシリアルの場合
    }else{
      error_log('Log--error - serial duplicate :input- ' . $serialno);
      return -1;
    }
  //該当するシリアルがない場合
  }else{
      error_log('Log--error - serial is not exist:input- ' . $serialno);
      return 0;
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