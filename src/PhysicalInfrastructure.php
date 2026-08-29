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

use Appliance_Item;
use Appliance_Item_Relation;
use CommonDBTM;
use CommonGLPI;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;
use IPAddress;
use Item_OperatingSystem;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class PhysicalInfrastructure
 */
class PhysicalInfrastructure extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";

    public static function getTypeName($nb = 0)
    {
        return __('Physical Infrastructure', 'webapplications');
    }

    public static function getIcon()
    {
        return "ti ti-server";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = count(self::getItems());
            return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new self();
        Dashboard::showList($obj);
    }

    public static function getItems()
    {
        global $CFG_GLPI;
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;

        $itemsAppDBTM = new Appliance_Item();

        $itemApp = $itemsAppDBTM->find([
            'appliances_id' => $ApplianceId,
            'itemtype' => $CFG_GLPI['inventory_types'],
        ], 'itemtype');

        $listItem = [];
        foreach ($itemApp as $st) {

            $itemDBTM = new $st['itemtype']();
            if ($itemDBTM->getFromDB($st['items_id'])) {
                $item = ['id' => $st['items_id'],'name' => $itemDBTM->fields['name'], 'itemtype' => $st['itemtype']];
                array_push($listItem, $item);
            }
        }
        usort($listItem, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        return $listItem;
    }

    public static function showListObjects($list)
    {
        global $DB;

        $list_by_itemtypes = [];
        foreach ($list as $item) {
            $list_by_itemtypes[$item['itemtype']][] =  $item['id'];
        }

        foreach ($list_by_itemtypes as $itemtype => $items) {

            $object = new $itemtype();
            $cards = [];

            $card_icon = "ti-server";
            if ($itemtype == "Phone") {
                $card_icon = "ti-phone";
            } elseif ($itemtype == "Printer") {
                $card_icon = "ti-printer";
            } elseif ($itemtype == "NetworkEquipment") {
                $card_icon = "ti-router";
            }

            foreach ($items as $items_id) {

                $object->getFromDB($items_id);
                $id = $items_id;

                $delete_html = Html::getSimpleForm(
                    PLUGIN_WEBAPPLICATIONS_WEBDIR . "/front/dashboard.php",
                    'reset',
                    __('Delete'),
                    ['items_id' => $id, 'itemtype' => $itemtype],
                    'ti-circle-x',
                );

                $blocks = [];

                $relations = $DB->request([
                    'FROM'   => Appliance_Item::getTable(),
                    'WHERE'  => [
                        'items_id' => $items_id,
                        'itemtype' => $itemtype,
                    ],
                ]);
                $relations = iterator_to_array($relations);

                $env_lines = [];
                foreach ($relations as $row) {
                    $iterator = $DB->request([
                        'FROM'   => Appliance_Item_Relation::getTable(),
                        'WHERE'  => [
                            Appliance_Item::getForeignKeyField() => $row['id'],
                        ],
                    ]);

                    foreach ($iterator as $row) {
                        $envtype = $row['itemtype'];
                        $env = new $envtype();
                        $env->getFromDB($row['items_id']);
                        // Icon class is DB data escaped by Twig; getLink() returns trusted framework markup.
                        $env_lines[] = [
                            'icon' => $env->getIcon(),
                            'link' => $env->getLink(),
                        ];
                    }
                }
                if (!empty($env_lines)) {
                    $blocks[] = [
                        'kind'  => 'links',
                        'lines' => $env_lines,
                    ];
                }

                if ($itemtype == "Computer") {
                    $blocks[] = [
                        'kind'  => 'text',
                        'value' => Dropdown::getDropdownName("glpi_computertypes", $object->fields['computertypes_id']),
                    ];
                } elseif ($itemtype == "NetworkEquipment") {
                    $blocks[] = [
                        'kind'  => 'text',
                        'value' => Dropdown::getDropdownName("glpi_networkequipmenttypes", $object->fields['networkequipmenttypes_id']),
                    ];
                }

                if ($itemtype == "Computer") {
                    $iterator = Item_OperatingSystem::getFromItem($object);
                    $os_lines = [];
                    foreach ($iterator as $row) {
                        $os_lines[] = [
                            'name'         => $row['name'],
                            'version'      => $row['version'],
                            'architecture' => $row['architecture'],
                        ];
                    }
                    if (!empty($os_lines)) {
                        $blocks[] = [
                            'kind'  => 'os',
                            'lines' => $os_lines,
                        ];
                    }
                }

                $ips = [];
                $ip  = new IPAddress();
                foreach ($DB->request(['FROM' => 'glpi_networkports', 'WHERE' => ['itemtype' => $itemtype,
                    'items_id' => $items_id]]) as $netname) {
                    foreach ($DB->request(['FROM' => 'glpi_networknames', 'WHERE' => ['itemtype' => 'NetworkPort',
                        'items_id' => $netname['id']]]) as $dataname) {
                        foreach ($DB->request(['FROM' => 'glpi_ipaddresses', 'WHERE' => ['itemtype' => 'NetworkName',
                            'items_id' => $dataname['id']]]) as $data) {
                            $ip->getFromDB($data['id']);

                            if ($ip->getName() != "127.0.0.1" && $ip->fields['version'] != 6) {
                                $ips[] = $ip->getName();
                            }
                        }
                    }
                }
                if (!empty($ips)) {
                    $blocks[] = [
                        'kind' => 'iplist',
                        'ips'  => $ips,
                    ];
                }

                $cards[] = [
                    'width_class'   => 'w-25',
                    'icon'          => 'ti ' . $card_icon,
                    'icon_size'     => '3em',
                    'top_right_html' => $delete_html,
                    'title_html'    => $object->getLink(),
                    'blocks'        => $blocks,
                    'edit_html'     => Dashboard::getCardEditHtml($object, (int) $id),
                ];
            }

            TemplateRenderer::getInstance()->display('@webapplications/webapplication_object_cards.html.twig', [
                'group_header' => true,
                'group_icon'   => $object->getIcon(),
                'group_title'  => $object->getTypeName(2),
                'cards'        => $cards,
            ]);
        }
    }
}
