<?php

include ('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8", true);
Html::header_nocache();
Session::checkLoginUser();

if ($_GET['action'] === 'get_plugins') {
   echo json_encode(PluginDevPlugincreator::getEditablePlugins());
}
