<?php

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if(isset($_POST['type'])&&isset($_POST['value'])) {

    switch ($_POST['type']) {
        case PluginWebapplicationsDashboard::getType():
            PluginWebapplicationsDashboard::showLists($_POST['value']);
            break;
        case PluginWebapplicationsDashboardEcosystem::getType():
            PluginWebapplicationsDashboardEcosystem::showLists($_POST['value']);
            break;
        case PluginWebapplicationsDashboardProcess::getType():
            PluginWebapplicationsDashboardProcess::showLists($_POST['value']);
            break;
        case PluginWebapplicationsDashboardApplication::getType():
            PluginWebapplicationsDashboardApplication::showLists($_POST['value']);
            break;
        case PluginWebapplicationsDashboardAdministration::getType():
            PluginWebapplicationsDashboardAdministration::showLists($_POST['value']);
            break;
        case PluginWebapplicationsDashboardLogicialInfrastructure::getType():
            PluginWebapplicationsDashboardLogicialInfrastructure::showLists($_POST['value']);
            break;
        case PluginWebapplicationsDashboardPhysicalInfrastructure::getType():
            PluginWebapplicationsDashboardPhysicalInfrastructure::showLists($_POST['value']);
            break;
    }
}
