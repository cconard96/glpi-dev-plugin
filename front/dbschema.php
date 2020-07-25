<?php

include ('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevDbschema');

$dbschema = new PluginDevDbschema();
$dbschema->showForm();

Html::footer();