<?php

include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

$plugin_creator = new PluginDevPlugincreator();
if (isset($_POST["init"])) {
    PluginDevPlugincreator::initPlugin($_POST);
    Session::addMessageAfterRedirect('Initialized plugin');
    Html::back();

} else {
    Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu', 'PluginDevPlugincreator');
    $plugin_creator->showCreateForm();

    Html::footer();
}
