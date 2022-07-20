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
 * Class PluginWebapplicationsDashboardPhysicalInfrastructure
 */
class PluginWebapplicationsDashboardPhysicalInfrastructure extends CommonDBTM {

    static $rightname         = "plugin_webapplications_physical_infra_dashboards";

    static function getTypeName($nb = 0) {

        return __('DashboardPhysicalInfrastructure', 'webapplications');
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

        return __('Physical Infrastructure', 'webapplications');

    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        self::showForm($item);
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
        echo "<div id=lists-physicalInfra></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-physicalInfra', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);

    }

    static function showLists($ApplianceId){

        echo "<h1>Physical Infrastructure</h1>";
        echo "<hr>";

        self::showListComputer($ApplianceId);
        echo "<hr>";
        self::showListPeripheral($ApplianceId);
        echo "<hr>";
        self::showListPhone($ApplianceId);

        echo "<script>accordion();</script>";


    }

    static function showListComputer($ApplianceId){


        echo "<h2>";
        echo "Computer";

        $computerDBTM = new Computer();
        $linkAddComptuer=$computerDBTM::getFormURL();

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'style' => 'float: right',
            'onclick' => "window.location.href='" . $linkAddComptuer . "'"]);

        echo "</h2>";
        echo "<div class='accordion' name=listComputer>";

        $computerAppDBTM = new Appliance_Item();
        $computerApp = $computerAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'Computer']);


        $listComputerId = array();
        foreach ($computerApp as $st) {
            $computerAppDBTM->getFromDB($st['id']);

            array_push($listComputerId, $st['items_id']);
        }


        if(!empty($listComputerId)){
            $computers = $computerDBTM->find(['id' => $listComputerId]);
            foreach ($computers as $computer) {

                $computerDBTM->getFromDB($computer['id']);

                $name = $computer['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                echo "<tr>";
                echo "<th>";
                echo "Name";
                echo "</th>";
                echo "<td>";
                echo $name;
                echo "</td>";

                echo "<td></td>";

                $linkApp = Computer::getFormURLWithID($computer['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkApp . "'"]);
                echo "</td>";

                echo "</tr>";


                $typeId = $computer['computertypes_id'];
                $ct = new ComputerType();
                $ct->getFromDB($typeId);
                $type = $ct->getName();

                $modelId = $computer['computermodels_id'];
                $cm = new ComputerModel();
                $cm->getFromDB($modelId);
                $model = $cm->getName();

                $computerId = $computer['id'];
                $computerOSDBTM = new Item_OperatingSystem();
                $computerOSDBTM->getFromDBByCrit(['items_id' => $computerId, 'itemtype' => 'Computer']);

                $OSId = $computerOSDBTM->fields['operatingsystems_id'];
                $OS = new OperatingSystem();
                $OS->getFromDB($OSId);
                $OSName = $OS->getName();

                $OSVersionId = $computerOSDBTM->fields['operatingsystemversions_id'];
                $OSVersion = new OperatingSystemVersion();
                $OSVersion->getFromDB($OSVersionId);
                $OSVersionName = $OSVersion->getName();


                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo 'Technical characteristics';
                echo "</th>";
                echo "<td>";
                //echo $type." ".$model." ".$OSName." ".$OSVersionName;
                echo "<table style='width:60%'>";
                echo "<tr><td><b> Type </b></td>";
                echo "<td>" . $type . "</td></tr>";
                echo "<tr><td><b> Model </b> </td>";
                echo "<td>" . $model . "</td></tr>";
                echo "<tr><td><b> OS </b></td>";
                echo "<td>" . $OSName." ".$OSVersionName. "</td></tr>";
                echo "</table>";
                echo "</td>";
                echo "</tr>";


             /*   $comment = $computer['comment'];

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo 'Comment';
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid white; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";*/



                $location = new Location();
                $location->getFromDB($computer['locations_id']);
                $locationName = $location->getName();
                $link = Location::getFormURLWithID($computer['locations_id']);

                echo "<tr>";
                echo "<th>";
                echo "Location";
                echo "</th>";
                echo "<td>";
                echo "<a href='" . $link . "'> ";
                echo $locationName;
                echo "</a>";
                echo "</td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table></div>";

            }
        }
        else echo "No Computer";
        echo "</div>";
    }

    static function showListPeripheral($ApplianceId){


        echo "<h2>";
        echo "Peripheral";

        $peripheralDBTM = new Peripheral();
        $linkAddPeripheral=$peripheralDBTM::getFormURL();

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'style' => 'float: right',
            'onclick' => "window.location.href='" . $linkAddPeripheral . "'"]);

        echo "</h2>";
        echo "<div class='accordion' name=listPeripheral>";

        $peripheralAppDBTM = new Appliance_Item();
        $peripheralApp = $peripheralAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'Peripheral']);


        $listPeripheralId = array();
        foreach ($peripheralApp as $st) {
            $peripheralAppDBTM->getFromDB($st['id']);

            array_push($listPeripheralId, $st['items_id']);
        }


        if(!empty($listPeripheralId)){
            $peripherals = $peripheralDBTM->find(['id' => $listPeripheralId]);
            foreach ($peripherals as $peripheral) {

                $peripheralDBTM->getFromDB($peripheral['id']);

                $name = $peripheral['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                echo "<tr>";
                echo "<th>";
                echo "Name";
                echo "</th>";
                echo "<td>";
                echo $name;
                echo "</td>";

                echo "<td></td>";

                $linkApp = Peripheral::getFormURLWithID($peripheral['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkApp . "'"]);
                echo "</td>";

                echo "</tr>";


                $comment = $peripheral['comment'];

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo "Comment";
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid white; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";



                $tech = new User();
                $tech->getFromDB($peripheral['users_id_tech']);
                $techName = $tech->getName();
                $link = User::getFormURLWithID($peripheral['users_id_tech']);

                echo "<tr>";
                echo "<th>";
                echo "operations manager";
                echo "</th>";
                echo "<td>";
                echo "<a href='" . $link . "'> ";
                echo $techName;
                echo "</a>";
                echo "</td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table></div>";

            }
        }
        else echo "No Peripheral";
        echo "</div>";
    }

    static function showListPhone($ApplianceId){


        echo "<h2>";
        echo "Phone";

        $phoneDBTM = new Phone();
        $linkAddPhone=$phoneDBTM::getFormURL();

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'style' => 'float: right',
            'onclick' => "window.location.href='" . $linkAddPhone . "'"]);

        echo "</h2>";
        echo "<div class='accordion' name=listPhone>";

        $phoneAppDBTM = new Appliance_Item();
        $phoneApp = $phoneAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'Phone']);


        $listPhoneId = array();
        foreach ($phoneApp as $st) {
            $phoneAppDBTM->getFromDB($st['id']);

            array_push($listPhoneId, $st['items_id']);
        }


        if(!empty($listPhoneId)){
            $phones = $phoneDBTM->find(['id' => $listPhoneId]);
            foreach ($phones as $phone) {

                $phoneDBTM->getFromDB($phone['id']);

                $name = $phone['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                echo "<tr>";
                echo "<th>";
                echo "Name";
                echo "</th>";
                echo "<td>";
                echo $name;
                echo "</td>";

                echo "<td></td>";

                $linkApp = Phone::getFormURLWithID($phone['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkApp . "'"]);
                echo "</td>";

                echo "</tr>";


                $comment = $phone['comment'];

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo "Comment";
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid white; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";



                $location = new Location();
                $location->getFromDB($phone['locations_id']);
                $locationName = $location->getName();
                $link = Location::getFormURLWithID($phone['locations_id']);

                echo "<tr>";
                echo "<th>";
                echo "Location";
                echo "</th>";
                echo "<td>";
                echo "<a href='" . $link . "'> ";
                echo $locationName;
                echo "</a>";
                echo "</td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table></div>";

            }
        }
        else echo "No Phone";
        echo "</div>";
    }





}