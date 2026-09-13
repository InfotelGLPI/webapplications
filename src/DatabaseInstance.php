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
        $appliance_id = (int) ($this->input['appliances_id'] ?? 0);
        $items_id = (int) ($this->input['databaseinstances_id'] ?? 0);
        // setDatabase() forwards the appliance id posted on the core DatabaseInstance
        // form without any check. Linking an item to an appliance is governed by UPDATE
        // on that appliance (Appliance_Item is a CommonDBRelation whose
        // $checkItem_1_Rights is HAVE_SAME_RIGHT_ON_ITEM); calling Appliance_Item::add()
        // directly bypasses it, so replay the check here.
        if ($appliance_id > 0 && (new \Appliance())->can($appliance_id, UPDATE)) {
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
                // The plugin Appliance, not the core one: the five columns read in the loop
                // below (exposure, availability, integrity, confidentiality, traceability)
                // belong to glpi_plugin_webapplications_appliances, and so does the
                // appliances_id column the criterion filters on - glpi_appliances has neither.
                // The leading backslash resolved the name to the core class, so the query hit
                // the wrong table and returned nothing at best. Appliance is imported at the
                // top of this file as the plugin class.
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

                // CommonDBTM::update() keys the write on the id of the input array and does
                // nothing at all without it, so this whole branch was a no-op: the record
                // loaded by getFromDBByCrit() a few lines above is the one to update.
                $database->update([
                    'id' => $database->fields['id'],
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

                // Each field used to fall back on $database->fields with a
                // "plugin_webapplications_" prefix, but the row loaded just above comes from
                // glpi_plugin_webapplications_databaseinstances, whose columns carry no such
                // prefix (see install/sql/empty.sql and the migration in
                // front/webapplication.php). The elseif was therefore never true, and every
                // field missing from $item->input fell back on the initial 0. As this method
                // runs on the update hook of the CORE DatabaseInstance - a form that carries
                // none of these five fields - saving the core object silently zeroed the
                // exposure, availability, integrity, confidentiality and traceability of the
                // plugin record. Only the values actually submitted are written now: a field
                // left out simply keeps what is stored, which is what the fallback was trying
                // to express.
                $input = ['id' => $database->fields['id']];
                foreach ([
                    'webapplicationexternalexpositions_id',
                    'webapplicationavailabilities',
                    'webapplicationintegrities',
                    'webapplicationconfidentialities',
                    'webapplicationtraceabilities',
                ] as $field) {
                    if (isset($item->input[$field])) {
                        $input[$field] = $item->input[$field];
                    }
                }

                if (count($input) > 1) {
                    $database->update($input);
                }
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

            $env_lines = [];
            foreach ($items as $row) {
                $iterator = $DB->request([
                    'FROM' => Appliance_Item_Relation::getTable(),
                    'WHERE' => [
                        Appliance_Item::getForeignKeyField() => $row['id'],
                    ],
                ]);

                foreach ($iterator as $objrow) {
                    $envtype = $objrow['itemtype'];
                    // Same two guards as the twin loop in
                    // Dashboard::getRelatedEnvironmentsLabel(), which reads this very
                    // table: only that one had been hardened. The class name comes from
                    // the database, so it is validated before instantiation (an
                    // autoloadable non-CommonDBTM value would otherwise be constructed,
                    // or raise a fatal error on the tab), and the environment may belong
                    // to another entity than the viewer, so the read right - which
                    // getFromDB() does not apply - is checked before its name is
                    // disclosed. The loop variable is renamed as well: it used to shadow
                    // the $row of the enclosing loop.
                    if (!is_a($envtype, CommonDBTM::class, true)) {
                        continue;
                    }
                    $env = new $envtype();
                    if (!$env->can((int) $objrow['items_id'], READ)) {
                        continue;
                    }
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
            $size_lines = [];
            foreach ($databases as $row) {
                $db->getFromDB($row['id']);
                if ($row['size'] > 0) {
                    // getLink() is trusted framework markup; the size label is DB data escaped by Twig.
                    $size_lines[] = [
                        'link'       => $db->getLink(),
                        'size_label' => sprintf(__('%s Mio'), $row['size']),
                    ];
                }
            }
            if (!empty($size_lines)) {
                $blocks[] = [
                    'kind'  => 'sizes',
                    'lines' => $size_lines,
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
