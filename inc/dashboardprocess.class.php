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
 * Class PluginWebapplicationsDashboardProcess
 */
class PluginWebapplicationsDashboardProcess extends CommonDBTM {

    static $rightname         = "plugin_webapplications_process_dashboards";

    static function getTypeName($nb = 0) {

        return __('DashboardProcess', 'webapplications');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

        return __('Process', 'webapplications');

    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        $class = new self();
        $class->showForm($item);
        return true;
    }


    function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        $options['candel'] = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance', 'webapplications') . "</td>";

        echo "<td >";
        $rand = Appliance::dropdown(['name' => 'applianceDropdown']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        echo "<div id=lists-Process></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-Process', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);

    }

    static function showLists($ApplianceId){

        echo "<h1>Business process</h1>";
        echo "<hr>";
        echo "<h2>";
        echo "Processes";

        $processDBTM = new PluginWebapplicationsProcess();
        $linkAddProc=$processDBTM::getFormURL();

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'style' => 'float: right',
            'onclick' => "window.location.href='" . $linkAddProc . "'"]);

        echo "</h2>";
        echo "<div class='accordion' name=listProcessesApp>";

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsProcess']);


        $listProcId = array();
        foreach ($procsApp as $proc) {
            $procsAppDBTM->getFromDB($proc['id']);

            array_push($listProcId, $proc['items_id']);
        }


        if(!empty($listProcId)){
            $processes = $processDBTM->find(['id' => $listProcId]);
            foreach ($processes as $process) {

                $processDBTM->getFromDB($process['id']);

                $name = $process['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                echo "<tr>";
                echo "<th>";
                echo "Name";
                echo "</th>";
                echo "<td>";
                echo $name;
                echo "</td>";

                $linkProc = PluginWebapplicationsProcess::getFormURLWithID($process['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkProc . "'"]);
                echo "</td>";

                echo "</tr>";

                $owner = $process['owner'];

                echo "<tr>";
                echo "<th>";
                echo "Owner";
                echo "</th>";
                echo "<td>";
                echo $owner;
                echo "</td>";
                echo "</tr>";


                $processEntityDBTM = new PluginWebapplicationsProcess_Entity();
                $entities = $processEntityDBTM->find(['plugin_webapplications_processes_id' => $process['id']]);
                $entityDBTM = new PluginWebapplicationsEntity();

                echo "<tr>";
                echo "<th>";
                echo "List Entities";
                echo "</th>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td>";
                echo "<td>";
                if (!empty($entities)) {

                    echo "<select name='entities' id='list' Size='3' ondblclick='location = this.value;'>";
                    foreach ($entities as $entity) {
                        $entityDBTM->getFromDB($entity['plugin_webapplications_entities_id']);
                        $name = $entityDBTM->getName();
                        $link = PluginWebapplicationsEntity::getFormURLWithID($entity['plugin_webapplications_entities_id']);
                        echo "<option value=$link>$name</option>";
                    }
                    echo "</select>";

                } else echo "no associated entity";
                echo "</td>";
                echo "</tr>";


                $applianceItemDBTM = new Appliance_Item();
                $appliances = $applianceItemDBTM->find(['items_id' => $process['id'], 'itemtype' => 'PluginWebapplicationsProcess']);
                $applianceDBTM = new Appliance();

                echo "<tr>";
                echo "<th>";
                echo "List Appliances";
                echo "</th>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td>";
                echo "<td>";
                if (!empty($appliances)) {

                    echo "<select name='appliances' id='list' Size='3' ondblclick='location = this.value;'>";
                    foreach ($appliances as $appliance) {
                        $applianceDBTM->getFromDB($appliance['appliances_id']);
                        $name = $applianceDBTM->getName();
                        $link = Appliance::getFormURLWithID($appliance['appliances_id']);
                        echo "<option value=$link>$name</option>";
                    }
                    echo "</select>";

                } else echo "no associated appliance";
                echo "</td>";
                echo "</tr>";


                $disp = $process['webapplicationavailabilities'];
                $int = $process['webapplicationintegrities'];
                $conf = $process['webapplicationconfidentialities'];
                $tra = $process['webapplicationtraceabilities'];


                echo "<tr>";
                echo "<th style='padding-top: 20px; padding-bottom: 20px'>";
                echo "DICT";
                echo "</th>";
                echo "<td>";

                echo "<table style='text-align : center; width: 60%'>";

                echo "<td class='dict'>";
                echo "Availability &nbsp";
                echo "</td>";

                echo "<td name='webapplicationavailabilities' id='5'>";
                echo $disp;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo "Integrity &nbsp";
                echo "</td>";
                echo "<td name='webapplicationintegrities' id='6'>";
                echo $int;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo "Confidentiality &nbsp";
                echo "</td>";
                echo "<td name='webapplicationconfidentialities' id='7'>";
                echo $conf;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo "Tracabeality &nbsp";
                echo "</td>";
                echo "<td name='webapplicationtraceabilities' id='8'>";
                echo $tra;
                echo "</td>";

                echo "</table>";

                echo "</td>";
                echo "</tr>";

                $comment = $process['comment'];

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo "Comment";
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid white; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table></div>";
            }
        }
        else echo "No process";

        echo "</div>";

        echo "<script>accordion();</script>";


    }

}
