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
 * Class PluginWebapplicationsProcess
 */
class PluginWebapplicationsProcess extends CommonDBTM
{
    use Glpi\Features\Inventoriable;

    public static $rightname = "plugin_webapplications_processes";

    public static function getTypeName($nb = 0)
    {
        return _n('Process', 'Processes', $nb, 'webapplications');
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
        return "fas fa-cogs";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;;
            $self = new self();
            $nb = count(PluginWebapplicationsDashboard::getObjects($self, $ApplianceId));
            return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new self();
        PluginWebapplicationsDashboard::showList($obj);
        return true;
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);

        $options['appliances_id'] = $_SESSION['plugin_webapplications_loaded_appliances_id'];
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_process_form.html.twig', [
            'item' => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function post_getEmpty()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;;
        if ($ApplianceId > 0) {
            $webs = getAllDataFromTable(
                PluginWebapplicationsAppliance::getTable(),
                [
                    'WHERE' => [
                        'appliances_id' => $ApplianceId,
                    ]
                ]
            );

            foreach ($webs as $web) {
                $this->fields["webapplicationavailabilities"] = $web["webapplicationavailabilities"];
                $this->fields["webapplicationintegrities"] = $web["webapplicationintegrities"];
                $this->fields["webapplicationconfidentialities"] = $web["webapplicationconfidentialities"];
                $this->fields["webapplicationtraceabilities"] = $web["webapplicationtraceabilities"];
            }
        }

    }

    public function prepareInputForAdd($input)
    {
        if (isset($input['appliances_id'])
            && !empty($input['appliances_id'])) {
            $item = new Appliance();
            if ($item->getFromDB($input['appliances_id'])) {
                $input['entities_id'] = $item->fields['entities_id'];
                $input['is_recursive'] = $item->fields['is_recursive'];
            }
        }
        return $input;
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
                    'itemtype' => 'PluginWebapplicationsProcess'
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
            'table' => self::getTable(),
            'field' => 'comment',
            'name' => __('Comments'),
            'datatype' => 'text'
        ];

        $tab[] = [
            'id' => '3',
            'table' => User::getTable(),
            'field' => 'name',
            'linkfield' => 'owner',
            'name' => __('Owner', 'webapplications'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '4',
            'table' => self::getTable(),
            'field' => 'webapplicationavailabilities',
            'name' => __('Availability', 'webapplications'),
            'datatype' => 'dropdown'
        ];
        $tab[] = [
            'id' => '5',
            'table' => self::getTable(),
            'field' => 'webapplicationintegrities',
            'name' => __('Integrity', 'webapplications'),
            'datatype' => 'dropdown'
        ];
        $tab[] = [
            'id' => '6',
            'table' => self::getTable(),
            'field' => 'webapplicationconfidentialities',
            'name' => __('Confidentiality', 'webapplications'),
            'datatype' => 'dropdown'
        ];
        $tab[] = [
            'id' => '7',
            'table' => self::getTable(),
            'field' => 'webapplicationtraceabilities',
            'name' => __('Traceability', 'webapplications'),
            'datatype' => 'dropdown'
        ];


        return $tab;
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        $this->addStandardTab('PluginWebapplicationsProcess_Entity', $ong, $options);
        return $ong;
    }

    public static function showListObjects($list)
    {
        $object = new self();

        echo "<div style='display: flex;flex-wrap: wrap;'>";

        foreach ($list as $field) {
            $name = $field['name'];
            $id = $field['id'];
            $object->getFromDB($id);

            echo "<div class='card w-33' style='margin-right: 10px;margin-top: 10px;'>";
            echo "<div class='card-body'>";
            echo "<div style='display: inline-block;margin: 40px;'>";
            echo "<i class='fa-5x fas ".self::getIcon()."'></i>";
            echo "</div>";
            echo "<div style='display: inline-block;';>";
            echo "<h5 class='card-title' style='font-size: 14px;'>" . $object->getLink() . "</h5>";
            if ($object->fields['owner'] > 0) {
                echo "<p class='card-text'>";
                echo __('Owner', 'webapplications')." : ".getUserName($object->fields['owner']);
                echo "</p>";
            }
            echo "<p class='card-text'>";
            $background = PluginWebapplicationsAppliance::getColorForDICT($object->fields['webapplicationavailabilities']);
            echo "<span class='dict-min' style='background-color:$background' title='".__('Availability', 'webapplications')."'>";
            echo $object->fields['webapplicationavailabilities'];
            echo "</span>";

            $background = PluginWebapplicationsAppliance::getColorForDICT($object->fields['webapplicationintegrities']);
            echo "<span class='dict-min' style='background-color:$background' title='".__('Integrity', 'webapplications')."'>";
            echo $object->fields['webapplicationintegrities'];
            echo "</span>";

            $background = PluginWebapplicationsAppliance::getColorForDICT($object->fields['webapplicationconfidentialities']);
            echo "<span class='dict-min' style='background-color:$background' title='".__('Confidentiality', 'webapplications')."'>";
            echo $object->fields['webapplicationconfidentialities'];
            echo "</span>";

            $background = PluginWebapplicationsAppliance::getColorForDICT($object->fields['webapplicationtraceabilities']);
            echo "<span class='dict-min' style='background-color:$background' title='".__('Traceability', 'webapplications')."'>";
            echo $object->fields['webapplicationtraceabilities'];
            echo "</span>";
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
