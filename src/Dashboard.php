<?php

/**
 * -------------------------------------------------------------------------
 * webapplications plugin for GLPI
 * Copyright (C) 2015-2026 by the webapplications Development Team.
 *
 * https://github.com/InfotelGLPI/webapplications
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of webapplications.
 *
 * webapplications is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * webapplications is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
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
use Glpi\Exception\Http\AccessDeniedHttpException;
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
            $options,
        );
        $this->addStandardTab(
            LogicalInfrastructure::class,
            $ong,
            $options,
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

        $rand = mt_rand();
        $dropdown_html = \Appliance::dropdown([
            'name'    => 'applianceDropdown',
            'value'   => $id,
            'rand'    => $rand,
            'display' => false,
        ]);

        $list_html = "";
        if ($id > 0) {
            $dashboard = new self();
            ob_start();
            $dashboard->display(['id' => 1, 'appliances_id' => $id]);
            $list_html = ob_get_clean();
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_select.html.twig', [
            'dropdown_html' => $dropdown_html,
            'list_html'     => $list_html,
        ]);

        $array['value'] = '__VALUE__';
        $array['type'] = self::getType();
        $array['reload'] = false;
        Ajax::updateItemOnSelectEvent(
            'dropdown_applianceDropdown' . $rand,
            'lists-dashboard',
            $CFG_GLPI['root_doc'] . PLUGIN_WEBAPPLICATIONS_WEBDIR . '/ajax/getLists.php',
            $array,
        );
    }

    public function showForm($ID, $options = [])
    {
        echo Html::css(PLUGIN_WEBAPPLICATIONS_WEBDIR . "/css/webapplications.css");

        $options['candel'] = false;
        $options['colspan'] = 1;

        $ApplianceId = (int) $options['appliances_id'];

        $appliance = new \Appliance();
        // The appliance id comes from user-controlled input ($_POST['value']); enforce
        // object-level right + entity access instead of loading it blindly.
        if (!$appliance->can($ApplianceId, READ)) {
            throw new AccessDeniedHttpException();
        }

        self::showHeaderDashboard($ApplianceId);

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
        $number_users = $applianceplugin->fields['number_users'] ?? 0;

        // Capture the sub-renderers (GLPI helpers / already-migrated methods) so the
        // page structure lives in the Twig template instead of raw echoes.
        ob_start();
        Appliance::showSupportPartFromDashboard($appliance);
        Appliance::showDocumentsAndContractsFromDashboard($appliance);
        Knowbase::showFromDashboard($appliance);
        $support_html = ob_get_clean();

        ob_start();
        self::showTitleforDashboard(__('Summary', 'webapplications'), $ApplianceId, $applianceplugin, "edit", "editapp");
        $summary_title_html = ob_get_clean();

        $summaryOptions = [];
        $summaryOptions['canedit'] = false;
        $summaryOptions['candel'] = false;
        ob_start();
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_summary.html.twig', [
            'item' => $appliance,
            'params' => $summaryOptions,
            'no_header' => true,
        ]);
        $summary_html = ob_get_clean();

        ob_start();
        self::showFromDashboard($appliance, new Entity());
        self::showFromDashboard($appliance, new Process());
        self::showFromDashboard($appliance, new PhysicalInfrastructure());
        self::showFromDashboard($appliance, new \DatabaseInstance());
        self::showFromDashboard($appliance, new \Certificate());
        self::showFromDashboard($appliance, new Stream());
        $objects_html = ob_get_clean();

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_main.html.twig', [
            'users_label'        => _n('User', 'Users', 2),
            'users_value'        => Appliance::getNbUsersValue($number_users),
            'leader_label'       => __('Project leader', 'webapplications'),
            'leader_name'        => getUserName($userAdminId),
            'team_label'         => __('Project team', 'webapplications'),
            'team_number'        => $numberAdmin,
            'support_html'       => $support_html,
            'summary_title_html' => $summary_title_html,
            'summary_html'       => $summary_html,
            'objects_html'       => $objects_html,
        ]);
    }

    //0296333734

    public static function showHeaderDashboard($ApplianceId)
    {
        $appliance = new \Appliance();
        $appliance->getFromDB($ApplianceId);

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_header.html.twig', [
            'icon'      => $appliance->getIcon(),
            'link_url'  => \Appliance::getFormURLWithID($appliance->getID()),
            'link_html' => $appliance->getLink(),
        ]);
    }

    public static function showTitleforDashboard($title, $id, $item = false, $type = "add", $name = "")
    {
        $icon = "";
        if ($item != false && $id > 0) {
            if ($item->getType() == "Contract_Item") {
                $icon = Contract::getIcon();
            } else {
                $icon = $item->getIcon();
            }
        }

        $action_html = "";
        if ($item != false && $id > 0 && $name != "") {
            if ($type == "add") {
                if ($item->getType() == "Supplier") {
                    $linkApp = PLUGIN_WEBAPPLICATIONS_WEBDIR . '/front/supplier.form.php';
                } else {
                    $linkApp = $item::getFormURL();
                }

                $btntitle = _sx('button', 'Add');
            } else {
                if ($item->getType() == "Supplier") {
                    $linkApp = PLUGIN_WEBAPPLICATIONS_WEBDIR . '/front/supplier.form.php?id=' . $id;
                } else {
                    $linkApp = $item::getFormURLWithID($id);
                }

                $linkApp .= "&forcetab=main";
                $btntitle = _sx('button', 'Edit');
            }

            $rand = mt_rand();
            if ($item->getType() != "DatabaseInstance"
                && $item->getType() != PhysicalInfrastructure::class
                && $item->canUpdate()) {
                $action_html = Html::submit(
                    $btntitle,
                    [
                        'name' => 'edit',
                        'class' => 'btn btn-secondary',
                        'icon' => 'ti ti-edit',
                        'style' => 'float: right',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#' . $name . $id . $rand,
                    ],
                );

                $action_html .= Ajax::createIframeModalWindow(
                    $name . $id . $rand,
                    $linkApp,
                    [
                        'display' => false,
                        'reloadonclose' => true,
                    ],
                );
            }
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_title.html.twig', [
            'icon'        => $icon,
            'title'       => $title,
            'action_html' => $action_html,
        ]);
    }

    public static function showFromDashboard($appliance, $item)
    {
        global $DB;

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

        ob_start();
        self::showTitleforDashboard($title, $ApplianceId, $item);
        $title_html = ob_get_clean();

        $obj = new $item();

        $entries = [];
        if (!empty($apps)) {
            foreach ($apps as $app) {
                if ($item->getType() == PhysicalInfrastructure::class) {
                    $itemDBTM = new $app['itemtype']();
                    if ($itemDBTM->getFromDB($app['id'])) {
                        $label = $itemDBTM->getName();
                        $url = $itemDBTM::getFormURLWithID($app['id']);
                        $label .= self::getRelatedEnvironmentsLabel($app['id'], $app['itemtype']);
                        $entries[] = ['url' => $url, 'label' => $label];
                    }
                } elseif ($item->getType() == "Certificate") {
                    if ($item->getFromDB($app['id'])) {
                        $entries[] = [
                            'url'   => $item::getFormURLWithID($app['id']),
                            'label' => $item->getName(),
                        ];
                    }
                } else {
                    if ($obj->getFromDB($app['items_id'])) {
                        $label = $obj->getName();
                        $url = $item::getFormURLWithID($app['items_id']);
                        if ($item->getType() == "DatabaseInstance") {
                            $label .= self::getRelatedEnvironmentsLabel($app['items_id'], 'DatabaseInstance');
                        }
                        $entries[] = ['url' => $url, 'label' => $label];
                    }
                }
            }
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_from.html.twig', [
            'title_html' => $title_html,
            'entries'    => $entries,
        ]);
    }

    /**
     * Build the " - env1 - env2" suffix listing the environments related to an
     * appliance item, as plain text (escaping is handled by the template).
     *
     * @param int    $items_id
     * @param string $itemtype
     *
     * @return string
     */
    private static function getRelatedEnvironmentsLabel($items_id, $itemtype): string
    {
        global $DB;

        $label = "";
        $items = $DB->request([
            'FROM' => Appliance_Item::getTable(),
            'WHERE' => [
                'items_id' => $items_id,
                'itemtype' => $itemtype,
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
                $label .= " - " . $env->getName();
            }
        }

        return $label;
    }

    /**
     * Build the "Edit" button and its iframe modal window for an object card.
     * Returns an empty string when the current user cannot update the object.
     *
     * @param \CommonDBTM $object
     * @param int         $id
     *
     * @return string
     */
    public static function getCardEditHtml($object, int $id): string
    {
        if (!$object->canUpdate()) {
            return '';
        }
        $rand = mt_rand();
        $link = $object::getFormURLWithID($id) . "&forcetab=main";
        $html = Html::submit(
            _sx('button', 'Edit'),
            [
                'name'           => 'edit',
                'class'          => 'btn btn-secondary right',
                'icon'           => 'ti ti-edit',
                'form'           => '',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#edit' . $id . $rand,
            ],
        );
        $html .= Ajax::createIframeModalWindow(
            'edit' . $id . $rand,
            $link,
            [
                'display'       => false,
                'reloadonclose' => true,
            ],
        );
        return $html;
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

        echo Html::css(PLUGIN_WEBAPPLICATIONS_WEBDIR . "/css/webapplications.css");

        $ApplianceId = (int) ($_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0);

        $appliance = new \Appliance();
        // Defense in depth: this id is read from session (set from user input in
        // ajax/getLists.php). When an appliance is actually selected, re-validate
        // object-level right + entity access here so a tab render can never expose an
        // appliance the user has no access to. can() loads the record on success.
        if ($ApplianceId > 0) {
            if (!$appliance->can($ApplianceId, READ)) {
                throw new AccessDeniedHttpException();
            }
        } else {
            $appliance->getFromDB($ApplianceId);
        }

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
            $form_action = PLUGIN_WEBAPPLICATIONS_WEBDIR . "/front/dashboard.php";
        } elseif ($object->getType() == "Certificate") {
            $form_action = Toolbox::getItemTypeFormURL('Certificate_Item');
        } else {
            $form_action = Toolbox::getItemTypeFormURL('Appliance_Item');
        }

        // Build the "add an item" dropdown cell content.
        ob_start();
        if ($object->getType() == PhysicalInfrastructure::class) {
            Dropdown::showSelectItemFromItemtypes(
                [
                    'items_id_name' => 'items_id',
                    'itemtypes' => $CFG_GLPI['inventory_types'],
                    'checkright' => true,
                ],
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
        $dropdown_html = ob_get_clean();

        if ($object->getType() == "Certificate") {
            $hidden_html = Html::hidden('items_id', ['value' => $ApplianceId]);
        } else {
            $hidden_html = Html::hidden('appliances_id', ['value' => $ApplianceId]);
        }

        $nb = count($list);
        $list_title = "";
        if ($nb > 0) {
            if ($item->getType() == Entity::class) {
                $list_title = _n("Entity list", "Entities list", $nb, 'webapplications');
            } elseif ($item->getType() == Process::class) {
                $list_title = _n("Process list", "Processes list", $nb, 'webapplications');
            } elseif ($item->getType() == PhysicalInfrastructure::class) {
                $list_title = _n("Item list", "Items list", $nb, 'webapplications');
            } elseif ($item->getType() == "DatabaseInstance") {
                $list_title = _n("Database list", "Databases list", $nb, 'webapplications');
            } elseif ($item->getType() == "Certificate") {
                $list_title = _n("Certificate list", "Certificates list", $nb, 'webapplications');
            } elseif ($item->getType() == Stream::class) {
                $list_title = _n("Stream list", "Streams list", $nb, 'webapplications');
            }
        }

        $objects_html = "";
        if (!empty($list)) {
            ob_start();
            if ($item->getType() == "DatabaseInstance") {
                DatabaseInstance::showListObjects($list);
            } elseif ($item->getType() == "Certificate") {
                Certificate::showListObjects($list);
            } else {
                $item::showListObjects($list);
            }
            $objects_html = ob_get_clean();
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_list.html.twig', [
            'form_action'   => $form_action,
            'add_title'     => __('Add an item'),
            'dropdown_html' => $dropdown_html,
            'hidden_html'   => $hidden_html,
            'can_create'    => $object->canCreate(),
            'list_title'    => $list_title,
            'is_empty'      => empty($list),
            'empty_message' => __("No associated objects", 'webapplications'),
            'objects_html'  => $objects_html,
        ]);
    }
}
