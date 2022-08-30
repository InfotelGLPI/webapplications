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
 * Class PluginWebapplicationsDashboardAdministration
 */
class PluginWebapplicationsDashboardAdministration extends CommonDBTM {

    static $rightname         = "plugin_webapplications_administration_dashboards";

    static function getTypeName($nb = 0) {

        return __('DashboardAdministration', 'webapplications');
    }

    static function getIndexName()
    {
        return 'Administration';
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

        return __('Administration', 'webapplications');

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
        echo "<div id=lists-Administration></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-Administration', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);
    }

    static function showLists($item) {

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        echo "<h1>Administration</h1>";
        echo "<hr>";

        //self::showListLDAP($ApplianceId);

        echo "<script>accordion();</script>";


    }

    static function showListLDAP($ApplianceId){



        echo "<h2>";
        echo "Domain Active Directory / LDAP";

        $domainDBTM = new Domain();
        $linkAddDomain=$domainDBTM::getFormURL();

        echo Html::submit(_sx('button', 'Add'), ['name' => 'edit',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus',
            'style' => 'float: right',
            'onclick' => "window.location.href='" . $linkAddDomain . "'"]);

        echo "</h2>";
        echo "<div class='accordion' name=listLDAP>";

        $domainAppDBTM = new Domain_Item();
        $domainApp = $domainAppDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);


        $listDomainId = array();
        foreach ($domainApp as $domApp) {
            $domainAppDBTM->getFromDB($domApp['id']);

            array_push($listDomainId, $domApp['domains_id']);
        }


        if(!empty($listDomainId)){
            $domains = $domainDBTM->find(['id' => $listDomainId]);
            foreach ($domains as $domain) {

                $domainDBTM->getFromDB($domain['id']);

                $name = $domain['name'];

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

                $linkApp = Domain::getFormURLWithID($domain['id']);

                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'onclick' => "window.location.href='" . $linkApp . "'"]);
                echo "</td>";

                echo "</tr>";


                $comment = $domain['comment'];

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

                echo "</tbody>";
                echo "</table></div>";

            }
        }
        else echo "No Domain";
        echo "</div>";

    }






}
