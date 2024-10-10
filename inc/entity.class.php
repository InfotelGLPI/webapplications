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
 * Class PluginWebapplicationsEntity
 */
class PluginWebapplicationsEntity extends CommonDBTM
{
    use Glpi\Features\Inventoriable;

    public static $rightname = "plugin_webapplications_entities";


    public static function getTypeName($nb = 0)
    {
        return _n('Entity', 'Entities', $nb);
    }

    public static function getMenuContent()
    {
        $menu = [];

        $menu['title'] = self::getMenuName();
        $menu['page'] = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);
        if (self::canCreate()) {
            $menu['links']['add'] = self::getFormURL(false);
        }

        $menu['icon'] = self::getIcon();


        return $menu;
    }


    public static function getIcon()
    {
        return "fas fa-users";
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

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_entity_form.html.twig', [
            'item' => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        if (isset($appliance_id) && !empty($appliance_id)) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add(
                [
                    'appliances_id' => $appliance_id,
                    'items_id' => $this->getID(),
                    'itemtype' => 'PluginWebapplicationsEntity'
                ]
            );
        }
    }

    /**
     * @return array
     */
    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => self::getTypeName(2)
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink',
            'itemlink_type' => $this->getType(),
        ];

        $tab[] = [
            'id' => '2',
            'table' => User::getTable(),
            'field' => 'name',
            'linkfield' => 'owner',
            'name' => __('Owner', 'webapplications'),
            'datatype' => 'dropdown'
        ];


        $tab[] = [
            'id' => '3',
            'table' => User::getTable(),
            'field' => 'name',
            'linkfield' => 'security_contact',
            'name' => __('Security Contact', 'webapplications'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'relation_nature',
            'name' => __('Relation nature', 'webapplications'),
            'datatype' => 'text'
        ];

        return $tab;
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('PluginWebapplicationsProcess_Entity', $ong, $options);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        return $ong;
    }

    public static function showEcosystemFromDashboard($appliance)
    {
        echo "<div class='card-body'>";
        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>" . __(
                'Ecosystem',
                'webapplications'
            ) . "</h2>";


        $ApplianceId = $appliance->getField('id');

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsEntity']
        );
        $processDBTM = new PluginWebapplicationsEntity();

        echo "<div class='row flex-row'>";
        echo "<div class='form-field row col-12 col-sm-6  mb-2'>";

        echo "<label class='col-form-label col-xxl-5 text-xxl-end'>";
        echo __('Entities list', 'webapplications');
        echo "</label>";

        echo "<div class='col-xxl-7 field-container'>";
        if (!empty($procsApp)) {
            echo "<select name='processes' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($procsApp as $procApp) {
                if ($processDBTM->getFromDB($procApp['items_id'])) {
                    $name = $processDBTM->getName();
                    $link = PluginWebapplicationsEntity::getFormURLWithID($procApp['items_id']);
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

    public static function getEntities()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $entitiesAppDBTM = new Appliance_Item();
        $entitiesApp     = $entitiesAppDBTM->find(['appliances_id' => $ApplianceId,
            'itemtype' => 'PluginWebapplicationsEntity']);


        $listEntitiesId = [];
        foreach ($entitiesApp as $entityApp) {
            array_push($listEntitiesId, $entityApp['items_id']);
        }

        $listEntities = [];
        if (!empty($listEntitiesId)) {
            $entitiesDBTM = new PluginWebapplicationsEntity();
            $listEntities = $entitiesDBTM->find(['id' => $listEntitiesId]);
        }
        return $listEntities;
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

        $entitiesDBTM = new PluginWebapplicationsEntity();
        $linkAddEnt   = $entitiesDBTM::getFormURL();


        $listEntities = self::getEntities();

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
        echo __('Ecosystem', 'webapplications');

        echo "<span style='float: right'>";
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
        echo "</span>";
        echo "</h2>";

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
        echo _n("Entity list", "Entities list", count($listEntities), 'webapplications');
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
                echo "<a href='$linkEntity'>$name</a>";
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

                echo "<tr>";
                echo "<th>";
                echo __("Owner", 'webapplications');
                echo "</th>";
                echo "<td>";
                echo "<a href='$linkOwner'>".getUserName($ownerid)."</a>";
                echo "</td>";
                echo "</tr>";

                $processEntityDBTM = new PluginWebapplicationsProcess_Entity();
                $processes         = $processEntityDBTM->find(['plugin_webapplications_entities_id' => $entity['id']]);
                $processDBTM       = new PluginWebapplicationsProcess();

                echo "<tr>";
                echo "<th>";
                echo __('Processes list', 'webapplications');
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

                echo "<tr>";
                echo "<th>";
                echo __("Security Contact", 'webapplications');
                echo "</th>";
                echo "<td>";
                echo "<a href='$linkSecCont'>".getUserName($secContid)."</a>";
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
