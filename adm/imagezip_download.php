<?php
set_time_limit(0);

define('IMAGE_DIR', __DIR__.'/../img/');
define('AUDIO_DIR', __DIR__.'/../audio/');

define('TMP_DIR', __DIR__.'/../tmp/');


$zip_name = 'imagefiles.zip';
$zip_tmp_dir = TMP_DIR;

$zip_obj = new ZipArchive();

$result = $zip_obj -> open($zip_tmp_dir.$zip_name, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
if(!$result){
    echo 'error code : '.$result;
    exit();
}

foreach(glob(IMAGE_DIR.'*') as $file){
    if(is_file($file)){
      $zip_obj -> addFile($file, basename($file));
    }
}

$zip_obj -> close();

header('Content-Type: application/force-download;');
header('Content-Length: '.filesize($zip_tmp_dir.$zip_name));
header('Content-Disposition: attachment; filename="'.$zip_name.'"');
readfile($zip_tmp_dir.$zip_name);

//unlink($zip_tmp_dir.$zip_name);
?>