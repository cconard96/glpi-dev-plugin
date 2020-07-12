<?php

include ('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8", true);
header("Cache-Control: max-age=3600"); // HTTP/1.1
Session::checkLoginUser();

$from = $_GET['from'];
$value = $_GET['value'];
$to = $_GET['to'];

if ($from === $to) {
   echo json_encode($value);
   return;
}
$fk = null;

switch ($from) {
   case 'fk':
      $fk = $value;
      break;
   case 'class_name':
      $fk = getForeignKeyFieldForItemType($value);
      break;
   case 'table':
      $fk = getForeignKeyFieldForTable($value);
      break;
}

$result = null;

switch ($to) {
   case 'fk':
      $result = $fk;
      break;
   case 'class_name':
      $result = getItemtypeForForeignKeyField($fk);
      break;
   case 'table':
      $result = getTableNameForForeignKeyField($fk);
      break;
}

echo json_encode($result);