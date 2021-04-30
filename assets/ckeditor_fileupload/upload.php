<?php
if(isset($_FILES['upload']['name']))
{
  $file=$_FILES['upload']['name'];
  $filetmp=$_FILES['upload']['tmp_name'];
  $prj_name_tmp = $_SERVER['PHP_SELF'];
  $prj_name = (explode('/', $prj_name_tmp));
  $prj = '/' . $prj_name[1];
  move_uploaded_file($filetmp,'upload/'.$file);
  $function_number=$_GET['CKEditorFuncNum'];
    if(isset($_SERVER['HTTPS'])){
       $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
       $protocol = 'http';
    }

    $url = $protocol."://".$_SERVER['SERVER_NAME'] . $prj . "/assets/ckeditor_fileupload/upload/".$_FILES['upload']['name'];
  $message='';
  echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($function_number, '$url', '$message');</script>";    
}
?>