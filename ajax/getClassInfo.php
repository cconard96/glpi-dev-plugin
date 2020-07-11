<?php

include ('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8", true);
Html::header_nocache();
Session::checkLoginUser();

echo json_encode([
   'searchoptions' => PluginDevClassviewer::getSearchOptions($_GET['class'])
], JSON_FORCE_OBJECT);