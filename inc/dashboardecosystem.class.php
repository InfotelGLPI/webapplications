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
 * Class PluginWebapplicationsDashboardEcosystem
 */
class PluginWebapplicationsDashboardEcosystem extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_ecosystem_dashboards";

    public static function getTypeName($nb = 0)
    {
        return __('Ecosystem', 'webapplications');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nbEntities = count(self::getEntities());
            return self::createTabEntry(self::getTypeName(), $nbEntities);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }

    public static function getEntities()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $entitiesAppDBTM = new Appliance_Item();
        $entitiesApp     = $entitiesAppDBTM->find(['appliances_id' => $ApplianceId,
            'itemtype' => 'PluginWebapplicationsEntity']);


        $listEntitiesId = array();
        foreach ($entitiesApp as $entityApp) {
            array_push($listEntitiesId, $entityApp['items_id']);
        }

        $listEntities = array();
        if (!empty($listEntitiesId)) {
            $entitiesDBTM = new PluginWebapplicationsEntity();
            $listEntities = $entitiesDBTM->find(['id' => $listEntitiesId]);
        }
        return $listEntities;
    }

    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance') . "</td>";

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

        echo '</h3></h3>
              </div>';

        


        $entitiesDBTM = new PluginWebapplicationsEntity();
        $linkAddEnt   = $entitiesDBTM::getFormURL();


        $listEntities = self::getEntities();

        echo "<h1>".__('Ecosystem', 'webapplications')."</h1>";
        echo "<hr>";
        echo "<h2>";
        echo _n('Entity', 'Entities', count($listEntities));

        echo Html::submit(
            _sx('button', 'Add'),
            ['name' => 'edit',
                'class' => 'btn btn-primary',
                'icon' => 'fas fa-plus',
                'data-bs-toggle' => 'modal',
                'data-bs-target' =>'#addEntity',
                'style' => 'float: right']
        );
        echo Ajax::createIframeModalWindow(
            'addEntity',
            $linkAddEnt."?appliance_id=".$ApplianceId,
            ['display' => false,
                'reloadonclose' => true]
        );


        echo "</h2>";

        echo "<div class='accordion' name=listEntitiesApp>";


        if (!empty($listEntities)) {
            foreach ($listEntities as $entity) {
                $name = $entity['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                $linkEntity = PluginWebapplicationsEntity::getFormURLWithID($entity['id']);
                $linkEntity .= "&forcetab=main";

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo "<a href=$linkEntity>$name</a>";
                echo "</td>";

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'data-bs-toggle' => 'modal', 'data-bs-target' =>'#editEntity'.$entity['id']]);

                echo Ajax::createIframeModalWindow(
                    'editEntity'.$entity['id'],
                    $linkEntity,
                    ['display' => false,
                        'reloadonclose' => true]
                );

                echo "</td>";


                echo "</tr>";

                $ownerid = $entity['owner'];
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
                echo "<a href=$linkOwner>$ownerName</a>";
                echo "</td>";
                echo "</tr>";

                $processEntityDBTM = new PluginWebapplicationsProcess_Entity();
                $processes         = $processEntityDBTM->find(['plugin_webapplications_entities_id' => $entity['id']]);
                $processDBTM       = new PluginWebapplicationsProcess();

                echo "<tr>";
                echo "<th>";
                echo __('List Processes', 'webapplications');
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
                } else {
                    echo __("No associated process", 'webapplications');
                }
                echo "</td>";
                echo "</tr>";


                $secContid = $entity['security_contact'];
                $linkSecCont = User::getFormURLWithID($secContid);
                $linkSecCont .= "&forcetab=main";
                $secCont = new User();
                $secCont->getFromDB($secContid);
                $secContName = $secCont->getName();
                echo "<tr>";
                echo "<th>";
                echo __("Security Contact", 'webapplications');
                echo "</th>";
                echo "<td>";
                echo "<a href=$linkSecCont>$secContName</a>";
                echo "</td>";
                echo "</tr>";

                $relation = $entity['relation_nature'];
                echo "<tr>";
                echo "<th>";
                echo __("Relation nature", 'webapplications');
                echo "</th>";
                echo "<td>";
                echo $relation;
                echo "</td>";
                echo "</tr>";


                echo "</tbody>";
                echo "</table></div>";
            }
        } else {
            echo __("No entity founded", 'webapplications');
        }

        echo "</div>";
        echo "<script>accordion();</script>";
    }
}
