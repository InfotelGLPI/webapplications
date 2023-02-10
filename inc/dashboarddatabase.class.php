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
 * Class PluginWebapplicationsDashboardDatabase
 */
class PluginWebapplicationsDashboardDatabase extends CommonDBTM
{
    public static $rightname         = "plugin_webapplications_database_dashboards";

    public static function getTypeName($nb = 0)
    {
        return _n('Database', 'Databases', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nbDB = count(self::getDatabases());
            return self::createTabEntry(self::getTypeName($nbDB), $nbDB);
        }
        return _n("Database", 'Databases', 2);
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }

    public static function getDatabases()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $databasesAppDBTM = new Appliance_Item();
        $databaseApp = $databasesAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'DatabaseInstance']);


        $listDatabaseId = array();
        foreach ($databaseApp as $db) {
            $databasesAppDBTM->getFromDB($db['id']);

            array_push($listDatabaseId, $db['items_id']);
        }

        $listDatabases = array();
        if (!empty($listDatabaseId)) {
            $databaseDBTM = new DatabaseInstance();
            $listDatabases = $databaseDBTM->find(['id' => $listDatabaseId]);
        }
        return $listDatabases;
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
        echo "<div id=lists-Database></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-Database', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);
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

        $listDatabase = self::getDatabases();

        echo "<h1>";
        echo _n("Database", 'Databases', count($listDatabase));
        echo "</h1>";
        echo "<hr>";

        echo "<form name='form' method='post' action='" .
            Toolbox::getItemTypeFormURL('Appliance_Item') . "'>";
        echo "<div align='center'><table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='6'>" . __('Add an item') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='center'>";
        DatabaseInstance::dropdown(['name' => 'items_id']);
        echo "</td>";
        echo "<td class='tab_bg_2 center' colspan='6'>";
        echo Html::hidden('itemtype', ['value' => 'DatabaseInstance']);
        echo Html::hidden('appliances_id', ['value' => $ApplianceId]);
        echo Html::submit(_sx('button', 'Add'), ['name' => 'add', 'class' => 'btn btn-primary']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";

        Html::closeForm();


        echo "<div class='accordion' name=listDatabaseApp>";

        if (!empty($listDatabase)) {
            foreach ($listDatabase as $database) {
                $name = $database['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                $databasePluginDBTM = new PluginWebapplicationsDatabaseInstance();
                $linkDB = $databasePluginDBTM::getFormURLWithID($database['id']);
                $linkDB .= "&forcetab=main";

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo "<a href=$linkDB>$name</a>";
                echo "</td>";

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'data-bs-toggle' => 'modal', 'data-bs-target' =>'#editDB'.$database['id']]);

                echo Ajax::createIframeModalWindow(
                    'editDB'.$database['id'],
                    $linkDB,
                    ['display' => false,
                        'reloadonclose' => true]
                );

                echo "</td>";

                echo "</tr>";


                $comment = $database['comment'];

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo __("Comment");
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";



                $respSecurityid = $database['users_id_tech'];
                $respSecurity = new User();
                $respSecurity->getFromDB($respSecurityid);
                $respSecurityName = $respSecurity->getName();

                $linkDB = User::getFormURLWithID($respSecurityid);

                echo "<tr>";
                echo "<th>";
                echo __('Technician in charge of the hardware');
                echo "</th>";
                echo "<td>";
                if ($respSecurityid>0) {
                    echo "<a href=$linkDB>$respSecurityName</a>";
                } else {
                    echo $respSecurityName;
                }
                echo "</td>";
                echo "</tr>";


                $techtypeid = $database['databaseinstancetypes_id'];
                $techtype= new DatabaseInstanceType();
                $techtype->getFromDB($techtypeid);

                echo "<tr>";
                echo "<th>";
                echo __("Technology type", 'webapplication');
                echo "</th>";
                echo "<td>";
                echo $techtype->getName();
                echo "</td>";
                echo "</tr>";


                $streamItemDBTM = new PluginWebapplicationsStream_Item();
                $streams = $streamItemDBTM->find(['items_id' => $database['id'], 'itemtype' => 'DatabaseInstance']);
                $streamDBTM = new PluginWebapplicationsStream();

                echo "<tr>";
                echo "<th>";
                echo __("List Streams", 'webapplications');
                echo "</th>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td>";
                echo "<td>";
                if (!empty($streams)) {
                    echo "<select name='streams' id='list' Size='3' ondblclick='location = this.value;'>";
                    foreach ($streams as $stream) {
                        $streamDBTM->getFromDB($stream['plugin_webapplications_streams_id']);
                        $name = $streamDBTM->getName();
                        $link = PluginWebapplicationsStream::getFormURLWithID($stream['plugin_webapplications_streams_id']);
                        echo "<option value=$link>$name</option>";
                    }
                    echo "</select>";
                } else {
                    echo __("No associated stream", 'webapplications');
                }
                echo "</td>";
                echo "</tr>";


                echo "<tr>";
                echo "<th style='padding-top: 20px; padding-bottom: 20px'>";
                echo __('DICT', 'webapplications');
                echo "</th>";
                echo "<td class='inTable'>";


                $databaseplugin = new PluginWebapplicationsDatabaseInstance();
                $is_known = $databaseplugin->getFromDBByCrit(['databases_id'=>$database['id']]);


                if ($is_known) {
                    $disp = $databaseplugin->fields['webapplicationavailabilities'];
                    $int = $databaseplugin->fields['webapplicationintegrities'];
                    $conf = $databaseplugin->fields['webapplicationconfidentialities'];
                    $tra = $databaseplugin->fields['webapplicationtraceabilities'];


                    echo "<table style='text-align : center; width: 60%'>";

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
                } else {
                    echo NOT_AVAILABLE;
                }
                echo "</td>";
                echo "</tr>";


                echo "<tr>";
                echo "<th>";
                echo _n('External exposition', 'External exposition', 1, 'webapplications');
                echo "</th>";


                if ($is_known) {
                    $extexpoid = $databaseplugin->fields['webapplicationexternalexpositions_id'];
                } else {
                    $extexpoid = 0;
                }
                $extexpo = new PluginWebapplicationsWebapplicationExternalExposition();
                $extexpo->getFromDB($extexpoid);

                echo "<td>";
                echo  $extexpo->getName();
                echo "</td>";
                echo "</tr>";


                echo "</tbody>";
                echo "</table></div>";
            }
        } else {
            echo __("No associated database", 'webapplications');
        }
        echo "</div>";


        echo "<script>accordion();</script>";
    }
}
