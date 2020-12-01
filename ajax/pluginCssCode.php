<?php

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], 'pluginCssCode.php')) {
   $AJAX_INCLUDE = 1;
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

// Helper to check required parameters
$checkParams = static function($required) {
   foreach ($required as $param) {
      if (!isset($_POST[$param])) {
         Toolbox::logError("Missing $param parameter");
         http_response_code(400);
         die();
      }
   }
};

$checkParams(['plugin', 'stylesheet']);
$css_path = Plugin::getPhpDir($_POST['plugin']) . '/css/' . $_POST['stylesheet'];
if ($css_path !== realpath($css_path)) {
   // Prevent directory traversal
   http_response_code(400);
   die();
}
$custom_css_code = file_get_contents($css_path);

$editor_mode = strtolower(substr($css_path, -4)) === '.css' ? 'text/css' : (strtolower(substr($css_path, -5)) === '.scss' ? 'text/x-scss' : 'text/text');

$rand = mt_rand();

echo '<textarea id="plugin_css_code_'. $rand . '" name="plugin_css_code">';
echo Html::entities_deep($custom_css_code);
echo '</textarea>';

$editor_options = [
   'mode'               => $editor_mode,
   'lineNumbers'        => true,

   // Autocomplete with CTRL+SPACE
   'extraKeys'          => [
      'Ctrl-Space' => 'autocomplete',
   ],

   // Code folding configuration
   'foldGutter' => true,
   'gutters'    => [
      'CodeMirror-linenumbers',
      'CodeMirror-foldgutter'
   ],
];

echo Html::scriptBlock('
   $(function() {
      var textarea = document.getElementById("plugin_css_code_' . $rand . '");
      var editor = CodeMirror.fromTextArea(textarea, ' . json_encode($editor_options) . ');
      // Fix bad display of gutter (see https://github.com/codemirror/CodeMirror/issues/3098 )
      setTimeout(function () {editor.refresh();}, 10);
   });
');
