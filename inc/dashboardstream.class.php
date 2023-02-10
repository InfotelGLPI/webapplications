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


/**
 * Class PluginWebapplicationsDashboardStream
 */
class PluginWebapplicationsDashboardStream extends CommonDBTM {

    static $rightname         = "plugin_webapplications_stream_dashboards";

    static function getTypeName($nb = 0) {

        return _n('Stream', 'Streams', $nb, 'webapplications');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

        if ($_SESSION['glpishow_count_on_tabs']) {
            $nbStreams = count(self::getStreams());
            return self::createTabEntry(self::getTypeName($nbStreams), $nbStreams);
        }
        return __('Streams', 'webapplications');

    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        self::showLists();
        return true;
    }


    static function getStreams(){
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $streamAppDBTM = new Appliance_Item();
        $streamApp = $streamAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsStream']);


        $listStreamId = array();
        foreach ($streamApp as $st) {
            $streamAppDBTM->getFromDB($st['id']);

            array_push($listStreamId, $st['items_id']);
        }


        $listStreams = array();
        if(!empty($listStreamId)){
            $streamDBTM = new PluginWebapplicationsStream();
            $listStreams = $streamDBTM->find(['id' => $listStreamId]);
        }
        return $listStreams;
    }

    function showForm($ID, $options = [])
    {

        global $CFG_GLPI;

        $options['candel'] = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance') . "</td>";

        echo "<td >";
        $rand = Appliance::dropdown(['name' => 'applianceDropdown']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        echo "<div id=lists-Stream></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-Stream', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);

    }

    static function showLists(){

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);

        echo '<div class="card-header main-header d-flex flex-wrap mx-n2 mt-n2 align-items-stretch">
                        <h3 class="card-title d-flex align-items-center ps-4">
                                                <div class="ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1">
                     <i class="ti ti-versions fa-2x"></i>
                  </div>
                              <h3 style="margin: auto">';
        $linkApp = Appliance::getFormURLWithID($ApplianceId);
        $name = $appliance->getName();
        echo "<a href=$linkApp>$name</a>";

        echo ' </h3>
                           </h3>
 </div>';

        $streamDBTM = new PluginWebapplicationsStream();
        $linkAddStream=$streamDBTM::getFormURL();

        $listStream = self::getStreams();

        echo "<h1>";
        echo _n("Stream",'Streams', count($listStream),'wbapplications');
        echo "</h1>";
        echo "<hr>";
        echo "<h2>";

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
                'class' => 'btn btn-primary',
                'icon' => 'fas fa-plus',
                'data-bs-toggle' => 'modal',
                'data-bs-target' =>'#addStream',
                'style' => 'float: right']
        );
        echo Ajax::createIframeModalWindow('addStream',
            $linkAddStream."?appliance_id=".$ApplianceId,
            ['display' => false,
             'reloadonclose' => true]
        );

        echo "</h2>";
        echo "<div class='accordion' name=listStreamApp>";


        if(!empty($listStream)){
            foreach ($listStream as $stream) {

                $name = $stream['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";
                $linkStream= PluginWebapplicationsStream::getFormURLWithID($stream['id']);
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
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'data-bs-toggle' => 'modal', 'data-bs-target' =>'#editStream'.$stream['id']]);

                echo Ajax::createIframeModalWindow('editStream'.$stream['id'],
                    $linkStream,
                    ['display' => false,
                     'reloadonclose' => true]
                );
                echo "</td>";

                echo "</tr>";


                echo "<tr>";
                echo "<th>";
                echo __("Transmitter",'webapplications');
                echo "</th>";
                echo "<td>";

                $transmitterType = $stream['transmitter_type'];
                $transmitterid = $stream['transmitter'];
                if(!empty($transmitterType) && !empty($transmitterid)){
                    $transmitter = new $transmitterType;
                    $transmitter->getFromDB($transmitterid);
                    $linkTransmitter= $transmitterType::getFormURLWithID($transmitterid);
                    $transmitterName = $transmitter->getName();
                    echo "<a href=$linkTransmitter>$transmitterName</a>";
                }
                echo "</td>";


                echo "<th>";
                echo __("Receiver", 'webapplications');
                echo "</th>";
                echo "<td>";

                $receiverType = $stream['receiver_type'];
                $receiverid = $stream['receiver'];
                if(!empty($receiverType) && !empty($receiverid)) {
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
                echo __("Encryption");
                echo "</th>";
                echo "<td>";
                if($encryption==0){
                    echo __('No');
                }
                else echo __('Yes');
                echo "</td>";


                $encryption_type = $stream['encryption_type'];

                if($encryption==0){
                    echo "<td></td>";
                }
                else{
                    echo "<th>";
                    echo __("Encryption type");
                    echo "</th>";
                    echo "<td>";
                    echo $encryption_type;
                    echo "</td>";
                }
                echo "</tr>";



                $ports = $stream['ports'];

                echo "<tr>";
                echo "<th>";
                echo __("Port");
                echo "</th>";
                echo "<td>";
                echo $ports;
                echo "</td>";


                $protocole = $stream['protocole'];

                echo "<th>";
                echo __("Protocole",'webapplications');
                echo "</th>";
                echo "<td>";
                echo $protocole;
                echo "</td>";
                echo "</tr>";


                echo "</tbody>";
                echo "</table></div>";

            }
        }
        else echo __("No stream",'webapplications');
        echo "</div>";

        echo "<script>accordion();</script>";

    }


}
