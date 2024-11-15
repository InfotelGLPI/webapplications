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
 * Class PluginWebapplicationsKnowbase
 */
class PluginWebapplicationsKnowbase extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";

    public static function getTypeName($nb = 0)
    {
        return __('Knowledge base');
    }

    public static function getIcon()
    {
        return "ti ti-lifebuoy";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];
            $kbAppDBTM = new KnowbaseItem_Item();
            $kbApp     = $kbAppDBTM->find(['items_id' => $ApplianceId,
                'itemtype' => 'Appliance']);

            $nbEntities = count($kbApp);
            return self::createTabEntry(self::getTypeName($nbEntities), $nbEntities);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }

    public static function showLists()
    {

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];
        $item = new Appliance();
        $item->getFromDB($ApplianceId);

        PluginWebapplicationsDashboard::showHeaderDashboard($ApplianceId);

        $icon = "<i class='" . self::getIcon() . " fa-1x'></i>";

        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>$icon";
        echo "&nbsp;<span style='margin-right: auto;'>".__(
                'Knowledge base'
            )."</span>";
        echo "</h2>";

        echo "<div class='card-body'>";

        $withtemplate = 0;
        KnowbaseItem_Item::showForItem($item, $withtemplate);

        echo "</div>";
    }

    private static function getCountForItem(CommonDBTM $item): int
    {
        if ($item->getType() == KnowbaseItem::getType()) {
            $criteria['WHERE'] = [
                'glpi_knowbaseitems_items.knowbaseitems_id' => $item->getID(),
            ];
        } else {
            $criteria = self::getVisibilityCriteriaForItem($item);
            $criteria['WHERE'][] = [
                'glpi_knowbaseitems_items.itemtype' => $item::getType(),
                'glpi_knowbaseitems_items.items_id' => $item->getId(),
            ];
        }

        return countElementsInTable('glpi_knowbaseitems_items', $criteria);
    }

    /**
     * Return visibility criteria that must be used to find KB items related to given item.
     */
    private static function getVisibilityCriteriaForItem(CommonDBTM $item): array
    {
        $criteria = array_merge_recursive(
            [
                'INNER JOIN' => [
                    'glpi_knowbaseitems' => [
                        'ON' => [
                            'glpi_knowbaseitems_items' => 'knowbaseitems_id',
                            'glpi_knowbaseitems'       => 'id'
                        ]
                    ]
                ]
            ],
            KnowbaseItem::getVisibilityCriteria()
        );

        $entity_criteria = getEntitiesRestrictCriteria($item->getTable(), '', '', $item->maybeRecursive());
        if (!empty($entity_criteria)) {
            $criteria['INNER JOIN'][$item->getTable()] = [
                'ON' => [
                    'glpi_knowbaseitems_items' => 'items_id',
                    $item->getTable()          => 'id'
                ]
            ];
            $criteria['WHERE'][] = $entity_criteria;
        }

        return $criteria;
    }

    public static function showFromDashboard($appliance)
    {
        global $CFG_GLPI;

        echo "<div class='card-body child33'>";

        $ApplianceId = $appliance->getField('id');

        $title = self::getTypeName();
        $know_item = new KnowbaseItem();

        PluginWebapplicationsDashboard::showTitleforDashboard($title, $ApplianceId, $know_item, "");

        $number = self::getCountForItem($appliance);



        if ($number > 0) {

            echo "<div class='list-group'  style='margin-top: 10px;'>";
            $start = 0;
            foreach (KnowbaseItem_Item::getItems($appliance, $start, $_SESSION['glpilist_limit']) as $data) {
                $know_item->getFromDB($data['knowbaseitems_id']);
                $name = $know_item->getName();
                $open = $CFG_GLPI["root_doc"] . "/front/knowbaseitem.form.php";
                $open .= (strpos($open, '?') ? '&' : '?') . 'id=' . $data['knowbaseitems_id'];
                echo "<a class='list-group-item list-group-item-action' href='$open'>$name</a>";
            }

            echo "</div>";

        }


        echo "</div>";
    }
}
