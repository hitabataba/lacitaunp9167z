<?php
header("Content-Type: text/html; charset=UTF-8");
setlocale(LC_ALL, 'ja_JP.UTF-8');


define('IMAGE_DIR', __DIR__.'/../img/');
define('AUDIO_DIR', __DIR__.'/../audio/');

$err_msg = array();
$cau_msg = array();
$msg = array();

if (is_uploaded_file($_FILES["imgfile"]["tmp_name"])) {
  $file_tmp_name = $_FILES["imgfile"]["tmp_name"];
  $file_name = $_FILES["imgfile"]["name"];

  //拡張子を判定
  if (!checkImageFile($file_name)) {
    $err_msg[] = '画像ファイルのみ対応しています。';
  } else {
    //ファイルをdataディレクトリに移動
    if (move_uploaded_file($file_tmp_name, IMAGE_DIR.$file_name)) {
      //後で削除できるように権限を644に
      chmod($filepath.$file_name, 0644);

      $msg[] = $file_name . "をアップロードしました。";

    } else {
      $err_msg[] = "ファイルをアップロードできません。";
    }
  }
} else {
  $err_msg[] = "ファイルを選択してください。";
}

function checkImageFile($file_name){
  if (pathinfo($file_name, PATHINFO_EXTENSION) == 'jpg' 
    || pathinfo($file_name, PATHINFO_EXTENSION) == 'jpeg'
    || pathinfo($file_name, PATHINFO_EXTENSION) == 'png'
    || pathinfo($file_name, PATHINFO_EXTENSION) == 'gif') {
    return true;
  }else{
    return false;
  }
}


?>

<form action="<?php echo($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
  画像ファイル(jpg/png/gif)：<br />
  <input type="file" name="imgfile" size="30" /><br />
  <input type="submit" value="画像アップロード" />
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
  foreach($cau_msg as $cm){
    echo("<li>");
    echo($cm);
    echo("</li>");
  }
}
?>
</ul>
