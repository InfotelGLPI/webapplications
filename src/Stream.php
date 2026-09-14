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
use Session;

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

        // Security (cross-entity disclosure): the endpoint columns point at a core object
        // (Computer, NetworkEquipment, DatabaseInstance, Appliance) that may live in a
        // completely different entity than the stream itself. isValidEndpointType()
        // settles which class is loaded, not whether the viewer may read that row, and
        // getFromDB() applies neither the right of the target type nor
        // Session::haveAccessToEntity(). Every other rendering of the same data already
        // filters with can($id, READ) - Dashboard::getObjects(),
        // PhysicalInfrastructure::getItems(), Pdf.php - these two
        // sites were the only ones left out. On refusal the neutral "All" label is shown
        // rather than the name, so the very existence of the target is not revealed either.
        $options['linkTransmitter'] = __('All');
        if (!empty($transmitterId) && self::isValidEndpointType($transmitter_type)) {
            $transmitter = new $transmitter_type();
            if ($transmitter->can((int) $transmitterId, READ)) {
                $linkTransmitter = $transmitter_type::getFormURLWithID($transmitterId);
                // getName() returns the raw stored value (unescaped since GLPI 10) and
                // this fragment is rendered as raw HTML by fields.htmlField, so escape
                // the name to prevent stored XSS and quote the href.
                $transmitterName = htmlspecialchars($transmitter->getName());

                $options['linkTransmitter'] = '<a href="' . $linkTransmitter . '">' . $transmitterName . '</a>';
            }
        }

        $receiver_type = $this->getField('receiver_type');
        $receiverId = $this->getField('receiver');
        // Same read filter as the transmitter above.
        $options['linkReceiver'] = __('All');
        if (!empty($receiverId) && self::isValidEndpointType($receiver_type)) {
            $receiver = new $receiver_type();
            if ($receiver->can((int) $receiverId, READ)) {
                $linkReceiver = $receiver_type::getFormURLWithID($receiverId);
                // Same as the transmitter above: escape the raw stored name before it
                // is rendered as raw HTML by fields.htmlField, and quote the href.
                $receiverName = htmlspecialchars($receiver->getName());

                $options['linkReceiver'] = '<a href="' . $linkReceiver . '">' . $receiverName . '</a>';
            }
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
     * Reset any transmitter/receiver endpoint the caller may not designate to an empty
     * couple (meaning "All"), so neither a forged itemtype nor an object identifier out of
     * the caller's scope can be persisted.
     */
    private function sanitizeEndpoints(array $input): array
    {
        // Only the itemtype used to be filtered, against $CFG_GLPI['stream_types']. The
        // identifier travelling with it went through array_intersect_key() as a plain integer
        // and was never confronted with anything: neither a read right nor an entity. A forged
        // form could therefore attach to a stream the computer of another entity. showForm()
        // and rawSearchOptions() do hide the name behind a can(READ), so nothing is shown on
        // the spot, but the identifier stays in the record, is written to the history, and is
        // reprinted by the PDF export - and any rendering added later that forgets the filter
        // turns the stored reference into a disclosure. The right place for that check is the
        // write, which is where the plugin already validates appliances_id.
        foreach (['transmitter' => 'transmitter_type', 'receiver' => 'receiver_type'] as $id_field => $type_field) {
            if (!array_key_exists($type_field, $input) && !array_key_exists($id_field, $input)) {
                // Nothing posted for this endpoint: leave the stored couple alone.
                continue;
            }

            // An update may post only one half of the couple, so the missing half is read back
            // from the record: what is checked is the pair that will actually be persisted.
            $type = (string) ($input[$type_field] ?? ($this->fields[$type_field] ?? ''));
            $id   = (int) ($input[$id_field] ?? ($this->fields[$id_field] ?? 0));

            $allowed = false;
            if (self::isValidEndpointType($type) && $id > 0) {
                // getItemForItemtype() rather than a direct new $type(): the itemtype has
                // already been confronted with the allow-list above, and the factory returns a
                // usable instance or false without a dynamic instantiation here.
                $endpoint = getItemForItemtype($type);
                // can() applies the global right, the object right and
                // Session::haveAccessToEntity(); getFromDB() applies none of them.
                $allowed = $endpoint instanceof CommonDBTM && $endpoint->can($id, READ);
            }

            if (!$allowed) {
                // Same fallback as a non-conforming itemtype, applied to the whole couple so
                // no orphan identifier survives: the endpoint becomes "All".
                $input[$type_field] = '';
                $input[$id_field]   = 0;
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
        $input = $this->sanitizeEndpoints($input);
        if (isset($input['appliances_id']) && !empty($input['appliances_id'])) {
            $item = new \Appliance();
            // The posted appliance drives both the entity this record lands in and the
            // Appliance_Item link created by post_addItem(). The core governs that link
            // with UPDATE on the appliance (Appliance_Item is a CommonDBRelation whose
            // $checkItem_1_Rights is HAVE_SAME_RIGHT_ON_ITEM); replay the same check
            // here since the plugin calls Appliance_Item::add() directly. can() also
            // applies Session::haveAccessToEntity(), which getFromDB() does not.
            if (!$item->can((int) $input['appliances_id'], UPDATE)) {
                Session::addMessageAfterRedirect(
                    __("You don't have permission to perform this action."),
                    false,
                    ERROR,
                );
                return false;
            }
            $input['entities_id'] = $item->fields['entities_id'];
            $input['is_recursive'] = $item->fields['is_recursive'];
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        // entities_id and is_recursive are deliberately out of the whitelist: the entity of
        // this record is derived from the linked appliance by prepareInputForAdd(), after a
        // can($appliances_id, UPDATE) on it, and no field of the form posts them. While they
        // were accepted, a forged POST moved the record into an entity the caller has no
        // access to - check($id, UPDATE) only settles the entity the record already occupies,
        // and CommonDBTM::update() does not revalidate a posted entities_id.
        $allowed = ['id', 'name',
            'transmitter', 'transmitter_type', 'receiver', 'receiver_type',
            'encryption', 'encryption_type', 'port', 'protocol'];
        $input = array_intersect_key($input, array_flip($allowed));
        $input = $this->sanitizeEndpoints($input);
        return parent::prepareInputForUpdate($input);
    }

    public function post_addItem()
    {
        // prepareInputForAdd() already refused any appliance the caller cannot update,
        // so reaching this point means the link is authorized. The null coalescing
        // keeps the method safe when the record is created without an appliance.
        $appliance_id = (int) ($this->input['appliances_id'] ?? 0);
        if ($appliance_id > 0) {
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
            // Same read filter as Stream::showForm(): the endpoint may belong to an
            // entity the viewer has no access to, and getFromDB() checks nothing.
            if (!empty($receiverid) && self::isValidEndpointType($receiverType)) {
                $receiver = new $receiverType();
                if ($receiver->can((int) $receiverid, READ)) {
                    $linkR        = $receiverType::getFormURLWithID($receiverid);
                    $receiverName = htmlescape($receiver->getName());
                    $linkReceiver = "<a href='" . htmlescape($linkR) . "'>" . $receiverName . "</a>";
                }
            }

            $linkTransmitter = __('All');
            $transmitterType = $field['transmitter_type'];
            $transmitterid   = $field['transmitter'];
            // Same read filter as the receiver above.
            if (!empty($transmitterid) && self::isValidEndpointType($transmitterType)) {
                $transmitter = new $transmitterType();
                if ($transmitter->can((int) $transmitterid, READ)) {
                    $linkT           = $transmitterType::getFormURLWithID($transmitterid);
                    $transmitterName = htmlescape($transmitter->getName());
                    $linkTransmitter = "<a href='" . htmlescape($linkT) . "'>" . $transmitterName . "</a>";
                }
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
