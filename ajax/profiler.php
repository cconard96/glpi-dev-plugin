<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

if ($_SESSION['glpi_use_mode'] !== Session::DEBUG_MODE) {
   return;
}
Session::checkRight(Config::$rightname, UPDATE);

PluginDevProfiler::showDebugTab();
