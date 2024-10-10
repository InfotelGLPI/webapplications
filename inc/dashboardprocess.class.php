<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 webapplications plugin for GLPI
 Copyright (C) 2009-2023 by the webapplications Development Team.

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
class PluginWebapplicationsDashboardProcess extends CommonDBTM
{
    public static $rightname         = "plugin_webapplications_process_dashboards";

    public static function getTypeName($nb = 0)
    {
        return _n('Process', 'Processes', $nb, 'webapplications');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nbProcess = count(self::getProcesses());
            return self::createTabEntry(self::getTypeName($nbProcess), $nbProcess);
        }
        return _n('Process', 'Processes', 2, 'webapplications');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }

    public static function getProcesses()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId,
            'itemtype' => 'PluginWebapplicationsProcess']);


        $listProcId = array();
        foreach ($procsApp as $proc) {
            array_push($listProcId, $proc['items_id']);
        }

        $listProcesses = array();
        if (!empty($listProcId)) {
            $processDBTM = new PluginWebapplicationsProcess();
            $listProcesses = $processDBTM->find(['id' => $listProcId]);
        }
        return $listProcesses;
    }


    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        $options['candel'] = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance') . "</td>";

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

    public static function showLists()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);

        echo '<div class="card-header main-header d-flex flex-wrap mx-n2 mt-n2 align-items-stretch">
                        <h3 class="card-title d-flex align-items-center ps-4">
                                                <div class="ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1">
                     <i class="ti ti-versions fa-2x"></i>
                  </div>
                              <h3 style="margin: auto">';
        $linkApp = Appliance::getFormURLWithID($ApplianceId);
        $name = $appliance->getName();
        echo "<a href=$linkApp>$name</a>";

        echo ' </h3>
                           </h3>
 </div>';


        $processDBTM = new PluginWebapplicationsProcess();
        $linkAddProc=$processDBTM::getFormURL();

        $listProc = self::getProcesses();

        echo "<h1>"._n('Process', 'Processes', count($listProc), 'webapplications')."</h1>";
        echo "<hr>";
        echo "<h2>";

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'data-bs-toggle' => 'modal',
            'data-bs-target' =>'#addProc',
            'style' => 'float: right']);
        echo Ajax::createIframeModalWindow(
            'addProc',
            $linkAddProc . "?appliance_id=" . $ApplianceId,
            ['display' => false,
                'reloadonclose' => true]
        );

        echo "</h2>";
        echo "<div class='accordion' name=listProcessesApp>";

        if (!empty($listProc)) {
            foreach ($listProc as $process) {
                $name = $process['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                $linkProc = PluginWebapplicationsProcess::getFormURLWithID($process['id']);
                $linkProc .= "&forcetab=main";

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo "<a href=$linkProc>$name</a>";
                echo "</td>";

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'data-bs-toggle' => 'modal', 'data-bs-target' =>'#editProcess'.$process['id']]);

                echo Ajax::createIframeModalWindow(
                    'editProcess'.$process['id'],
                    $linkProc,
                    ['display' => false,
                        'reloadonclose' => true]
                );
                echo "</td>";

                echo "</tr>";

                $ownerid = $process['owner'];
                $linkOwner = User::getFormURLWithID($ownerid);
                $linkOwner .= "&forcetab=main";
                $owner = new User();
                $owner->getFromDB($ownerid);
                $ownerName = $owner->getName();
                echo "<tr>";
                echo "<th>";
                echo __("Owner", 'webapplications');
                echo "</th>";
                echo "<td>";
                if ($ownerid>0) {
                    echo "<a href=$linkOwner>$ownerName</a>";
                } else {
                    echo $ownerName;
                }
                echo "</td>";
                echo "</tr>";


                $processEntityDBTM = new PluginWebapplicationsProcess_Entity();
                $entities = $processEntityDBTM->find(['plugin_webapplications_processes_id' => $process['id']]);
                $entityDBTM = new PluginWebapplicationsEntity();

                echo "<tr>";
                echo "<th>";
                echo __('Entities list', 'webapplications');
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
                } else {
                    echo __("No associated entity", 'webapplications');
                }
                echo "</td>";
                echo "</tr>";


                $applianceItemDBTM = new Appliance_Item();
                $appliances = $applianceItemDBTM->find(['items_id' => $process['id'], 'itemtype' => 'PluginWebapplicationsProcess']);
                $applianceDBTM = new Appliance();

                echo "<tr>";
                echo "<th>";
                echo __('Appliances list', 'webapplications');
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
                } else {
                    echo __("No associated entity", 'webapplications');
                }
                echo "</td>";
                echo "</tr>";


                $disp = $process['webapplicationavailabilities'];
                $int = $process['webapplicationintegrities'];
                $conf = $process['webapplicationconfidentialities'];
                $tra = $process['webapplicationtraceabilities'];


                echo "<tr>";
                echo "<th style='padding-top: 20px; padding-bottom: 20px'>";
                echo __('DICT', 'webapplications');
                echo "</th>";
                echo "<td class='inTable'>";

                echo "<table style='text-align : center; width: 60%'>";

                echo "<td class='dict'>";
                echo __('Availability', 'webapplications')."&nbsp";
                echo "</td>";

                echo "<td name='webapplicationavailabilities' id='5'>";
                echo $disp;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo __('Integrity', 'webapplications')."&nbsp";
                echo "</td>";
                echo "<td name='webapplicationintegrities' id='6'>";
                echo $int;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo __('Confidentiality', 'webapplications')."&nbsp";
                echo "</td>";
                echo "<td name='webapplicationconfidentialities' id='7'>";
                echo $conf;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo __('Traceability', 'webapplications')."&nbsp";
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
                echo __('Comment');
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table></div>";
            }
        } else {
            echo __("No associated process", 'webapplications');
        }

        echo "</div>";

        echo "<script>accordion();</script>";
    }
}
