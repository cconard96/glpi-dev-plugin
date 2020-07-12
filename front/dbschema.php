<?php

include ('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevDbschema');

$tables = PluginDevDbschema::getTables();

sort($tables);

echo "<div id='dbschemaview-container'>";
echo "<div class='sidebar'>";
echo "<input name='search'/>";
echo "<ul>";
foreach ($tables as $table) {
   echo "<li><a href='#'>$table</a></li>";
}
echo "</ul>";
echo "</div>";

echo "<div class='info-container'>";

echo "</div>";
echo "</div>";

Html::footer();