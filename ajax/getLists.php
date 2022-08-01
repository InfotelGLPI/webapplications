<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

Toolbox::logInfo($_POST);
if(isset($_POST['type'])
   && isset($_POST['value'])
&& $_POST['value'] > 0) {

    switch ($_POST['type']) {
        case PluginWebapplicationsDashboard::getType():
            //echo $_SESSION['plugin_webapplications_loaded_appliances_id'];
            $_SESSION['plugin_webapplications_loaded_appliances_id'] = $_POST['value'];
            $dashboard = new PluginWebapplicationsDashboard();
            $dashboard->display(['id' => 1, 'appliances_id' => $_POST['value']]);
            break;
//        case PluginWebapplicationsDashboardEcosystem::getType():
//            PluginWebapplicationsDashboardEcosystem::showLists($_POST['value']);
//            break;
//        case PluginWebapplicationsDashboardProcess::getType():
//            PluginWebapplicationsDashboardProcess::showLists($_POST['value']);
//            break;
//        case PluginWebapplicationsDashboardApplication::getType():
//            PluginWebapplicationsDashboardApplication::showLists($_POST['value']);
//            break;
//        case PluginWebapplicationsDashboardAdministration::getType():
//            PluginWebapplicationsDashboardAdministration::showLists($_POST['value']);
//            break;
//        case PluginWebapplicationsDashboardLogicialInfrastructure::getType():
//            PluginWebapplicationsDashboardLogicialInfrastructure::showLists($_POST['value']);
//            break;
//        case PluginWebapplicationsDashboardPhysicalInfrastructure::getType():
//            PluginWebapplicationsDashboardPhysicalInfrastructure::showLists($_POST['value']);
//            break;
    }
}
