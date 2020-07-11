<?php

include ('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevThemedesigner');
if (!Session::haveRight("config", UPDATE)) {
   return false;
}
echo '<div id=\'themedesigner-designer\'></div>';
echo '<div><pre id=\'themedesigner-results\' style="white-space: pre-line"></pre></div>';
echo '<script>$(document).ready(function() {loadThemeDesigner();});</script>';

Html::footer();