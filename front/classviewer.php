<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevClassviewer');

$classviewer = new PluginDevClassviewer();
$classviewer->showForm();

Html::footer();
