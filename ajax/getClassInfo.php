<?php

include ('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8", true);
Html::header_nocache();
Session::checkLoginUser();

/** @var CommonDBTM $itemtype */
$itemtype = $_GET['class'];
echo json_encode([
   'name'            => [$itemtype::getTypeName(1), $itemtype::getTypeName(Session::getPluralNumber())],
   'icon'            => $itemtype::getIcon(),
   'searchoptions'   => PluginDevClassviewer::getSearchOptions($itemtype)
], JSON_FORCE_OBJECT);