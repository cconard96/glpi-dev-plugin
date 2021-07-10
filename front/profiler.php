<?php

include ('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevThemedesigner');
if (!Session::haveRight("config", UPDATE)) {
   return false;
}

PluginDevProfiler::showDashboard($_GET['log'] ?? null, $_GET['session'] ?? null);

Html::footer();
