<?php

include('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8", true);
Html::header_nocache();
Session::checkLoginUser();

/** @var CommonDBTM $itemtype */
$itemtype = html_entity_decode($_GET['class']);
echo json_encode([
    'name' => [$itemtype::getTypeName(1), $itemtype::getTypeName(Session::getPluralNumber())],
    'icon' => $itemtype::getIcon(),
    'searchoptions' => PluginDevClassviewer::getSearchOptions($itemtype),
    'missing_searchoptions' => PluginDevClassviewer::getMissingSearchOptions($itemtype),
    'unlinked_searchoptions' => PluginDevClassviewer::getUnlinkedSearchOptions($itemtype),
], JSON_FORCE_OBJECT);
