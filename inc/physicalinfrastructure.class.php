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
    public static $rightname = "plugin_webapplications";

    public static function getTypeName($nb = 0)
    {
        return __('Physical Infrastructure', 'webapplications');
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
            $item = ['id' => $st['items_id'], 'itemtype' => $st['itemtype']];

            $itemDBTM = new $st['itemtype'];
            if ($itemDBTM->getFromDB($st['items_id'])) {
                array_push($listItem, $item);
            }
        }

        return $listItem;
    }

}
