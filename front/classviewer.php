<?php

include ('../../../inc/includes.php');

Session::checkLoginUser();

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevClassviewer');

$loadedClasses = get_declared_classes();
$glpiClasses = [];

foreach ($loadedClasses as $class) {
   if (is_subclass_of($class, 'CommonGLPI')) {
      $glpiClasses[] = $class;
   }
}
sort($glpiClasses);

echo "<div id='classview-container'>";
echo "<div class='sidebar'>";
echo "<input name='search'/>";
echo "<ul>";
foreach ($glpiClasses as $class) {
   echo "<li><a href='#'>$class</a></li>";
}
echo "</ul>";
echo "</div>";

echo "<div class='info-container'>";

echo "</div>";
echo "</div>";

Html::footer();