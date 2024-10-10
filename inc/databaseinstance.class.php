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

use Glpi\Application\View\TemplateRenderer;

/**
 * Class PluginWebapplicationsDatabaseInstance
 */
class PluginWebapplicationsDatabaseInstance extends CommonDBTM
{
    public static $rightname = "plugin_webapplications";
    public static function getTypeName($nb = 0)
    {
        return _n("Database", 'Databases', $nb);
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

    /**
     * @param $params
     */
    public static function addFields($params)
    {
        $item = $params['item'];
        $webapp_database = new self();
        if ($item->getType() == 'DatabaseInstance') {
            if ($item->getID()) {
                $webapp_database->getFromDBByCrit(['databaseinstances_id' => $item->getID()]);
            } else {
                $webapp_database->getEmpty();
            }

            $options = [];

            if (isset($params["options"]["appliances_id"])) {
                $options = ['appliances_id' => $params["options"]["appliances_id"]];
            }

            TemplateRenderer::getInstance()->display('@webapplications/webapplication_database_form.html.twig', [
                'item' => $webapp_database,
                'params' => $options,
            ]);
        }
        return true;
    }

    public function showForm($ID, $options = [])
    {
        $instance = new DatabaseInstance();
        $instance->showForm($ID, $options);

        return true;
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        $items_id = $this->input['databaseinstances_id'];
        if (isset($appliance_id) && !empty($appliance_id)) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add([
                'appliances_id' => $appliance_id,
                'items_id' => $items_id,
                'itemtype' => 'DatabaseInstance'
            ]);
        }
    }

    /**
     * @param \Database $item
     *
     * @return false
     */
    public static function databaseAdd(DatabaseInstance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setDatabase($item);
    }


    /**
     * @param \Database $item
     *
     * @return false
     */
    public static function databaseUpdate(DatabaseInstance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setDatabase($item);
    }

    /**
     * @param \Database $item
     */
    public static function setDatabase(DatabaseInstance $item)
    {
        $database = new PluginWebapplicationsDatabaseInstance();
        if (!empty($item->fields)) {
            $database->getFromDBByCrit(['databaseinstances_id' => $item->getID()]);
            if (is_array($database->fields) && count($database->fields) > 0) {
                $database->update([
                    'id' => $database->fields['id'],
                    'webapplicationexternalexpositions_id' => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : $database->fields['plugin_webapplications_webapplicationexternalexpositions_id'],
                    'webapplicationavailabilities' => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : $database->fields['plugin_webapplications_webapplicationavailabilities'],
                    'webapplicationintegrities' => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : $database->fields['plugin_webapplications_webapplicationintegrities'],
                    'webapplicationconfidentialities' => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : $database->fields['plugin_webapplications_webapplicationconfidentialities'],
                    'webapplicationtraceabilities' => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : $database->fields['plugin_webapplications_webapplicationtraceabilities']
                ]);
            } else {
                $database->add([
                    'webapplicationexternalexpositions_id' => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : 0,
                    'webapplicationavailabilities' => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : 0,
                    'webapplicationintegrities' => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : 0,
                    'webapplicationconfidentialities' => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : 0,
                    'webapplicationtraceabilities' => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : 0,
                    'appliances_id' => isset($item->input['appliances_id']) ? $item->input['appliances_id'] : 0,
                    'databaseinstances_id' => $item->getID()
                ]);
            }
        }
    }

    public function post_getEmpty()
    {
        $this->fields["webapplicationconfidentialities"] = 0;
    }

    /**
     * @param $item
     */
    public static function cleanRelationToDatabase($item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['databaseinstances_id' => $item->getID()]);
    }

    public static function showDatabaseFromDashboard($appliance)
    {
        echo "<div class='card-body'>";
        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>" . _n(
                'Database',
                'Databases',
                2
            ) . "</h2>";

        $ApplianceId = $appliance->getField('id');

        $databasesAppDBTM = new Appliance_Item();
        $databasesApp = $databasesAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'DatabaseInstance']);
        $databaseDBTM = new DatabaseInstance();

        echo "<div class='row flex-row'>";
        echo "<div class='form-field row col-12 col-sm-12  mb-2'>";

        echo "<label class='col-form-label col-xxl-5 text-xxl-end'>";
        echo _n("Database list", "Databases list", count($databasesApp), 'webapplications');
        echo "</label>";

        echo "<div class='col-xxl-7 field-container'>";
        if (!empty($databasesApp)) {
            echo "<select name='databases' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($databasesApp as $dbApp) {
                if ($databaseDBTM->getFromDB($dbApp['items_id'])) {
                    $name = $databaseDBTM->getName();
                    $link = DatabaseInstance::getFormURLWithID($dbApp['items_id']);
                    echo "<option value='$link'>$name</option>";
                }
            }
            echo "</select>";
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }

    public static function getDatabases()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $databasesAppDBTM = new Appliance_Item();
        $databaseApp = $databasesAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'DatabaseInstance']);


        $listDatabaseId = [];
        foreach ($databaseApp as $db) {
            array_push($listDatabaseId, $db['items_id']);
        }

        $listDatabases = [];
        if (!empty($listDatabaseId)) {
            $databaseDBTM = new DatabaseInstance();
            $listDatabases = $databaseDBTM->find(['id' => $listDatabaseId]);
        }
        return $listDatabases;
    }

    public static function showLists()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);

        echo "<div class='card-header main-header d-flex flex-wrap mx-n2 mt-n2 align-items-stretch'>";
        echo "<h3 class='card-title d-flex align-items-center ps-4'>";
        echo "<div class='ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1'>";
        echo "<i class='ti ti-versions fa-2x'></i>";
        echo "</div>";
        echo "<h3 style='margin: auto'>";
        $linkApp = Appliance::getFormURLWithID($ApplianceId);
        $name = $appliance->getName();
        echo "<a href='$linkApp'>$name</a>";

        echo "</h3>";
        echo "</h3>";
        echo "</div>";

        $listDatabase = self::getDatabases();

        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>";
        echo _n("Database", 'Databases', count($listDatabase));
        echo "</h2>";


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

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
        echo _n("Database list", "Databases list", count($listDatabase), 'webapplications');
        echo "</h2>";


        if (empty($listDatabase)) {

            echo "<table class='tab_cadre_fixe'>";
            echo "<tbody>";
            echo "<tr class='center'>";
            echo "<td colspan='4'>";
            echo __("No associated databases", 'webapplications');
            echo "</td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";

        } else {

            echo "<div class='accordion' name=listDatabaseApp>";

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
                echo Html::submit(
                    _sx('button', 'Edit'),
                    [
                        'name' => 'edit',
                        'class' => 'btn btn-secondary',
                        'icon' => 'fas fa-edit',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#editDB' . $database['id']
                    ]
                );

                echo Ajax::createIframeModalWindow(
                    'editDB' . $database['id'],
                    $linkDB,
                    [
                        'display' => false,
                        'reloadonclose' => true
                    ]
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
                echo __('Technician in charge');
                echo "</th>";
                echo "<td>";
                if ($respSecurityid > 0) {
                    echo "<a href=$linkDB>$respSecurityName</a>";
                } else {
                    echo $respSecurityName;
                }
                echo "</td>";
                echo "</tr>";


                $techtypeid = $database['databaseinstancetypes_id'];
                $techtype = new DatabaseInstanceType();
                $techtype->getFromDB($techtypeid);

                echo "<tr>";
                echo "<th>";
                echo __("Technology type", 'webapplications');
                echo "</th>";
                echo "<td>";
                echo $techtype->getName();
                echo "</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<th style='padding-top: 20px; padding-bottom: 20px'>";
                echo __('DICT', 'webapplications');
                echo "</th>";
                echo "<td class='inTable'>";


                $databaseplugin = new PluginWebapplicationsDatabaseInstance();
                $is_known = $databaseplugin->getFromDBByCrit(['databaseinstances_id' => $database['id']]);


                if ($is_known) {
                    $disp = $databaseplugin->fields['webapplicationavailabilities'];
                    $int = $databaseplugin->fields['webapplicationintegrities'];
                    $conf = $databaseplugin->fields['webapplicationconfidentialities'];
                    $tra = $databaseplugin->fields['webapplicationtraceabilities'];

                    echo "<table style='text-align : center; width: 60%'>";
                    echo "<td class='dict'>";
                    echo __('Availability', 'webapplications') . "&nbsp";
                    echo "</td>";

                    echo "<td name='webapplicationavailabilities' id='5'>";
                    echo $disp;
                    echo "</td>";

                    echo "<td></td>";

                    echo "<td class='dict'>";
                    echo __('Integrity', 'webapplications') . "&nbsp";
                    echo "</td>";
                    echo "<td name='webapplicationintegrities' id='6'>";
                    echo $int;
                    echo "</td>";

                    echo "<td></td>";

                    echo "<td class='dict'>";
                    echo __('Confidentiality', 'webapplications') . "&nbsp";
                    echo "</td>";
                    echo "<td name='webapplicationconfidentialities' id='7'>";
                    echo $conf;
                    echo "</td>";


                    echo "<td></td>";

                    echo "<td class='dict'>";
                    echo __('Traceability', 'webapplications') . "&nbsp";
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
                echo $extexpo->getName();
                echo "</td>";
                echo "</tr>";


                echo "</tbody>";
                echo "</table></div>";
            }
        }
        echo "</div>";
        echo "<script>accordion();</script>";
    }
}
