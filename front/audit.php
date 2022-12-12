<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevAudit');

$audit = new PluginDevAudit();
$audit->showForm();

Html::footer();
