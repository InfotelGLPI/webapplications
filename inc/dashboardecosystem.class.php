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
 * Class PluginWebapplicationsDashboardEcosystem
 */
class PluginWebapplicationsDashboardEcosystem extends CommonDBTM {

    static $rightname = "plugin_webapplications_ecosystem_dashboards";

    static function getTypeName($nb = 0) {

        return __('DashboardEcosystem', 'webapplications');
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

        return __('Ecosystem', 'webapplications');

    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        $eco = new PluginWebapplicationsDashboardEcosystem();
        $eco->showForm($item);
        return true;
    }

    public function showForm($ID, $options = []) {
        global $CFG_GLPI;

        $options['candel']  = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance', 'webapplications') . "</td>";

        echo "<td >";
        $rand = Appliance::dropdown(['name' => 'applianceDropdown']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        echo "<div id=lists-Ecosystem></div>";

        $array['value'] = '__VALUE__';
        $array['type']  = self::getType();

        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown' . $rand, 'lists-Ecosystem', $CFG_GLPI['root_doc'] . PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . '/ajax/getLists.php', $array);

    }

    static function showLists($ApplianceId) {


        echo "<h1>Ecosystem</h1>";
        echo "<hr>";
        echo "<h2>";
        echo "Entities";

        $entitiesDBTM = new PluginWebapplicationsEntity();
        $linkAddEnt   = $entitiesDBTM::getFormURL();

        echo Html::submit(_sx('button', 'Add'), ['name'    => 'edit',
                                                 'class'   => 'btn btn-primary',
                                                 'icon'    => 'fas fa-plus',
                                                 'style'   => 'float: right',
                                                 'onclick' => "window.location.href='" . $linkAddEnt . "'"]);

        echo "</h2>";

        echo "<div class='accordion' name=listEntitiesApp>";

        $entitiesAppDBTM = new Appliance_Item();
        $entitiesApp     = $entitiesAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsEntity']);


        $listEntitiesId = array();
        foreach ($entitiesApp as $entityApp) {
            $entitiesAppDBTM->getFromDB($entityApp['id']);

            array_push($listEntitiesId, $entityApp['items_id']);
        }


        if (!empty($listEntitiesId)) {
            $entities = $entitiesDBTM->find(['id' => $listEntitiesId]);
            foreach ($entities as $entity) {

                $entitiesDBTM->getFromDB($entity['id']);

                $name = $entity['name'];

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

                $linkEntity = PluginWebapplicationsEntity::getFormURLWithID($entity['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkEntity . "'"]);
                echo "</td>";

                echo "</tr>";

                $owner = $entity['owner'];
                echo "<tr>";
                echo "<th>";
                echo "Owner";
                echo "</th>";
                echo "<td>";
                echo $owner;
                echo "</td>";
                echo "</tr>";


                $processEntityDBTM = new PluginWebapplicationsProcess_Entity();
                $processes         = $processEntityDBTM->find(['plugin_webapplications_entities_id' => $entity['id']]);
                $processDBTM       = new PluginWebapplicationsProcess();

                echo "<tr>";
                echo "<th>";
                echo "List Processes";
                echo "</th>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td>";
                echo "<td>";
                if (!empty($processes)) {

                    echo "<select name='processes' id='list' Size='3' ondblclick='location = this.value;'>";
                    foreach ($processes as $process) {
                        $processDBTM->getFromDB($process['plugin_webapplications_processes_id']);
                        $name = $processDBTM->getName();
                        $link = PluginWebapplicationsProcess::getFormURLWithID($process['plugin_webapplications_processes_id']);
                        echo "<option value=$link>$name</option>";
                    }
                    echo "</select>";

                } else echo "no associated process";
                echo "</td>";
                echo "</tr>";


                $securityContact = $entity['security_contact'];
                echo "<tr>";
                echo "<th>";
                echo "Security Contact";
                echo "</th>";
                echo "<td>";
                echo $securityContact;
                echo "</td>";
                echo "</tr>";

                $relation = $entity['relation_nature'];
                echo "<tr>";
                echo "<th>";
                echo "Relation nature";
                echo "</th>";
                echo "<td>";
                echo $relation;
                echo "</td>";
                echo "</tr>";


                echo "</tbody>";
                echo "</table></div>";
            }
        } else echo "No process";

        echo "</div>";
        echo "<script>accordion();</script>";


    }


}
