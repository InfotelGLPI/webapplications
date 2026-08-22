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
use CommonDBTM;
use CommonGLPI;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Stream
 */
class Stream extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_streams";

    public static function getTypeName($nb = 0)
    {
        return _n('Stream', 'Streams', $nb, 'webapplications');
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
        return "ti ti-network";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;
            $self = new self();
            $nb = count(Dashboard::getObjects($self, $ApplianceId));
            return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new self();
        Dashboard::showList($obj);
        return true;
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);

        $this->getFromDB($ID);

        $transmitter_type = $this->getField('transmitter_type');
        $transmitterId = $this->getField('transmitter');

        if (!empty($transmitterId) && self::isValidEndpointType($transmitter_type)) {
            $transmitter = new $transmitter_type();
            $transmitter->getFromDB($transmitterId);
            $linkTransmitter = $transmitter_type::getFormURLWithID($transmitterId);
            $transmitterName = $transmitter->getName();

            $options['linkTransmitter'] = "<a href= $linkTransmitter>$transmitterName</a>";
        } else {
            $options['linkTransmitter'] = __('All');
        }

        $receiver_type = $this->getField('receiver_type');
        $receiverId = $this->getField('receiver');
        if (!empty($receiverId) && self::isValidEndpointType($receiver_type)) {
            $receiver = new $receiver_type();
            $receiver->getFromDB($receiverId);
            $linkReceiver = $receiver_type::getFormURLWithID($receiverId);
            $receiverName = $receiver->getName();

            $options['linkReceiver'] = "<a href= $linkReceiver>$receiverName</a>";
        } else {
            $options['linkReceiver'] = __('All');
        }

        $options['appliances_id'] = $_SESSION['plugin_webapplications_loaded_appliances_id'];
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_stream_form.html.twig', [
            'item' => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function pre_update()
    {
        if (isset($_POST["update"])) {
            if (isset($_POST["transmitter_type"])) {
                if ((strcmp($_POST["transmitter_type"], "0") == 0) || (strcmp($_POST["transmitter"], "0") == 0)) {
                    unset($_POST['transmitter_type'], $_POST['transmitter']);
                }
            }
            if (isset($_POST["receiver_type"])) {
                if ((strcmp($_POST["receiver_type"], "0") == 0) || (strcmp($_POST["receiver"], "0") == 0)) {
                    unset($_POST['receiver_type'], $_POST['receiver']);
                }
            }
        }
    }

    /**
     * Whether an endpoint itemtype (transmitter/receiver) is one of the plugin's
     * allowed stream endpoint types. These values are user controlled and are later
     * used as class names (`new $type()`), so this whitelist must gate both writes
     * and every dynamic instantiation.
     */
    private static function isValidEndpointType($type): bool
    {
        global $CFG_GLPI;

        return is_string($type) && $type !== ''
            && in_array($type, $CFG_GLPI['stream_types'] ?? [], true);
    }

    /**
     * Reset any transmitter/receiver itemtype that is not whitelisted to an empty
     * value (meaning "All"), so a forged endpoint type can never be persisted.
     */
    private static function sanitizeEndpointTypes(array $input): array
    {
        foreach (['transmitter_type', 'receiver_type'] as $type_field) {
            if (isset($input[$type_field]) && !self::isValidEndpointType($input[$type_field])) {
                $input[$type_field] = '';
            }
        }
        return $input;
    }

    public function prepareInputForAdd($input)
    {
        $allowed = ['id', 'entities_id', 'is_recursive', 'name', 'appliances_id',
            'transmitter', 'transmitter_type', 'receiver', 'receiver_type',
            'encryption', 'encryption_type', 'port', 'protocol'];
        $input = array_intersect_key($input, array_flip($allowed));
        $input = self::sanitizeEndpointTypes($input);
        if (isset($input['appliances_id']) && !empty($input['appliances_id'])) {
            $item = new \Appliance();
            if ($item->getFromDB($input['appliances_id'])) {
                $input['entities_id'] = $item->fields['entities_id'];
                $input['is_recursive'] = $item->fields['is_recursive'];
            }
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $allowed = ['id', 'entities_id', 'is_recursive', 'name',
            'transmitter', 'transmitter_type', 'receiver', 'receiver_type',
            'encryption', 'encryption_type', 'port', 'protocol'];
        $input = array_intersect_key($input, array_flip($allowed));
        $input = self::sanitizeEndpointTypes($input);
        return parent::prepareInputForUpdate($input);
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        if (isset($appliance_id) && !empty($appliance_id)) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add([
                'appliances_id' => $appliance_id,
                'items_id' => $this->getID(),
                'itemtype' => Stream::class,
            ]);
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
            'name' => self::getTypeName(2),
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
            'field' => 'transmitter_type',
            'name' => __('Source', 'webapplications'),
            'datatype' => 'specific',
            'massiveaction' => 'false',
            'nosort' => true,
            'nosearch' => true,
        ];

        $tab[] = [
            'id' => '3',
            'table' => self::getTable(),
            'field' => 'receiver_type',
            'name' => __('Destination', 'webapplications'),
            'datatype' => 'specific',
            'massiveaction' => 'false',
            'nosort' => true,
            'nosearch' => true,
        ];

        $tab[] = [
            'id' => '6',
            'table' => self::getTable(),
            'field' => 'encryption',
            'name' => __('Encryption', 'webapplications'),
            'datatype' => 'bool',
        ];
        $tab[] = [
            'id' => '7',
            'table' => self::getTable(),
            'field' => 'encryption_type',
            'name' => __('Encryption type', 'webapplications'),
            'datatype' => 'text',
        ];
        $tab[] = [
            'id' => '8',
            'table' => self::getTable(),
            'field' => 'port',
            'name' => __('Port', 'webapplications'),
            'datatype' => 'text',
        ];
        $tab[] = [
            'id' => '9',
            'table' => self::getTable(),
            'field' => 'protocol',
            'name' => __('Protocol', 'webapplications'),
            'datatype' => 'text',
        ];

        return $tab;
    }

    /**
     * display a value according to a field
     *
     * @param $field     String         name of the field
     * @param $values    String / Array with the value to display
     * @param $options   array option
     *
     * @return string
     *
     */
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        global $CFG_GLPI;

        switch ($field) {
            case "transmitter_type":
            case "receiver_type":
                $types = $CFG_GLPI['inventory_types'];
                $types[] = 'DatabaseInstance';
                $types[] = 'Appliance';
                $items = [];
                foreach ($types as $k => $type) {
                    $items[$type] = $type::getTypeName();
                }

                if (isset($items[$values['name']])) {
                    return $items[$values['name']];
                }

                return "";
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    /**
     * @param $field
     * @param $name (default '')
     * @param $values (defaut '')
     * @param $options   array
     **@since version 2.3.0
     *
     */
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        global $CFG_GLPI;

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'transmitter_type':
            case "receiver_type":
                $types = $CFG_GLPI['inventory_types'];
                $types[] = 'DatabaseInstance';
                $types[] = 'Appliance';
                $items = [];
                foreach ($types as $k => $type) {
                    $items[$type] = $type::getTypeName();
                }
                $options['value'] = $values[$field];
                return Dropdown::showFromArray(
                    $name,
                    $items,
                    $options,
                );
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        return $ong;
    }

    public static function showListObjects($list)
    {
        $object  = new self();
        $entries = [];

        foreach ($list as $field) {
            $id = (int) $field['id'];
            $object->getFromDB($id);

            // Endpoint (transmitter / receiver): itemtype + id, else "All".
            $linkReceiver = __('All');
            $receiverType = $field['receiver_type'];
            $receiverid   = $field['receiver'];
            if (!empty($receiverid) && self::isValidEndpointType($receiverType)) {
                $receiver = new $receiverType();
                $receiver->getFromDB($receiverid);
                $linkR        = $receiverType::getFormURLWithID($receiverid);
                $receiverName = htmlescape($receiver->getName());
                $linkReceiver = "<a href='" . htmlescape($linkR) . "'>" . $receiverName . "</a>";
            }

            $linkTransmitter = __('All');
            $transmitterType = $field['transmitter_type'];
            $transmitterid   = $field['transmitter'];
            if (!empty($transmitterid) && self::isValidEndpointType($transmitterType)) {
                $transmitter = new $transmitterType();
                $transmitter->getFromDB($transmitterid);
                $linkT           = $transmitterType::getFormURLWithID($transmitterid);
                $transmitterName = htmlescape($transmitter->getName());
                $linkTransmitter = "<a href='" . htmlescape($linkT) . "'>" . $transmitterName . "</a>";
            }

            $flow_html = "<i class='ti ti-network'></i>&nbsp;" . $linkTransmitter
                . "&nbsp;<i class='fa-1x ti ti-arrow-narrow-right'></i>&nbsp;"
                . "<i class='ti ti-network'></i>&nbsp;" . $linkReceiver;

            // Name links to the stream form (main tab).
            $name_html = "<a href='" . htmlescape($object::getFormURLWithID($id)) . "'>"
                . htmlescape($field['name']) . "</a>";

            // Encryption: type badge when enabled, dash otherwise.
            if ($object->fields['encryption'] == 1) {
                $encryption_html = "<span class='badge bg-outline-secondary'>"
                    . htmlescape($object->fields['encryption_type']) . "</span>";
            } else {
                $encryption_html = "<span class='text-muted'>&mdash;</span>";
            }

            $entries[] = [
                'name'       => $name_html,
                'flow'       => $flow_html,
                'network'    => htmlescape($object->fields['protocol'] . " - " . $object->fields['port']),
                'encryption' => $encryption_html,
                'edit'       => Dashboard::getCardEditHtml($object, $id),
            ];
        }

        // Core datatable component (no @namespace): read-only styled table for the
        // dashboard tab. Sorting/filtering/paging are disabled as there is no dedicated
        // controller to reload this tab-embedded list.
        TemplateRenderer::getInstance()->display('components/datatable.html.twig', [
            'nofilter'        => true,
            'nosort'          => true,
            'columns'         => [
                'name'       => __('Name'),
                'flow'       => __('Source', 'webapplications') . ' → ' . __('Destination', 'webapplications'),
                'network'    => __('Protocol', 'webapplications') . ' / ' . __('Port', 'webapplications'),
                'encryption' => __('Encryption type', 'webapplications'),
                'edit'       => '',
            ],
            'formatters'      => [
                'name'       => 'raw_html',
                'flow'       => 'raw_html',
                'network'    => 'raw_html',
                'encryption' => 'raw_html',
                'edit'       => 'raw_html',
            ],
            'entries'         => $entries,
            'total_number'    => count($entries),
            'filtered_number' => count($entries),
        ]);
    }
}
