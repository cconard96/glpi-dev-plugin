<?php

include('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8", true);
Html::header_nocache();
Session::checkLoginUser();

echo json_encode(PluginDevDbschema::getTableSchema($_GET['table']), JSON_FORCE_OBJECT);
