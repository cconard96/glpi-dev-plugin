<?php

class PluginDevAudit extends CommonGLPI
{

    public static function getTypeName($nb = 0)
    {
        return _x('tool', 'Audit', 'dev');
    }

    public function showForm()
    {
        global $DB;

        $loadedClasses = get_declared_classes();
        $glpiClasses = [];

        foreach ($loadedClasses as $class) {
            if (is_subclass_of($class, 'CommonGLPI') && !(new ReflectionClass($class))->isAbstract()) {
                $glpiClasses[] = $class;
            }
        }
        sort($glpiClasses);

        $missing_searchoptions = [];
        $missing_searchoptions_total = 0;
        $unlinked_searchoptions = [];
        $unlinked_searchoptions_total = 0;

        $total_loaded_classes = count($loadedClasses);
        $total_loaded_glpi_classes = count($glpiClasses);

        foreach ($glpiClasses as $class) {
            if ($DB->tableExists(getTableForItemType($class)) && is_subclass_of($class, 'CommonDBTM')) {
                $missing = PluginDevClassviewer::getMissingSearchOptions($class);
                $missing_searchoptions_total += count($missing);
                $missing_searchoptions[$class] = $missing;

                $unlinked = PluginDevClassviewer::getUnlinkedSearchOptions($class);
                $unlinked_searchoptions_total += count($unlinked);
                $unlinked_searchoptions[$class] = $unlinked;
            }
        }

        echo "<div id='devaudit-container'>";
        echo "<div style='font-size: 1.2em; margin-bottom: 2em'>{$total_loaded_classes} Total Loaded Classes<br>";
        echo "{$total_loaded_glpi_classes} Total Loaded GLPI Classes<br>";
        echo "{$missing_searchoptions_total} Total Missing Search Options<br>";
        echo "{$unlinked_searchoptions_total} Total Unlinked Search Options</div>";
        echo "<div>";
        echo Html::getCheckbox([
                'title' => 'Hide Classes With No Issues',
                'name' => 'hide_ok_classes',
                'checked' => 1
            ]) . '&nbsp;Hide Classes With No Issues';
        echo "</div>";
        echo "<div style='margin-top: 5px'><ul>";
        foreach ($glpiClasses as $class) {
            $warn_count = isset($missing_searchoptions[$class]) ? count($missing_searchoptions[$class]) : 0;
            $warn_count += isset($unlinked_searchoptions[$class]) ? count($unlinked_searchoptions[$class]) : 0;
            $status_msg = 'No Issues';
            if ($warn_count) {
                $status_msg = "$warn_count Warnings";
            }
            echo "<li><details data-issue-count='$warn_count'>";
            $button_group = "<button title='View Item Type' class='classview-link-btn' data-format='class_name' data-value='$class'><i class='fas fa-sitemap'></i></button>";
            if ($DB->tableExists(getTableForItemType($class)) && is_subclass_of($class, 'CommonDBTM')) {
                $table = getTableForItemType($class);
                $button_group .= "<button title='View Table' class='dbschemaview-link-btn' data-format='table' data-value='$table'><i class='fas fa-table'></i></button>";
            }
            echo "<summary>$class ($status_msg) $button_group</summary>";
            if ($missing_searchoptions[$class]) {
                echo "<ul class='warning'>";
                foreach ($missing_searchoptions[$class] as $opt) {
                    echo "<li>The field $opt does not have a matching search option</li>";
                }
                echo "</ul>";
            }
            if ($unlinked_searchoptions[$class]) {
                echo "<ul class='warning'>";
                foreach ($unlinked_searchoptions[$class] as $opt) {
                    echo "<li>The search option for the field $opt does not have a matching DB field</li>";
                }
                echo "</ul>";
            }
            echo "</details></li>";
        }
        echo "</ul></div>";
        echo "</div>";
    }
}
