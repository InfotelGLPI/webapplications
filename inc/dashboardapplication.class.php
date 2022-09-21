<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 webapplications plugin for GLPI
 Copyright (C) 2009-2016 by the webapplications Development Team.

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
 * Class PluginWebapplicationsDashboardApplication
 */
class PluginWebapplicationsDashboardApplication extends CommonDBTM {

    static $rightname         = "plugin_webapplications_application_dashboards";

    static function getTypeName($nb = 0) {

        return __('DashboardApplication', 'webapplications');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

        return __('Application', 'webapplications');

    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        self::showLists($item);
        return true;
    }


    function showForm($ID, $options = [])
    {

        global $CFG_GLPI;

        $options['candel'] = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance', 'webapplications') . "</td>";

        echo "<td >";
        $rand = Appliance::dropdown(['name' => 'applianceDropdown']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        echo "<div id=lists-Application></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-Application', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);

    }

    static function showLists($ApplianceId){

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);



        echo '<div class="card-header main-header d-flex flex-wrap mx-n2 mt-n2 align-items-stretch">
                        <h3 class="card-title d-flex align-items-center ps-4">
                                                <div class="ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1">
                     <i class="ti ti-versions fa-2x"></i>
                  </div>
                              <h3 style="margin: auto">';
        echo $appliance->getName();

        echo ' </h3>
                           </h3>
 </div>';

        echo "<h1>".__('Applications','webapplications')."</h1>";
        echo "<hr>";

        self::showListDatabase($ApplianceId);
        echo "<hr>";
        self::showListStream($ApplianceId);
        echo "<hr>";
        self::showSupportPart($ApplianceId);

        echo "<script>accordion();</script>";

    }


    static function showListDatabase($ApplianceId){


        $databaseDBTM = new DatabaseInstance();
        $linkAddDatabase=$databaseDBTM::getFormURL();

        $databasesAppDBTM = new Appliance_Item();
        $databaseApp = $databasesAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'DatabaseInstance']);


        $listDatabaseId = array();
        foreach ($databaseApp as $db) {
            $databasesAppDBTM->getFromDB($db['id']);

            array_push($listDatabaseId, $db['items_id']);
        }

        echo "<h2>";
        echo _n("Database", 'Databases', count($listDatabaseId));

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'style' => 'float: right',
            'onclick' => "window.location.href='" . $linkAddDatabase . "'"]);

        echo "</h2>";
        echo "<div class='accordion' name=listDatabaseApp>";

        if(!empty($listDatabaseId)){
            $databases = $databaseDBTM->find(['id' => $listDatabaseId]);
            foreach ($databases as $database) {

                $databaseDBTM->getFromDB($database['id']);

                $name = $database['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo $name;
                echo "</td>";

                $linkApp = DatabaseInstance::getFormURLWithID($database['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkApp . "'"]);
                echo "</td>";

                echo "</tr>";


                $comment = $database['comment'];

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo __("Comment");
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid white; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";



                $respSecurityid = $database['users_id_tech'];
                $respSecurity = new User();
                $respSecurity->getFromDB($respSecurityid);

                echo "<tr>";
                echo "<th>";
                echo __('Technician in charge of the hardware');
                echo "</th>";
                echo "<td>";
                echo $respSecurity->getName();
                echo "</td>";
                echo "</tr>";


                $techtypeid = $database['databaseinstancetypes_id'];
                $techtype= new DatabaseInstanceType();
                $techtype->getFromDB($techtypeid);

                echo "<tr>";
                echo "<th>";
                echo __("Technology type",'webapplication');
                echo "</th>";
                echo "<td>";
                echo $techtype->getName();
                echo "</td>";
                echo "</tr>";


                $streamDatabaseDBTM = new PluginWebapplicationsStream_Database();
                $streams = $streamDatabaseDBTM->find(['databaseinstances_id' => $database['id']]);
                $streamDBTM = new PluginWebapplicationsStream();

                echo "<tr>";
                echo "<th>";
                echo __("List Streams", 'webapplications');
                echo "</th>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td>";
                echo "<td>";
                if (!empty($streams)) {

                    echo "<select name='streams' id='list' Size='3' ondblclick='location = this.value;'>";
                    foreach ($streams as $stream) {
                        $streamDBTM->getFromDB($stream['plugin_webapplications_streams_id']);
                        $name = $streamDBTM->getName();
                        $link = PluginWebapplicationsStream::getFormURLWithID($stream['plugin_webapplications_streams_id']);
                        echo "<option value=$link>$name</option>";
                    }
                    echo "</select>";

                } else echo __("no associated stream",'webapplications');
                echo "</td>";
                echo "</tr>";


                echo "<tr>";
                echo "<th style='padding-top: 20px; padding-bottom: 20px'>";
                echo __('DICT','webapplications');
                echo "</th>";
                echo "<td class='inTable'>";


                $databaseplugin = new PluginWebapplicationsDatabase();
                $is_known = $databaseplugin->getFromDBByCrit(['databases_id'=>$database['id']]);

                if($is_known) {
                    $disp = $databaseplugin->fields['webapplicationavailabilities'];
                    $int = $databaseplugin->fields['webapplicationintegrities'];
                    $conf = $databaseplugin->fields['webapplicationconfidentialities'];
                    $tra = $databaseplugin->fields['webapplicationtraceabilities'];


                    echo "<table style='text-align : center; width: 60%'>";

                    echo "<table style='text-align : center; width: 60%'>";
                    echo "<td class='dict'>";
                    echo __('Availability')."&nbsp";
                    echo "</td>";

                    echo "<td name='webapplicationavailabilities' id='5'>";
                    echo $disp;
                    echo "</td>";

                    echo "<td></td>";

                    echo "<td class='dict'>";
                    echo __('Integrity','webapplications')."&nbsp";
                    echo "</td>";
                    echo "<td name='webapplicationintegrities' id='6'>";
                    echo $int;
                    echo "</td>";

                    echo "<td></td>";

                    echo "<td class='dict'>";
                    echo __('Confidentiality','webapplications')."&nbsp";
                    echo "</td>";
                    echo "<td name='webapplicationconfidentialities' id='7'>";
                    echo $conf;
                    echo "</td>";

                    echo "<td></td>";

                    echo "<td class='dict'>";
                    echo __('Tracabeality','webapplications')."&nbsp";
                    echo "</td>";
                    echo "<td name='webapplicationtraceabilities' id='8'>";
                    echo $tra;
                    echo "</td>";

                    echo "</table>";

                }
                else echo NOT_AVAILABLE;
                echo "</td>";
                echo "</tr>";


                echo "<tr>";
                echo "<th>";
                echo __("External Exposition", 'webapplications');
                echo "</th>";


                if($is_known) $extexpoid = $databaseplugin->fields['webapplicationexternalexpositions_id'];
                else $extexpoid = 0;
                $extexpo = new PluginWebapplicationsWebapplicationExternalExposition();
                $extexpo->getFromDB($extexpoid);

                echo "<td>";
                echo  $extexpo->getName();
                echo "</td>";
                echo "</tr>";


                echo "</tbody>";
                echo "</table></div>";

            }
        }
        else echo __("No database");
        echo "</div>";

    }

    static function showListStream($ApplianceId){



        $streamDBTM = new PluginWebapplicationsStream();
        $linkAddStream=$streamDBTM::getFormURL();


        $streamAppDBTM = new Appliance_Item();
        $streamApp = $streamAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsStream']);


        $listStreamId = array();
        foreach ($streamApp as $st) {
            $streamAppDBTM->getFromDB($st['id']);

            array_push($listStreamId, $st['items_id']);
        }

        echo "<h2>";
        echo _n("Stream",'Streams', count($listStreamId),'wbapplications');

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'style' => 'float: right',
            'onclick' => "window.location.href='" . $linkAddStream . "'"]);

        echo "</h2>";
        echo "<div class='accordion' name=listStreamApp>";


        if(!empty($listStreamId)){
            $streams = $streamDBTM->find(['id' => $listStreamId]);
            foreach ($streams as $stream) {

                $streamDBTM->getFromDB($stream['id']);

                $name = $stream['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo $name;
                echo "</td>";

                echo "<td></td>";

                $linkApp = PluginWebapplicationsStream::getFormURLWithID($stream['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkApp . "'"]);
                echo "</td>";

                echo "</tr>";


                $transmitter = $stream['transmitter'];

                echo "<tr>";
                echo "<th>";
                echo __("Transmitter",'webapplications');
                echo "</th>";
                echo "<td>";
                echo $transmitter;
                echo "</td>";


                $receiver = $stream['receiver'];

                echo "<th>";
                echo __("Receiver", 'webapplications');
                echo "</th>";
                echo "<td>";
                echo $receiver;
                echo "</td>";
                echo "</tr>";


                $encryption = $stream['encryption'];

                echo "<tr>";
                echo "<th>";
                echo __("Encryption");
                echo "</th>";
                echo "<td>";
                echo $encryption;
                echo "</td>";


                $encryption_type = $stream['encryption_type'];

                echo "<th>";
                echo __("Encryption type");
                echo "</th>";
                echo "<td>";
                echo $encryption_type;
                echo "</td>";
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

    }

    static function showSupportPart($ApplianceId){

        echo "<h2 style='margin-bottom: 15px'>";
        echo __("Support");

        $linkApp = Appliance::getFormURLWithID($ApplianceId);
        $linkApp .= "&forcetab=main";

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);

        echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'style' => 'float: right', 'onclick' => "window.location.href='" . $linkApp . "'"]);
        echo "</h2>";

        echo "<div id=supportApp>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tbody>";



        $refEditid = $appliance->getField('manufacturers_id');
        $refEdit = new Manufacturer();
        $refEdit->getFromDB($refEditid);


        echo "<tr>";
        echo "<th>";
        echo __("Referent editor",'webapplications');
        echo "</th>";
        echo "<td>";
        echo $refEdit->getName();
        echo "</td>";
        echo "</tr>";


        $applianceplugin = new PluginWebapplicationsAppliance();
        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id'=>$ApplianceId]);

        echo "<tr>";
        echo "<th>";
        echo __("Mail support",'webapplications');
        echo "</th>";
        echo "<td>";

        $mail = null;
        if($is_known) $mail = $applianceplugin->fields['webapplicationmailsupport'];

        if(!$is_known || $mail == null) $mail = NOT_AVAILABLE;

        echo $mail;
        echo "</td>";


        echo "<th>";
        echo __("Phone support",'webapplications');
        echo "</th>";
        echo "<td>";

        $phone = null;
        if($is_known) $phone = $applianceplugin->fields['webapplicationphonesupport'];

        if(!$is_known || $phone == null) $phone = NOT_AVAILABLE;

        echo $phone;
        echo "</td>";
        echo "</tr>";

        echo "</tbody>";
        echo "</table>";
        echo "</div>";

    }

}
