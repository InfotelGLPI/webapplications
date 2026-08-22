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
use CommonDBTM;
use CommonGLPI;
use Database;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Webapplications\Appliance;
use Html;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class DatabaseInstance
 */
class DatabaseInstance extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";

    public static function getTypeName($nb = 0)
    {
        return _n('Database', 'Databases', $nb);
    }

    public static function getIcon()
    {
        return "ti ti-database-import";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;
            $self = new \DatabaseInstance();
            $nb = count(Dashboard::getObjects($self, $ApplianceId));
            return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new \DatabaseInstance();
        Dashboard::showList($obj);
        return true;
    }

    /**
     * @param $params
     */
    //    public static function addFields($params)
    //    {
    //        $item = $params['item'];
    //        $webapp_database = new self();
    //        if ($item->getType() == 'DatabaseInstance') {
    //            if ($item->getID()) {
    //                $webapp_database->getFromDBByCrit(['databaseinstances_id' => $item->getID()]);
    //            } else {
    //                $webapp_database->getEmpty();
    //            }
    //
    //            $options = [];
    //
    //            if (isset($params["options"]["appliances_id"])) {
    //                $options = ['appliances_id' => $params["options"]["appliances_id"]];
    //            }
    //
    //            TemplateRenderer::getInstance()->display('@webapplications/webapplication_database_form.html.twig', [
    //                'item' => $webapp_database,
    //                'params' => $options,
    //            ]);
    //        }
    //        return true;
    //    }

    public function showForm($ID, $options = [])
    {
        $instance = new \DatabaseInstance();
        $instance->showForm($ID, $options);

        return true;
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        $items_id = $this->input['databaseinstances_id'];
        if (isset($appliance_id) && !empty($appliance_id)) {
            $itemDBTM = new Appliance_Item();
            $data = $itemDBTM->find([
                'appliances_id' => $appliance_id,
                'items_id' => $items_id,
                'itemtype' => 'DatabaseInstance',
            ]);

            if (count($data) == 0) {
                $itemDBTM->add([
                    'appliances_id' => $appliance_id,
                    'items_id' => $items_id,
                    'itemtype' => 'DatabaseInstance',
                ]);
            }
        }
    }

    /**
     * @param \Database $item
     *
     * @return false
     */
    public static function databaseAdd(\DatabaseInstance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setDatabase($item);
    }

    /**
     * @param \Database $item
     *
     * @return false
     */
    public static function databaseLink(Appliance_Item $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        if (!empty($item->input) && $item->input['itemtype'] == 'DatabaseInstance') {
            $database = new DatabaseInstance();
            $database->getFromDBByCrit(['databaseinstances_id' => $item->input['items_id']]);
            if (is_array($database->fields) && count($database->fields) > 0) {
                $webs = getAllDataFromTable(
                    \Appliance::getTable(),
                    [
                        'WHERE' => [
                            'appliances_id' => $item->input['appliances_id'],
                        ],
                    ],
                );
                foreach ($webs as $web) {
                    $item->input["webapplicationexternalexpositions_id"] = $web["webapplicationexternalexpositions_id"];
                    $item->input["webapplicationavailabilities"] = $web["webapplicationavailabilities"];
                    $item->input["webapplicationintegrities"] = $web["webapplicationintegrities"];
                    $item->input["webapplicationconfidentialities"] = $web["webapplicationconfidentialities"];
                    $item->input["webapplicationtraceabilities"] = $web["webapplicationtraceabilities"];
                }

                $webapplicationexternalexpositions_id = 0;
                if (isset($item->input['webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $item->input['webapplicationexternalexpositions_id'];
                }

                $webapplicationavailabilities = 0;
                if (isset($item->input['webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $item->input['webapplicationavailabilities'];
                }

                $webapplicationintegrities = 0;
                if (isset($item->input['webapplicationintegrities'])) {
                    $webapplicationintegrities = $item->input['webapplicationintegrities'];
                }

                $webapplicationconfidentialities = 0;
                if (isset($item->input['webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $item->input['webapplicationconfidentialities'];
                }

                $webapplicationtraceabilities = 0;
                if (isset($item->input['webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $item->input['webapplicationtraceabilities'];
                }

                $database->update([
                    'webapplicationexternalexpositions_id' => $webapplicationexternalexpositions_id,
                    'webapplicationavailabilities' => $webapplicationavailabilities,
                    'webapplicationintegrities' => $webapplicationintegrities,
                    'webapplicationconfidentialities' => $webapplicationconfidentialities,
                    'webapplicationtraceabilities' => $webapplicationtraceabilities,
                    'appliances_id' => isset($item->input['appliances_id']) ? $item->input['appliances_id'] : 0,
                    'databaseinstances_id' => $item->input['items_id'],
                ]);
            } else {
                $webs = getAllDataFromTable(
                    Appliance::getTable(),
                    [
                        'WHERE' => [
                            'appliances_id' => $item->input['appliances_id'],
                        ],
                    ],
                );
                foreach ($webs as $web) {
                    $item->input["webapplicationexternalexpositions_id"] = $web["webapplicationexternalexpositions_id"];
                    $item->input["webapplicationavailabilities"] = $web["webapplicationavailabilities"];
                    $item->input["webapplicationintegrities"] = $web["webapplicationintegrities"];
                    $item->input["webapplicationconfidentialities"] = $web["webapplicationconfidentialities"];
                    $item->input["webapplicationtraceabilities"] = $web["webapplicationtraceabilities"];
                }

                $webapplicationexternalexpositions_id = 0;
                if (isset($item->input['webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $item->input['webapplicationexternalexpositions_id'];
                }

                $webapplicationavailabilities = 0;
                if (isset($item->input['webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $item->input['webapplicationavailabilities'];
                }

                $webapplicationintegrities = 0;
                if (isset($item->input['webapplicationintegrities'])) {
                    $webapplicationintegrities = $item->input['webapplicationintegrities'];
                }

                $webapplicationconfidentialities = 0;
                if (isset($item->input['webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $item->input['webapplicationconfidentialities'];
                }

                $webapplicationtraceabilities = 0;
                if (isset($item->input['webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $item->input['webapplicationtraceabilities'];
                }

                $database->add([
                    'webapplicationexternalexpositions_id' => $webapplicationexternalexpositions_id,
                    'webapplicationavailabilities' => $webapplicationavailabilities,
                    'webapplicationintegrities' => $webapplicationintegrities,
                    'webapplicationconfidentialities' => $webapplicationconfidentialities,
                    'webapplicationtraceabilities' => $webapplicationtraceabilities,
                    'appliances_id' => isset($item->input['appliances_id']) ? $item->input['appliances_id'] : 0,
                    'databaseinstances_id' => $item->input['items_id'],
                ]);
            }
        }
    }

    /**
     * @param \Database $item
     *
     * @return false
     */
    public static function databaseUpdate(\DatabaseInstance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setDatabase($item);
    }

    /**
     * @param \Database $item
     */
    public static function setDatabase(\DatabaseInstance $item)
    {
        $database = new DatabaseInstance();
        if (!empty($item->fields)) {
            $database->getFromDBByCrit(['databaseinstances_id' => $item->getID()]);
            if (is_array($database->fields) && count($database->fields) > 0) {

                $webapplicationexternalexpositions_id = 0;
                if (isset($item->input['webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $item->input['webapplicationexternalexpositions_id'];
                } elseif (isset($database->fields['plugin_webapplications_webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $database->fields['plugin_webapplications_webapplicationexternalexpositions_id'];
                }

                $webapplicationavailabilities = 0;
                if (isset($item->input['webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $item->input['webapplicationavailabilities'];
                } elseif (isset($database->fields['plugin_webapplications_webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $database->fields['plugin_webapplications_webapplicationavailabilities'];
                }

                $webapplicationintegrities = 0;
                if (isset($item->input['webapplicationintegrities'])) {
                    $webapplicationintegrities = $item->input['webapplicationintegrities'];
                } elseif (isset($database->fields['plugin_webapplications_webapplicationintegrities'])) {
                    $webapplicationintegrities = $database->fields['plugin_webapplications_webapplicationintegrities'];
                }

                $webapplicationconfidentialities = 0;
                if (isset($item->input['webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $item->input['webapplicationconfidentialities'];
                } elseif (isset($database->fields['plugin_webapplications_webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $database->fields['plugin_webapplications_webapplicationconfidentialities'];
                }

                $webapplicationtraceabilities = 0;
                if (isset($item->input['webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $item->input['webapplicationtraceabilities'];
                } elseif (isset($database->fields['plugin_webapplications_webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $database->fields['plugin_webapplications_webapplicationtraceabilities'];
                }

                $database->update([
                    'id' => $database->fields['id'],
                    'webapplicationexternalexpositions_id' => $webapplicationexternalexpositions_id,
                    'webapplicationavailabilities' => $webapplicationavailabilities,
                    'webapplicationintegrities' => $webapplicationintegrities,
                    'webapplicationconfidentialities' => $webapplicationconfidentialities,
                    'webapplicationtraceabilities' => $webapplicationtraceabilities,
                ]);
            } else {
                if ($item->getID() > 0) {
                    $webs = getAllDataFromTable(
                        "glpi_plugin_webapplications_databaseinstances",
                        [
                            'WHERE' => [
                                'databaseinstances_id' => $item->getID(),
                            ],
                        ],
                    );
                    foreach ($webs as $web) {
                        $item->input["webapplicationavailabilities"] = $web["webapplicationavailabilities"];
                        $item->input["webapplicationintegrities"] = $web["webapplicationintegrities"];
                        $item->input["webapplicationconfidentialities"] = $web["webapplicationconfidentialities"];
                        $item->input["webapplicationtraceabilities"] = $web["webapplicationtraceabilities"];
                    }
                }

                $webapplicationexternalexpositions_id = 0;
                if (isset($item->input['webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $item->input['webapplicationexternalexpositions_id'];
                }

                $webapplicationavailabilities = 0;
                if (isset($item->input['webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $item->input['webapplicationavailabilities'];
                }

                $webapplicationintegrities = 0;
                if (isset($item->input['webapplicationintegrities'])) {
                    $webapplicationintegrities = $item->input['webapplicationintegrities'];
                }

                $webapplicationconfidentialities = 0;
                if (isset($item->input['webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $item->input['webapplicationconfidentialities'];
                }

                $webapplicationtraceabilities = 0;
                if (isset($item->input['webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $item->input['webapplicationtraceabilities'];
                }

                $database->add([
                    'webapplicationexternalexpositions_id' => $webapplicationexternalexpositions_id,
                    'webapplicationavailabilities' => $webapplicationavailabilities,
                    'webapplicationintegrities' => $webapplicationintegrities,
                    'webapplicationconfidentialities' => $webapplicationconfidentialities,
                    'webapplicationtraceabilities' => $webapplicationtraceabilities,
                    'appliances_id' => isset($item->input['appliances_id']) ? $item->input['appliances_id'] : 0,
                    'databaseinstances_id' => $item->getID(),
                ]);
            }
        }
    }

    /**
     * @param $item
     */
    public static function cleanRelationToDatabase($item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['databaseinstances_id' => $item->getID()]);
    }

    public static function showListObjects($list)
    {
        global $DB;

        $object = new \DatabaseInstance();
        $cards = [];

        foreach ($list as $field) {
            $id = $field['id'];
            $object->getFromDB($id);

            $blocks = [];

            $items = $DB->request([
                'FROM' => Appliance_Item::getTable(),
                'WHERE' => [
                    'items_id' => $id,
                    'itemtype' => 'DatabaseInstance',
                ],
            ]);
            $items = iterator_to_array($items);

            $env_html = "";
            foreach ($items as $row) {
                $iterator = $DB->request([
                    'FROM' => Appliance_Item_Relation::getTable(),
                    'WHERE' => [
                        Appliance_Item::getForeignKeyField() => $row['id'],
                    ],
                ]);

                foreach ($iterator as $row) {
                    $envtype = $row['itemtype'];
                    $env = new $envtype();
                    $env->getFromDB($row['items_id']);
                    $env_html .= "<i class='" . htmlescape($env->getIcon()) . "'></i>" .
                        "&nbsp;" . $env->getLink();
                }
            }
            if (!empty($env_html)) {
                $blocks[] = [
                    'kind' => 'raw',
                    'html' => $env_html,
                ];
            }

            if ($object->fields['databaseinstancetypes_id'] > 0) {
                $blocks[] = [
                    'kind'  => 'info',
                    'label' => __('Type'),
                    'value' => Dropdown::getDropdownName(
                        "glpi_databaseinstancetypes",
                        $object->fields['databaseinstancetypes_id'],
                    ),
                ];
            }

            $databases = getAllDataFromTable(
                Database::getTable(),
                [
                    'WHERE' => [
                        'databaseinstances_id' => $id,
                    ],
                    'ORDER' => 'name',
                ],
            );
            $db = new Database();
            $sizes_html = "";
            foreach ($databases as $row) {
                $db->getFromDB($row['id']);
                if ($row['size'] > 0) {
                    $sizes_html .= $db->getLink() . " - "
                        . htmlescape(sprintf(__('%s Mio'), $row['size'])) . "</br>";
                }
            }
            if (!empty($sizes_html)) {
                $blocks[] = [
                    'kind' => 'raw',
                    'html' => $sizes_html,
                ];
            }

            $dicts = getAllDataFromTable(
                "glpi_plugin_webapplications_databaseinstances",
                [
                    'WHERE' => [
                        'databaseinstances_id' => $id,
                    ],
                ],
            );

            foreach ($dicts as $dict) {
                $blocks[] = [
                    'kind'   => 'badges',
                    'badges' => [
                        [
                            'value' => $dict['webapplicationavailabilities'],
                            'color' => Appliance::getColorForDICT($dict['webapplicationavailabilities']),
                            'title' => __('Availability', 'webapplications'),
                        ],
                        [
                            'value' => $dict['webapplicationintegrities'],
                            'color' => Appliance::getColorForDICT($dict['webapplicationintegrities']),
                            'title' => __('Integrity', 'webapplications'),
                        ],
                        [
                            'value' => $dict['webapplicationconfidentialities'],
                            'color' => Appliance::getColorForDICT($dict['webapplicationconfidentialities']),
                            'title' => __('Confidentiality', 'webapplications'),
                        ],
                        [
                            'value' => $dict['webapplicationtraceabilities'],
                            'color' => Appliance::getColorForDICT($dict['webapplicationtraceabilities']),
                            'title' => __('Traceability', 'webapplications'),
                        ],
                    ],
                ];
            }

            $cards[] = [
                'width_class' => 'w-33',
                'icon'        => 'ti ti-database',
                'icon_size'   => '3em',
                'title_html'  => $object->getLink(),
                'blocks'      => $blocks,
                'edit_html'   => Dashboard::getCardEditHtml($object, (int) $id),
            ];
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_object_cards.html.twig', [
            'cards' => $cards,
        ]);
    }
}
