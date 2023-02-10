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
 * Class PluginWebapplicationsDashboardAdministration
 */
class PluginWebapplicationsDashboardLogicalInfrastructure extends CommonDBTM
{
    public static $rightname         = "plugin_webapplications_logical_infra_dashboards";

    public static function getTypeName($nb = 0)
    {
        return __('Logical Infrastructure', 'webapplications');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
    {
        return __('Logical Infrastructure', 'webapplications');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
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
        echo "<div id=lists-LogicalInfra></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-LogicalInfra', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);
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
        echo $appliance->getName();

        echo ' </h3>
                           </h3>
        </div>';

        echo "<h1>".__('Logical Infrastructure', 'webapplications')."</h1>";
        echo "<hr>";

        echo Html::css(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/css/webapplications.css");
    }
}
