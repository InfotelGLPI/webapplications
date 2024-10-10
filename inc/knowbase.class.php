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
    public static $rightname = "plugin_webapplications";

    public static function getTypeName($nb = 0)
    {
        return __('Knowledge base');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];
            $kbAppDBTM = new KnowbaseItem_Item();
            $kbApp     = $kbAppDBTM->find(['items_id' => $ApplianceId,
                'itemtype' => 'Appliance']);

            $nbEntities = count($kbApp);
            return self::createTabEntry(self::getTypeName(), $nbEntities);
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

        echo "<div class='card-body'>";

        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>" . __(
                'Knowledge base'
            );
        echo "</h2>";

        $item = new Appliance();
        $item->getFromDB($ApplianceId);
        $withtemplate = 0;

        KnowbaseItem_Item::showForItem($item, $withtemplate);

        echo "</div>";
    }
}
