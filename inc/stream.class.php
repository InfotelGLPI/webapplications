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
 * Class PluginWebapplicationsStream
 */
class PluginWebapplicationsStream extends CommonDBTM
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
        return "fas fa-network-wired";
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

        $this->getFromDB($ID);

        $transmitter_type = $this->getField('transmitter_type');
        $transmitterId = $this->getField('transmitter');


        if (!empty($transmitter_type) && !empty($transmitterId)) {
            $transmitter = new $transmitter_type;
            $transmitter->getFromDB($transmitterId);
            $linkTransmitter = $transmitter_type::getFormURLWithID($transmitterId);
            $transmitterName = $transmitter->getName();

            $options['linkTransmitter'] = "<a href= $linkTransmitter>$transmitterName</a>";
        } else {
            $options['linkTransmitter'] = __('All');
        }

        $receiver_type = $this->getField('receiver_type');
        $receiverId = $this->getField('receiver');
        if (!empty($receiver_type) && !empty($receiverId)) {
            $receiver = new $receiver_type;
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
            $itemDBTM->add([
                'appliances_id' => $appliance_id,
                'items_id' => $this->getID(),
                'itemtype' => 'PluginWebapplicationsStream'
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
            'field' => 'transmitter_type',
            'name' => __('Source', 'webapplications'),
            'datatype' => 'specific',
            'massiveaction' => 'false',
            'nosort' => true,
            'nosearch' => true
        ];

        $tab[] = [
            'id' => '3',
            'table' => self::getTable(),
            'field' => 'receiver_type',
            'name' => __('Destination', 'webapplications'),
            'datatype' => 'specific',
            'massiveaction' => 'false',
            'nosort' => true,
            'nosearch' => true
        ];

        $tab[] = [
            'id' => '6',
            'table' => self::getTable(),
            'field' => 'encryption',
            'name' => __('Encryption', 'webapplications'),
            'datatype' => 'bool'
        ];
        $tab[] = [
            'id' => '7',
            'table' => self::getTable(),
            'field' => 'encryption_type',
            'name' => __('Encryption type', 'webapplications'),
            'datatype' => 'text'
        ];
        $tab[] = [
            'id' => '8',
            'table' => self::getTable(),
            'field' => 'port',
            'name' => __('Port', 'webapplications'),
            'datatype' => 'text'
        ];
        $tab[] = [
            'id' => '9',
            'table' => self::getTable(),
            'field' => 'protocol',
            'name' => __('Protocol', 'webapplications'),
            'datatype' => 'text'
        ];

        return $tab;
    }

    /**
     * display a value according to a field
     *
     * @param $field     String         name of the field
     * @param $values    String / Array with the value to display
     * @param $options   Array          of option
     *
     * @return string
     *
     */
    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        global $CFG_GLPI;

        switch ($field) {
            case "transmitter_type":
            case "receiver_type":
                $types = $CFG_GLPI['inventory_types'];
                $types[] = 'DatabaseInstance';
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
                $items = [];
                foreach ($types as $k => $type) {
                    $items[$type] = $type::getTypeName();
                }
                $options['value'] = $values[$field];
                return Dropdown::showFromArray(
                    $name,
                    $items,
                    $options
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
        $object = new self();

        echo "<div style='display: flex;flex-wrap: wrap;'>";

        foreach ($list as $field) {
            $name = $field['name'];
            $id = $field['id'];
            $object->getFromDB($id);

//            echo "<h3 class='accordionhead'>";
//            echo $name;
//            echo "</td>";
//            echo "</h3>";
//
//            echo "<div class='panel' id='tabsbody'>";
//            $options = [];
//            $options['canedit'] = false;
//            $options['candel'] = false;

            $linkReceiver = __('All');
            $receiverType = $field['receiver_type'];
            $receiverid = $field['receiver'];
            if (!empty($receiverType) && !empty($receiverid)) {
                $receiver = new $receiverType;
                $receiver->getFromDB($receiverid);
                $linkR = $receiverType::getFormURLWithID($receiverid);
                $receiverName = $receiver->getName();
                $linkReceiver = "<a href='$linkR'>" . $receiverName . "</a>";
            }

            $linkTransmitter = __('All');
            $transmitterType = $field['transmitter_type'];
            $transmitterid = $field['transmitter'];
            if (!empty($transmitterType) && !empty($transmitterid)) {
                $transmitter = new $transmitterType;
                $transmitter->getFromDB($transmitterid);
                $linkT = $transmitterType::getFormURLWithID($transmitterid);
                $transmitterName = $transmitter->getName();
                $linkTransmitter = "<a href='$linkT'>" . $transmitterName . "</a>";
            }


            echo "<div class='card w-25' style='margin-right: 10px;margin-top: 10px;'>";
            echo "<div class='card-body'>";
            echo "<div style='display: inline-block;margin: 40px;'>";
            echo "<i class='fa-5x fas ".self::getIcon()."'></i>";
            echo "</div>";
            echo "<div style='display: inline-block;';>";

            echo "<h5 class='card-title'><i class='fa-1x fas fa-ethernet'></i>&nbsp;" . $linkTransmitter . "&nbsp;";
            echo "<i class='fa-1x fas fa-arrow-right'></i>";
            echo "&nbsp;<i class='fa-1x fas fa-ethernet'></i>&nbsp;" . $linkReceiver . "</h5>";
            echo "<p class='card-text'>";
            echo $name;
            echo "</p>";
            echo "<p class='card-text'>";
            echo $object->fields['protocol']. " - ". $object->fields['port'];
            echo "</p>";
            if ($object->fields['encryption'] == 1) {
                echo "<p class='card-text'>";
                echo __('Encryption type', 'webapplications')." : ".$object->fields['encryption_type'];
                echo "</p>";
            }
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
