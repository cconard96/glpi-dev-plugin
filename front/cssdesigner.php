<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu');

PluginDevCssdesigner::showCSSDesignerForm($_GET['plugin'] ?? 'dev', $_GET['stylesheet'] ?? 'dev.scss');

Html::footer();