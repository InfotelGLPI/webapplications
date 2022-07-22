<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 webapplications plugin for GLPI
 Copyright (C) 2009-2016 by the webapplications Development Team.

 https://github.com/InfotelGLPI/webapplications
 -------------------------------------------------------------------------

 LICENSE

 This file is part of webapplications.

 webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginWebapplicationsDashboard
 */
class PluginWebapplicationsDashboard extends CommonDBTM {

    static $rightname = "plugin_webapplications_dashboards";

    static function getTypeName($nb = 0) {

        return __('Dashboard', 'webapplications');
    }

    static function getIcon() {
        return "fas fa-fw fa-border-all";
    }


    function showForm($ID, $options = []) {


        $options['candel']  = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance', 'webapplications') . "</td>";

        echo "<td >";
        Appliance::dropdown(['name' => 'applianceDropdown']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";

    }

    function defineTabs($options = []) {

        echo Html::css(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/lib/jquery-ui/jquery-ui.min.css");
        echo Html::script(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/lib/jquery-ui/jquery-ui.min.js");
        echo "<link rel='stylesheet' href='../css/style.css'>";
        echo $JS = <<<JAVASCRIPT
         <script type='text/javascript'>
         function accordion(classname) {
             if(classname == undefined){
                 classname  = 'accordion';
             }
             jQuery(document).ready(function () {
                 $("."+classname).accordion({
                     collapsible: true,
                     heightStyle: "content",
                     active: false
                 });
             });
         };
         </script>
        JAVASCRIPT;

        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('PluginWebapplicationsDashboardEcosystem', $ong, $options);// Vue Ecosystème
        $this->addStandardTab('PluginWebapplicationsDashboardProcess', $ong, $options);//Vue Metier
        $this->addStandardTab('PluginWebapplicationsDashboardApplication', $ong, $options);//Vue Applications
        $this->addStandardTab('PluginWebapplicationsDashboardAdministration', $ong, $options);//Vue Administration
        $this->addStandardTab('PluginWebapplicationsDashboardLogicialInfrastructure', $ong, $options);//Vue Infra logiques
        $this->addStandardTab('PluginWebapplicationsDashboardPhysicalInfrastructure', $ong, $options);//Vue Infra physiques


        return $ong;
    }


}
