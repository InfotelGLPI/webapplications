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
 * Class PluginWebapplicationsPhysicalInfrastructure
 */
class PluginWebapplicationsPhysicalInfrastructure extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";

    public static function getTypeName($nb = 0)
    {
        return __('Physical Infrastructure', 'webapplications');
    }

    public static function getIcon()
    {
        return "ti ti-server";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = count(self::getItems());
            return self::createTabEntry(self::getTypeName(), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new self();
        PluginWebapplicationsDashboard::showList($obj);
    }


    public static function getItems()
    {
        global $CFG_GLPI;
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $itemsAppDBTM = new Appliance_Item();

        $itemApp = $itemsAppDBTM->find([
            'appliances_id' => $ApplianceId,
            'itemtype' => $CFG_GLPI['inventory_types']
        ], 'itemtype');


        $listItem = [];
        foreach ($itemApp as $st) {

            $itemDBTM = new $st['itemtype'];
            if ($itemDBTM->getFromDB($st['items_id'])) {
                $item = ['id' => $st['items_id'],'name' => $itemDBTM->fields['name'], 'itemtype' => $st['itemtype']];
                array_push($listItem, $item);
            }
        }
        usort($listItem, function($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        return $listItem;
    }

    public static function showListObjects($list)
    {
        global $DB;

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];
        $list_by_itemtypes = [];
        foreach ($list as $item) {
            $list_by_itemtypes[$item['itemtype']][] =  $item['id'];
        }

        foreach ($list_by_itemtypes as $itemtype => $items) {

            $object = new $itemtype();
            $nb = 2;
            $icon = "<i class='" . $object->getIcon() . " fa-1x'></i>";
            echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>$icon";
            echo "&nbsp;<span style='margin-right: auto;'>".$object->getTypeName($nb)."</span></h2>";

            echo "<div style='display: flex;flex-wrap: wrap;'>";

            foreach ($items as $items_id) {

                $object->getFromDB($items_id);
                $id = $items_id;
                $name = $object->fields['name'];

                echo "<div class='card w-25'>";// style='margin-right: 10px;margin-top: 10px;'
                echo "<div class='card-body'>";
                echo "<div style='display: inline-block;margin: 40px;'>";

                $icon = "fa-server";
                if ($itemtype == "Phone") {
                    $icon = "fa-phone";
                } elseif ($itemtype == "Printer") {
                    $icon = "fa-print";
                } elseif ($itemtype == "NetworkEquipment") {
                    $icon = "fa-network-wired";
                }
                echo "<i class='fa-5x fas $icon'></i>";
                echo "</div>";

                echo "<span style='float: right'>";
                echo Html::showSimpleForm(
                    PLUGIN_WEBAPPLICATIONS_WEBDIR."/front/dashboard.php",
                    'reset',
                    __('Delete'),
                    ['items_id' => $id, 'itemtype' => $itemtype],
                    'fa-times-circle fa-2x'
                );
                echo "</span>";

                echo "<div style='display: inline-block;';>";

                echo "<h5 class='card-title' style='font-size: 14px;'>" . $object->getLink() . "</h5>";

                $items = $DB->request([
                    'FROM'   => Appliance_Item::getTable(),
                    'WHERE'  => [
                        'items_id' => $items_id,
                        'itemtype' => $itemtype
                    ]
                ]);
                $items = iterator_to_array($items);

                foreach ($items as $row) {
                    $iterator = $DB->request([
                        'FROM'   => Appliance_Item_Relation::getTable(),
                        'WHERE'  => [
                            Appliance_Item::getForeignKeyField() => $row['id']
                        ]
                    ]);

                    foreach ($iterator as $row) {
                        $envtype = $row['itemtype'];
                        $env = new $envtype();
                        $env->getFromDB($row['items_id']);
                        echo "<i class='" . $env->getIcon() . "'></i>" .
                            "&nbsp;" . $env->getLink();
                    }
                }

                echo "<p class='card-text'>";
                if ($itemtype == "Computer") {
                    echo Dropdown::getDropdownName("glpi_computertypes", $object->fields['computertypes_id']);
                } else if ($itemtype == "NetworkEquipment") {
                    echo Dropdown::getDropdownName("glpi_networkequipmenttypes", $object->fields['networkequipmenttypes_id']);
                }
                echo "</p>";
                echo "<p class='card-text'>";
                if ($itemtype == "Computer") {
                    $iterator = Item_OperatingSystem::getFromItem($object);

                    foreach ($iterator as $row) {
                        echo $row['name'] . " - " . $row['version'];
                        echo "</br>";
                        echo $row['architecture'];
                    }
                }
                echo "</p>";

                $link = $object::getFormURLWithID($id);
                $link .= "&forcetab=main";
                $rand = mt_rand();
                echo "<span style='float: right'>";
                if ($object->canUpdate()) {
                    echo Html::submit(
                        _sx('button', 'Edit'),
                        [
                            'name' => 'edit',
                            'class' => 'btn btn-secondary right',
                            'icon' => 'fas fa-edit',
                            'form' => '',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#edit' . $id . $rand
                        ]
                    );

                    echo Ajax::createIframeModalWindow(
                        'edit' . $id . $rand,
                        $link,
                        [
                            'display' => false,
                            'reloadonclose' => true
                        ]
                    );
                }
                echo "</span>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        }
    }
}
