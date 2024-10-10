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
            $nbProcess = count(self::getProcesses());
            return self::createTabEntry(self::getTypeName($nbProcess), $nbProcess);
        }
        return _n('Process', 'Processes', 2, 'webapplications');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);


        TemplateRenderer::getInstance()->display('@webapplications/webapplication_process_form.html.twig', [
            'item' => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function post_getEmpty()
    {
        $this->fields["webapplicationconfidentialities"] = 0;
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

    public static function showProcessFromDashboard($appliance)
    {
        echo "<div class='card-body'>";

        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>" . __(
                'Process',
                'webapplications'
            ) . "</h2>";


        $ApplianceId = $appliance->getField('id');

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsProcess']
        );
        $processDBTM = new PluginWebapplicationsProcess();

        echo "<div class='row flex-row'>";
        echo "<div class='form-field row col-12 col-sm-12  mb-2'>";

        echo "<label class='col-form-label col-xxl-5 text-xxl-end'>";
        echo __('Processes list', 'webapplications');
        echo "</label>";

        echo "<div class='col-xxl-7 field-container'>";
        if (!empty($procsApp)) {
            echo "<select name='processes' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($procsApp as $procApp) {
                if ($processDBTM->getFromDB($procApp['items_id'])) {
                    $name = $processDBTM->getName();
                    $link = PluginWebapplicationsProcess::getFormURLWithID($procApp['items_id']);
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

    public static function getProcesses()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find([
            'appliances_id' => $ApplianceId,
            'itemtype' => 'PluginWebapplicationsProcess'
        ]);


        $listProcId = [];
        foreach ($procsApp as $proc) {
            array_push($listProcId, $proc['items_id']);
        }

        $listProcesses = [];
        if (!empty($listProcId)) {
            $processDBTM = new PluginWebapplicationsProcess();
            $listProcesses = $processDBTM->find(['id' => $listProcId]);
        }
        return $listProcesses;
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


        $processDBTM = new PluginWebapplicationsProcess();
        $linkAddProc = $processDBTM::getFormURL();

        $listProc = self::getProcesses();

        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>";
        echo _n('Process', 'Processes', 2, 'webapplications');

        echo "<span style='float: right'>";
        echo Html::submit(_sx('button', 'Add'), [
            'name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#addProc',
            'style' => 'float: right'
        ]);
        echo Ajax::createIframeModalWindow(
            'addProc',
            $linkAddProc . "?appliance_id=" . $ApplianceId,
            [
                'display' => false,
                'reloadonclose' => true
            ]
        );
        echo "</span>";
        echo "</h2>";

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
        echo _n("Process list", "Processes list", count($listProc), 'webapplications');
        echo "</h2>";



        if (empty($listProc)) {

            echo "<table class='tab_cadre_fixe'>";
            echo "<tbody>";
            echo "<tr class='center'>";
            echo "<td colspan='4'>";
            echo __("No associated processes", 'webapplications');
            echo "</td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";

        } else {

            echo "<div class='accordion' name=listProcessesApp>";

            foreach ($listProc as $process) {
                $name = $process['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                $linkProc = PluginWebapplicationsProcess::getFormURLWithID($process['id']);
                $linkProc .= "&forcetab=main";

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo "<a href=$linkProc>$name</a>";
                echo "</td>";

                echo "<td style='width: 10%'>";
                echo Html::submit(
                    _sx('button', 'Edit'),
                    [
                        'name' => 'edit',
                        'class' => 'btn btn-secondary',
                        'icon' => 'fas fa-edit',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#editProcess' . $process['id']
                    ]
                );

                echo Ajax::createIframeModalWindow(
                    'editProcess' . $process['id'],
                    $linkProc,
                    [
                        'display' => false,
                        'reloadonclose' => true
                    ]
                );
                echo "</td>";

                echo "</tr>";

                $ownerid = $process['owner'];
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
                if ($ownerid > 0) {
                    echo "<a href=$linkOwner>$ownerName</a>";
                } else {
                    echo $ownerName;
                }
                echo "</td>";
                echo "</tr>";


                $processEntityDBTM = new PluginWebapplicationsProcess_Entity();
                $entities = $processEntityDBTM->find(['plugin_webapplications_processes_id' => $process['id']]);
                $entityDBTM = new PluginWebapplicationsEntity();

                echo "<tr>";
                echo "<th>";
                echo _n("Entity list", "Entities list", 2, 'webapplications');
                echo "</th>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td>";
                echo "<td>";
                if (!empty($entities)) {
                    echo "<select name='entities' id='list' Size='3' ondblclick='location = this.value;'>";
                    foreach ($entities as $entity) {
                        $entityDBTM->getFromDB($entity['plugin_webapplications_entities_id']);
                        $name = $entityDBTM->getName();
                        $link = PluginWebapplicationsEntity::getFormURLWithID(
                            $entity['plugin_webapplications_entities_id']
                        );
                        echo "<option value=$link>$name</option>";
                    }
                    echo "</select>";
                } else {
                    echo __("No associated entity", 'webapplications');
                }
                echo "</td>";
                echo "</tr>";


                $applianceItemDBTM = new Appliance_Item();
                $appliances = $applianceItemDBTM->find(
                    ['items_id' => $process['id'], 'itemtype' => 'PluginWebapplicationsProcess']
                );
                $applianceDBTM = new Appliance();

                echo "<tr>";
                echo "<th>";
                echo __('Appliances list', 'webapplications');
                echo "</th>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td>";
                echo "<td>";
                if (!empty($appliances)) {
                    echo "<select name='appliances' id='list' Size='3' ondblclick='location = this.value;'>";
                    foreach ($appliances as $appliance) {
                        $applianceDBTM->getFromDB($appliance['appliances_id']);
                        $name = $applianceDBTM->getName();
                        $link = Appliance::getFormURLWithID($appliance['appliances_id']);
                        echo "<option value=$link>$name</option>";
                    }
                    echo "</select>";
                } else {
                    echo __("No associated entity", 'webapplications');
                }
                echo "</td>";
                echo "</tr>";


                $disp = $process['webapplicationavailabilities'];
                $int = $process['webapplicationintegrities'];
                $conf = $process['webapplicationconfidentialities'];
                $tra = $process['webapplicationtraceabilities'];


                echo "<tr>";
                echo "<th style='padding-top: 20px; padding-bottom: 20px'>";
                echo __('DICT', 'webapplications');
                echo "</th>";
                echo "<td class='inTable'>";

                echo "<table style='text-align : center; width: 60%'>";

                echo "<td class='dict'>";
                echo __('Availability', 'webapplications') . "&nbsp";
                echo "</td>";

                echo "<td name='webapplicationavailabilities' id='5'>";
                echo $disp;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo __('Integrity', 'webapplications') . "&nbsp";
                echo "</td>";
                echo "<td name='webapplicationintegrities' id='6'>";
                echo $int;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo __('Confidentiality', 'webapplications') . "&nbsp";
                echo "</td>";
                echo "<td name='webapplicationconfidentialities' id='7'>";
                echo $conf;
                echo "</td>";

                echo "<td></td>";

                echo "<td class='dict'>";
                echo __('Traceability', 'webapplications') . "&nbsp";
                echo "</td>";
                echo "<td name='webapplicationtraceabilities' id='8'>";
                echo $tra;
                echo "</td>";

                echo "</table>";

                echo "</td>";
                echo "</tr>";

                $comment = $process['comment'];

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo __('Comment');
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table></div>";
            }
            echo "</div>";

            echo "<script>accordion();</script>";
        }


    }
}
