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
        return "fas fa-rss";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nbStreams = count(self::getStreams());
            return self::createTabEntry(self::getTypeName($nbStreams), $nbStreams);
        }
        return _n('Stream', 'Streams', 2, 'webapplications');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
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
        }

        $receiver_type = $this->getField('receiver_type');
        $receiverId = $this->getField('receiver');
        if (!empty($receiver_type) && !empty($receiverId)) {
            $receiver = new $receiver_type;
            $receiver->getFromDB($receiverId);
            $linkReceiver = $receiver_type::getFormURLWithID($receiverId);
            $receiverName = $receiver->getName();

            $options['linkReceiver'] = "<a href= $linkReceiver>$receiverName</a>";
        }


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
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id' => '3',
            'table' => self::getTable(),
            'field' => 'receiver_type',
            'name' => __('Destination', 'webapplications'),
            'datatype' => 'dropdown'
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
            'field' => 'ports',
            'name' => __('Port', 'webapplications'),
            'datatype' => 'text'
        ];
        $tab[] = [
            'id' => '9',
            'table' => self::getTable(),
            'field' => 'protocole',
            'name' => __('Protocol', 'webapplications'),
            'datatype' => 'text'
        ];

        return $tab;
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        return $ong;
    }

    public static function getStreams()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $streamAppDBTM = new Appliance_Item();
        $streamApp = $streamAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsStream']
        );


        $listStreamId = [];
        foreach ($streamApp as $st) {
            array_push($listStreamId, $st['items_id']);
        }


        $listStreams = [];
        if (!empty($listStreamId)) {
            $streamDBTM = new PluginWebapplicationsStream();
            $listStreams = $streamDBTM->find(['id' => $listStreamId]);
        }
        return $listStreams;
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

        $streamDBTM = new PluginWebapplicationsStream();
        $linkAddStream = $streamDBTM::getFormURL();

        $listStream = self::getStreams();

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
        echo _n('Stream', 'Streams', 2, 'webapplications');

        echo "<span style='float: right'>";
        echo Html::submit(
            _sx('button', 'Add'),
            [
                'name' => 'edit',
                'class' => 'btn btn-primary',
                'icon' => 'fas fa-plus',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#addStream',
                'style' => 'float: right'
            ]
        );
        echo Ajax::createIframeModalWindow(
            'addStream',
            $linkAddStream . "?appliance_id=" . $ApplianceId,
            [
                'display' => false,
                'reloadonclose' => true
            ]
        );
        echo "</span>";
        echo "</h2>";

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
        echo _n("Stream list", 'Streams list', count($listStream), 'webapplications');
        echo "</h2>";

        echo "<div class='accordion' name=listStreamApp>";


        if (!empty($listStream)) {
            foreach ($listStream as $stream) {
                $name = $stream['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";
                $linkStream = PluginWebapplicationsStream::getFormURLWithID($stream['id']);
                $linkStream .= "&forcetab=main";

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo "<a href=$linkStream>$name</a>";
                echo "</td>";

                echo "<td></td>";

                echo "<td style='width: 10%'>";
                echo Html::submit(
                    _sx('button', 'Edit'),
                    [
                        'name' => 'edit',
                        'class' => 'btn btn-secondary',
                        'icon' => 'fas fa-edit',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#editStream' . $stream['id']
                    ]
                );

                echo Ajax::createIframeModalWindow(
                    'editStream' . $stream['id'],
                    $linkStream,
                    [
                        'display' => false,
                        'reloadonclose' => true
                    ]
                );
                echo "</td>";

                echo "</tr>";


                echo "<tr>";
                echo "<th>";
                echo __("Source", 'webapplications');
                echo "</th>";
                echo "<td>";

                $transmitterType = $stream['transmitter_type'];
                $transmitterid = $stream['transmitter'];
                if (!empty($transmitterType) && !empty($transmitterid)) {
                    $transmitter = new $transmitterType;
                    $transmitter->getFromDB($transmitterid);
                    $linkTransmitter = $transmitterType::getFormURLWithID($transmitterid);
                    $transmitterName = $transmitter->getName();
                    echo "<a href=$linkTransmitter>$transmitterName</a>";
                }
                echo "</td>";


                echo "<th>";
                echo __("Destination", 'webapplications');
                echo "</th>";
                echo "<td>";

                $receiverType = $stream['receiver_type'];
                $receiverid = $stream['receiver'];
                if (!empty($receiverType) && !empty($receiverid)) {
                    $receiver = new $receiverType;
                    $receiver->getFromDB($receiverid);
                    $linkReceiver = $receiverType::getFormURLWithID($receiverid);
                    $receiverName = $receiver->getName();
                    echo "<a href= $linkReceiver>$receiverName</a>";
                }
                echo "</td>";
                echo "</tr>";


                $encryption = $stream['encryption'];

                echo "<tr>";
                echo "<th>";
                echo __("Encryption", 'webapplications');
                echo "</th>";
                echo "<td>";
                if ($encryption == 0) {
                    echo __('No');
                } else {
                    echo __('Yes');
                }
                echo "</td>";


                $encryption_type = $stream['encryption_type'];

                if ($encryption == 0) {
                    echo "<td></td>";
                } else {
                    echo "<th>";
                    echo __("Encryption type", 'webapplications');
                    echo "</th>";
                    echo "<td>";
                    echo $encryption_type;
                    echo "</td>";
                }
                echo "</tr>";


                $ports = $stream['ports'];

                echo "<tr>";
                echo "<th>";
                echo __('Port', 'webapplication');
                echo "</th>";
                echo "<td>";
                echo $ports;
                echo "</td>";


                $protocole = $stream['protocole'];

                echo "<th>";
                echo __("Protocol", 'webapplications');
                echo "</th>";
                echo "<td>";
                echo $protocole;
                echo "</td>";
                echo "</tr>";


                echo "</tbody>";
                echo "</table></div>";
            }
        } else {
            echo __("No associated stream", 'webapplications');
        }
        echo "</div>";

        echo "<script>accordion();</script>";
    }
}
