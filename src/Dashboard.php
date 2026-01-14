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

namespace GlpiPlugin\Webapplications;

use Ajax;
use Appliance_Item;
use Appliance_Item_Relation;
use Certificate_Item;
use CommonDBTM;
use Contract;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Group_User;
use Html;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


/**
 * Class Dashboard
 */
class Dashboard extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_dashboards";

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab(Entity::class, $ong, $options);
        $this->addStandardTab(Process::class, $ong, $options);
        $this->addStandardTab(
            PhysicalInfrastructure::class,
            $ong,
            $options
        );
        $this->addStandardTab(
            LogicalInfrastructure::class,
            $ong,
            $options
        );
        $this->addStandardTab(DatabaseInstance::class, $ong, $options);
        $this->addStandardTab(Certificate::class, $ong, $options);
        $this->addStandardTab(Stream::class, $ong, $options);
        $this->addStandardTab(Knowbase::class, $ong, $options);
        $this->addStandardTab(Printpdf::class, $ong, $options);

        return $ong;
    }

    public static function getTypeName($nb = 0)
    {
        return __('Appliance dashboard', 'webapplications');
    }

    public function getHeaderName($options = []): string
    {
        $appId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;
        if (!isset($_SESSION['plugin_webapplications_loaded_appliances_id'])) {
            $_SESSION['plugin_webapplications_loaded_appliances_id'] = 0;
        }
        if ($appId > 0) {
            $appliance = new \Appliance();
            $appliance->getFromDB($appId);
            return $appliance->getName();
        }
        return "";
    }


    public static function getMenuContent()
    {
        $menu = [];
        $menu['title'] = self::getMenuName();
        $menu['page'] = self::getSearchURL(false);

        $menu['icon'] = self::getIcon();

        return $menu;
    }

    public static function getIcon()
    {
        return "ti ti-border-all";
    }


    public static function selectAppliance($id)
    {
        global $CFG_GLPI;

        echo "<div class='center' style='margin-top: 10px'>";
        $rand = \Appliance::dropdown(['name' => 'applianceDropdown', 'value' => $id]);
        echo "</div>";


        echo "<div id='lists-dashboard'>";
        if ($id > 0) {
            $dashboard = new self();
            $dashboard->display(['id' => 1, 'appliances_id' => $id]);
        }
        echo "</div>";

        $array['value'] = '__VALUE__';
        $array['type'] = self::getType();
        $array['reload'] = false;
        Ajax::updateItemOnSelectEvent(
            'dropdown_applianceDropdown' . $rand,
            'lists-dashboard',
            $CFG_GLPI['root_doc'] . PLUGIN_WEBAPPLICATIONS_WEBDIR . '/ajax/getLists.php',
            $array
        );
    }

    public function showForm($ID, $options = [])
    {
        echo Html::css(PLUGIN_WEBAPPLICATIONS_WEBDIR . "/css/webapplications.css");

        $options['candel'] = false;
        $options['colspan'] = 1;

        $ApplianceId = $options['appliances_id'];

        $appliance = new \Appliance();
        $appliance->getFromDB($ApplianceId);

        self::showHeaderDashboard($ApplianceId);

        echo "<div style='display: flex;flex-wrap: wrap;'>";

        $userAdminId = $appliance->fields['users_id_tech'] ?? 0;
        $groupsAdminId = $appliance->fields['groups_id_tech'] ?? [];
        $Group_User = new Group_User();
        $numberAdmin = 0;
        foreach ($groupsAdminId as $groupId) {
            if ($groupId > 0) {
                $groupsAdmin = $Group_User->find(['groups_id' => $groupId]);
                if (count($groupsAdmin) > 0) {
                    $groupUserAdmin = array_column($groupsAdmin, 'users_id');
                    $numberAdmin += count(array_unique($groupUserAdmin));
                }
            }
        }

        $applianceplugin = new Appliance();
        $applianceplugin->getFromDBByCrit(['appliances_id' => $ApplianceId]);

        echo "<div class='card-body child33' style='text-align:center;font-weight: bold'>";
        echo "<h2>";
        echo _n('User', 'Users', 2);
        echo "</h2>";
        echo "<i class='fa fa-users fa-3x'></i>";
        echo "<br>";
        echo "<h1>";
        $number_users = $applianceplugin->fields['number_users'] ?? 0;
        echo Appliance::getNbUsersValue($number_users);
        echo "</h1>";
        echo "</div>";

        echo "<div class='card-body child33' style='text-align:center;font-weight: bold'>";
        echo "<h2>";
        echo __('Project leader', 'webapplications');
        echo "</h2>";
        echo "<i class='fa fa-user-cog fa-3x'></i>";
        echo "<br>";
        echo "<h2>";
        echo getUserName($userAdminId);
        echo "</h2>";
        echo "</div>";

        echo "<div class='card-body child33' style='text-align:center;font-weight: bold'>";
        echo "<h2>";
        echo __('Project team', 'webapplications');
        echo "</h2>";
        echo "<i class='fa fa-users-cog fa-3x'></i>";
        echo "<br>";
        echo "<h1>";
        echo $numberAdmin;
        echo "</h1>";
        echo "</div>";

        echo "</div>";

        echo "<div style='display: flex;flex-wrap: wrap;'>";

        Appliance::showSupportPartFromDashboard($appliance);

        Appliance::showDocumentsAndContractsFromDashboard($appliance);

        Knowbase::showFromDashboard($appliance);

        echo "</div>";

        echo "<div class='card-body border-0'>";
        $title = __('Summary', 'webapplications');
        self::showTitleforDashboard($title, $ApplianceId, $applianceplugin, "edit", "editapp");

        echo "</div>";

        $options = [];
        $options['canedit'] = false;
        $options['candel'] = false;

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_summary.html.twig', [
            'item' => $appliance,
            'params' => $options,
            'no_header' => true,
        ]);

        echo "<div style='display: flex;flex-wrap: wrap;'>";

        self::showFromDashboard($appliance, new Entity());

        self::showFromDashboard($appliance, new Process());

        self::showFromDashboard($appliance, new PhysicalInfrastructure());

        self::showFromDashboard($appliance, new \DatabaseInstance());

        self::showFromDashboard($appliance, new \Certificate());

        self::showFromDashboard($appliance, new Stream());

        echo "</div>";
    }

    //0296333734

    public static function showHeaderDashboard($ApplianceId)
    {
        echo "<div class='card-header card-web-header main-header d-flex flex-wrap mx-n2 mt-n2 align-items-stretch'>";
        echo "<h3 class='card-title d-flex align-items-center ps-4'>";

        echo "<div class='ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1'>";
        $appliance = new \Appliance();
        $appliance->getFromDB($ApplianceId);
        $icon = $appliance->getIcon();
        echo "<i class='" . $icon . " fa-2x'></i>";
        echo "</div>";

        echo "<h1 style='margin: auto'>";
        $linkApp = \Appliance::getFormURLWithID($appliance->getID());
        $name = $appliance->getLink();
        echo "<a href='$linkApp'>" . $name . "</a>";
        echo "</h1>";
        echo "</h3>";

        echo "</div>";
        echo "</div>";
    }

    public static function showTitleforDashboard($title, $id, $item = false, $type = "add", $name = "")
    {
        // <i class='fas fa-1x fa-caret-right'></i>

        $icon = "";
        if ($item != false && $id > 0) {
            if ($item->getType() == "Contract_Item") {
                $icon = "<i class='" . Contract::getIcon() . " fa-1x'></i>";
            } else {
                $icon = "<i class='" . $item->getIcon() . " fa-1x'></i>";
            }

        }
        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>$icon";
        echo "&nbsp;<span style='margin-right: auto;'>$title</span>";

        if ($item != false && $id > 0 && $name != "") {
            //            echo "<div class='ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1'>";
            //            echo "<i class='" . $item->getIcon() . " fa-2x'></i>";
            //            echo "</div>";

            if ($type == "add") {
                $linkApp = $item::getFormURL();
                $title = _sx('button', 'Add');
            } else {
                $linkApp = $item::getFormURLWithID($id);
                $linkApp .= "&forcetab=main";
                Toolbox::logInfo($linkApp);
                $title = _sx('button', 'Edit');
            }

            $rand = mt_rand();
            if ($item->getType() != "DatabaseInstance"
                && $item->getType() != PhysicalInfrastructure::class
                && $item->canUpdate()) {
                echo "<span style='float: right'>";
                echo Html::submit(
                    $title,
                    [
                        'name' => 'edit',
                        'class' => 'btn btn-secondary',
                        'icon' => 'ti ti-edit',
                        'style' => 'float: right',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#' . $name . $id . $rand,
                    ]
                );

                echo Ajax::createIframeModalWindow(
                    $name . $id . $rand,
                    $linkApp,
                    [
                        'display' => false,
                        'reloadonclose' => true,
                    ]
                );
                echo "</span>";
            }
        }
        echo "</h2>";
    }


    public static function showFromDashboard($appliance, $item)
    {
        global $DB;

        echo "<div class='card-body child50'>";

        $ApplianceId = $appliance->getField('id');

        $app_item = new Appliance_Item();

        if ($item->getType() == PhysicalInfrastructure::class) {
            $apps = PhysicalInfrastructure::getItems();
        } elseif ($item->getType() == "Certificate") {
            $apps = self::getObjects($item, $ApplianceId);
        } else {
            $apps = $app_item->find(['appliances_id' => $ApplianceId, 'itemtype' => $item->getType()]);
        }
        $title = $item->getTypeName(count($apps));

        self::showTitleforDashboard($title, $ApplianceId, $item);

        $obj = new $item();

        echo "<div class='row flex-row'>";
        echo "<div class='form-field row col-12 col-sm-12 mb-2'>";

        echo "<div class='col-xxl-12 field-container list-group' style='display: grid; grid-template-columns: 1fr 1fr; gap: 10px;'>";
        if (!empty($apps)) {
            foreach ($apps as $app) {
                if ($item->getType() == PhysicalInfrastructure::class) {
                    $itemDBTM = new $app['itemtype']();
                    if ($itemDBTM->getFromDB($app['id'])) {
                        $name = $itemDBTM->getName();
                        $link = $itemDBTM::getFormURLWithID($app['id']);

                        echo "<div style='padding:5px;'>";
                        echo "<a class='list-group-item list-group-item-action' href='$link'>$name";

                        $items = $DB->request([
                            'FROM' => Appliance_Item::getTable(),
                            'WHERE' => [
                                'items_id' => $app['id'],
                                'itemtype' => $app['itemtype'],
                            ],
                        ]);
                        $items = iterator_to_array($items);

                        foreach ($items as $row) {
                            $iterator = $DB->request([
                                'FROM' => Appliance_Item_Relation::getTable(),
                                'WHERE' => [
                                    Appliance_Item::getForeignKeyField() => $row['id'],
                                ],
                            ]);

                            foreach ($iterator as $objrow) {
                                $envtype = $objrow['itemtype'];
                                $env = new $envtype();
                                $env->getFromDB($objrow['items_id']);
                                echo " - " . $env->getName();
                            }
                        }
                        echo "</a>";
                        echo "</div>";
                    }
                } elseif ($item->getType() == "Certificate") {
                    //                    $itemDBTM = new $app['itemtype'];
                    if ($item->getFromDB($app['id'])) {
                        $name = $item->getName();
                        $link = $item::getFormURLWithID($app['id']);
                        echo "<div style='padding:5px;'>";
                        echo "<a class='list-group-item list-group-item-action' href='$link'>$name";
                        echo "</a>";
                        echo "</div>";
                    }
                } else {
                    if ($obj->getFromDB($app['items_id'])) {
                        $name = $obj->getName();
                        $link = $item::getFormURLWithID($app['items_id']);

                        echo "<div style='padding:5px;'>";
                        echo "<a class='list-group-item list-group-item-action' href='$link'>$name";

                        if ($item->getType() == "DatabaseInstance") {
                            $items = $DB->request([
                                'FROM' => Appliance_Item::getTable(),
                                'WHERE' => [
                                    'items_id' => $app['items_id'],
                                    'itemtype' => 'DatabaseInstance',
                                ],
                            ]);
                            $items = iterator_to_array($items);

                            foreach ($items as $row) {
                                $iterator = $DB->request([
                                    'FROM' => Appliance_Item_Relation::getTable(),
                                    'WHERE' => [
                                        Appliance_Item::getForeignKeyField() => $row['id'],
                                    ],
                                ]);

                                foreach ($iterator as $objrow) {
                                    $envtype = $objrow['itemtype'];
                                    $env = new $envtype();
                                    $env->getFromDB($objrow['items_id']);
                                    echo " - " . $env->getName();
                                }
                            }
                        }
                        echo "</a>";
                        echo "</div>";
                    }
                }
            }
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }

    public static function getObjects($item, $ApplianceId)
    {

        if ($item->getType() == "Certificate") {
            $app_item = new Certificate_Item();
            $apps = $app_item->find([
                'items_id' => $ApplianceId,
                'itemtype' => "Appliance",
            ]);
        } else {
            $app_item = new Appliance_Item();
            $apps = $app_item->find([
                'appliances_id' => $ApplianceId,
                'itemtype' => $item->getType(),
            ]);
        }

        $listId = [];
        if ($item->getType() == "Certificate") {
            foreach ($apps as $app) {
                array_push($listId, $app['certificates_id']);
            }
        } else {

            foreach ($apps as $app) {
                array_push($listId, $app['items_id']);
            }
        }

        $list = [];
        if (!empty($listId)) {
            $obj = new $item();
            $list = $obj->find(['id' => $listId]);
        }

        return $list;
    }

    public static function showList($item)
    {
        global $CFG_GLPI;

        echo Html::css(PLUGIN_WEBAPPLICATIONS_WEBDIR . "/lib/jquery-ui/jquery-ui.min.css");
        echo Html::script(PLUGIN_WEBAPPLICATIONS_WEBDIR . "/lib/jquery-ui/jquery-ui.min.js");
        echo Html::css(PLUGIN_WEBAPPLICATIONS_WEBDIR . "/css/webapplications.css");

        echo Html::scriptBlock(
            "function accordion(classname) {
             if(classname == undefined){
                 classname  = 'accordion';
             }
             jQuery(document).ready(function () {
                 $('.'+classname).accordion({
                     collapsible: true,
                     heightStyle: 'content',
                     active: false
                 });
             });
         };"
        );

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;
        ;

        $appliance = new \Appliance();
        $appliance->getFromDB($ApplianceId);

        self::showHeaderDashboard($ApplianceId);

        $object = new $item();

        if ($item->getType() == PhysicalInfrastructure::class) {
            $list = PhysicalInfrastructure::getItems();
        } else {
            $list = self::getObjects($item, $ApplianceId);
        }
        $used = [];

        if (count($list) > 0) {
            foreach ($list as $field) {
                $used[] = $field['id'];
            }
        }

        $title = $object->getTypeName(2);
        self::showTitleforDashboard($title, $ApplianceId, $object, 'add', 'addObject');

        if ($object->getType() == PhysicalInfrastructure::class) {
            echo "<form name='form' method='post' action='"
                . PLUGIN_WEBAPPLICATIONS_WEBDIR . "/front/dashboard.php" . "'>";
        } elseif ($object->getType() == "Certificate") {
            echo "<form name='form' method='post' action='"
                . Toolbox::getItemTypeFormURL('Certificate_Item') . "'>";
        } else {
            echo "<form name='form' method='post' action='"
                . Toolbox::getItemTypeFormURL('Appliance_Item') . "'>";
        }

        echo "<div class='center'><table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='6'>" . __('Add an item') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='center'>";

        if ($object->getType() == PhysicalInfrastructure::class) {
            Dropdown::showSelectItemFromItemtypes(
                [
                    'items_id_name' => 'items_id',
                    'itemtypes' => $CFG_GLPI['inventory_types'],
                    'checkright' => true,
                ]
            );
        } else {
            $class = $object->getType();
            if ($object->getType() == "Certificate") {
                $class::dropdown(['name' => 'certificates_id', 'used' => $used]);
                echo Html::hidden('itemtype', ['value' => 'Appliance']);
            } else {
                $class::dropdown(['name' => 'items_id', 'used' => $used]);
                echo Html::hidden('itemtype', ['value' => $object->getType()]);
            }

        }
        echo "</td>";
        echo "<td class='tab_bg_2 center' colspan='6'>";
        if ($object->getType() == "Certificate") {
            echo Html::hidden('items_id', ['value' => $ApplianceId]);
        } else {
            echo Html::hidden('appliances_id', ['value' => $ApplianceId]);
        }

        if ($object->canCreate()) {
            echo Html::submit(_sx('button', 'Associate'), ['name' => 'add', 'class' => 'btn btn-primary']);
        }
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";

        Html::closeForm();

        $nb = count($list);

        if ($nb > 0) {
            echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
            if ($item->getType() == Entity::class) {
                echo _n("Entity list", "Entities list", $nb, 'webapplications');
            } elseif ($item->getType() == Process::class) {
                echo _n("Process list", "Processes list", $nb, 'webapplications');
            } elseif ($item->getType() == PhysicalInfrastructure::class) {
                echo _n("Item list", "Items list", $nb, 'webapplications');
            } elseif ($item->getType() == "DatabaseInstance") {
                echo _n("Database list", "Databases list", $nb, 'webapplications');
            } elseif ($item->getType() == "Certificate") {
                echo _n("Certificate list", "Certificates list", $nb, 'webapplications');
            } elseif ($item->getType() == Stream::class) {
                echo _n("Stream list", "Streams list", $nb, 'webapplications');
            }
            echo "</h2>";
        }

        if (empty($list)) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tbody>";
            echo "<tr class='center'>";
            echo "<td colspan='4'>";
            echo __("No associated objects", 'webapplications');
            echo "</td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";
        } else {
            if ($item->getType() == "DatabaseInstance") {
                DatabaseInstance::showListObjects($list);
            } elseif ($item->getType() == "Certificate") {
                Certificate::showListObjects($list);
            } else {
                $item::showListObjects($list);
            }
        }
    }
}
