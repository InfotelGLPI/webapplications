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
    public static $rightname         = "plugin_webapplications_streams";

    public static function getTypeName($nb = 0)
    {
        return _n('Stream', 'Streams', $nb, 'webapplications');
    }

    public static function getMenuContent()
    {
        $menu = [];

        $menu['title']           = self::getMenuName();
        $menu['page']            = self::getSearchURL(false);
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


    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);

        $this->getFromDB($ID);

        $transmitter_type = $this->getField('transmitter_type');
        $transmitterId = $this->getField('transmitter');
        if (!empty($transmitter_type) && !empty($transmitterId)) {
            $transmitter = new $transmitter_type;
            $transmitter->getFromDB($transmitterId);
            $linkTransmitter= $transmitter_type::getFormURLWithID($transmitterId);
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
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function pre_update()
    {
        if (isset($_POST["update"])) {
            if (isset($_POST["transmitter_type"])) {
                if ((strcmp($_POST["transmitter_type"], "0")==0) || (strcmp($_POST["transmitter"], "0")==0)) {
                    unset($_POST['transmitter_type'], $_POST['transmitter']);
                }
            }
            if (isset($_POST["receiver_type"])) {
                if ((strcmp($_POST["receiver_type"], "0")==0) || (strcmp($_POST["receiver"], "0")==0)) {
                    unset($_POST['receiver_type'], $_POST['receiver']);
                }
            }
        }
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        if (!is_null($appliance_id)&&$appliance_id!=0) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add(['appliances_id' => $appliance_id, 'items_id' => $this->getID(), 'itemtype' => 'PluginWebapplicationsStream']);
        }
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        $this->addStandardTab('PluginWebapplicationsStream_Item', $ong, $options);
        return $ong;
    }
}
