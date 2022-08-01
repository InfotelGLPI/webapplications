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
 * Class PluginWebapplicationsDashboard
 */
class PluginWebapplicationsDashboard extends CommonDBTM {

    static $rightname = "plugin_webapplications_dashboards";

    static function getTypeName($nb = 0) {

        return __('Dashboard', 'webapplications');
    }

    static function getIcon() {
        return "fas fa-fw fa-border-all";
    }



    function showForm($ID, $options = []) {
        global  $CFG_GLPI;

        $options['candel']  = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance', 'webapplications') . "</td>";

        echo "<td >";
        $rand = Appliance::dropdown(['name' => 'applianceDropdown']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        echo "<div id=lists-dashboard></div>";

        $array['value']='__VALUE__';
        $array['type']=self::getType();
        Ajax::updateItemOnSelectEvent('dropdown_applianceDropdown'.$rand, 'lists-dashboard', $CFG_GLPI['root_doc'].PLUGIN_WEBAPPLICATIONS_DIR_NOFULL.'/ajax/getLists.php', $array);

    }

    static function getURLForPicture($ApplianceId) {
        global $CFG_GLPI;



        if (!empty($picture)) {
            $tmp = explode(".", $picture);

            if (count($tmp) == 2) {
                return $CFG_GLPI["root_doc"] . "/front/document.send.php?file=_pictures/" . $tmp[0] .
                    "." . $tmp[1];
            }
        }
        return PLUGIN_SERVICECATALOG_WEBDIR . "/pics/picture_links.png";

    }

    static function showLists($ApplianceId){

        global $CFG_GLPI;

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);

        $pictures = importArrayFromDB($appliance->getField('pictures'));

        if(!empty($pictures)){
            $urlPicture = $pictures[0];

            $rand = mt_rand();

            echo "<div style='width:190px; text-align:center;' id='picture$rand'>";
            echo "<img alt=\"" . __s('Picture') . "\" src='" .
                $CFG_GLPI["root_doc"] . "/front/document.send.php?file=_pictures/" . $urlPicture . "'>";
            echo "</div>";
        }



        echo "<div class=accueilDashboard>";
        echo "<h1>Abstract</h1>";

        echo "<hr>";
        echo "<h3>Ecosystem</h3>";

        $applianceplugin = new PluginWebapplicationsAppliance();
        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id'=>$ApplianceId]);
        $extexpoid = $applianceplugin->getField('webapplicationexternalexpositions_id');

        $extexpo = new PluginWebapplicationsWebapplicationExternalExposition();
        $extexpo->getFromDB($extexpoid);
        $extexpoName = $extexpo->getName();

        $respSecurityid = $appliance->getField('users_id_tech');
        $respSecurity = new User();
        $respSecurity->getFromDB($respSecurityid);

        $respSec = $respSecurity->getName();

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<td>External Exposition</td>";
        echo "<td>$extexpoName</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Security manager</td>";
        echo "<td>$respSec</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        echo "DICT";
        echo "</td>";
        echo "<td>";


        if($is_known){
            $disp = $applianceplugin->fields['webapplicationavailabilities'];
            $int = $applianceplugin->fields['webapplicationintegrities'];
            $conf = $applianceplugin->fields['webapplicationconfidentialities'];
            $tra = $applianceplugin->fields['webapplicationtraceabilities'];


            echo "<table style='text-align : center; width: 60%'>";

            echo "<td class='dict'>";
            echo "Availability &nbsp";
            echo "</td>";

            echo "<td name='webapplicationavailabilities' id='5'>";
            echo $disp;
            echo "</td>";

            echo "<td></td>";

            echo "<td class='dict'>";
            echo "Integrity &nbsp";
            echo "</td>";
            echo "<td name='webapplicationintegrities' id='6'>";
            echo $int;
            echo "</td>";

            echo "<td></td>";

            echo "<td class='dict'>";
            echo "Confidentiality &nbsp";
            echo "</td>";
            echo "<td name='webapplicationconfidentialities' id='7'>";
            echo $conf;
            echo "</td>";

            echo "<td></td>";

            echo "<td class='dict'>";
            echo "Tracabeality &nbsp";
            echo "</td>";
            echo "<td name='webapplicationtraceabilities' id='8'>";
            echo $tra;
            echo "</td>";

            echo "</table>";
        }
        else echo NOT_AVAILABLE;
        echo "</td>";
        echo "</tr>";


        echo "</table>";


        echo "<hr>";
        echo "<h3>Process</h3>";


        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsProcess']);
        $processDBTM = new PluginWebapplicationsProcess();
        $test = null;

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";

        echo "<td>";
        echo "List Process";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        if (!empty($procsApp)) {

            echo "<select name='processes' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($procsApp as $procApp) {
                $processDBTM->getFromDB($procApp['items_id']);
                $name = $processDBTM->getName();
                $link = PluginWebapplicationsProcess::getFormURLWithID($procApp['items_id']);
                echo "<option value=$link>$name</option>";
            }
            echo "</select>";

        } else echo "no associated process";
        echo "</td>";
        echo "</tr>";
        echo "</table>";



        echo "<hr>";
        echo "<h3>Application</h3>";
        echo "<hr>";
        echo "<h3>Administration</h3>";
        echo "<hr>";
        echo "<h3>Logicial Infrastructure</h3>";
        echo "<hr>";
        echo "<h3>Physical Infrastruture</h3>";


        echo "</div>";
    }



    function defineTabs($options = []) {

        echo Html::css(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/lib/jquery-ui/jquery-ui.min.css");
        echo Html::script(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/lib/jquery-ui/jquery-ui.min.js");
        echo "<link rel='stylesheet' href='../css/style.css'>";
        echo $JS = <<<JAVASCRIPT
         <script type='text/javascript'>
         function accordion(classname) {
             if(classname == undefined){
                 classname  = 'accordion';
             }
             jQuery(document).ready(function () {
                 $("."+classname).accordion({
                     collapsible: true,
                     heightStyle: "content",
                     active: false
                 });
             });
         };
         </script>
        JAVASCRIPT;

        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('PluginWebapplicationsDashboardEcosystem', $ong, $options);// Vue Ecosystème
        $this->addStandardTab('PluginWebapplicationsDashboardProcess', $ong, $options);//Vue Metier
        $this->addStandardTab('PluginWebapplicationsDashboardApplication', $ong, $options);//Vue Applications
        $this->addStandardTab('PluginWebapplicationsDashboardAdministration', $ong, $options);//Vue Administration
        $this->addStandardTab('PluginWebapplicationsDashboardLogicialInfrastructure', $ong, $options);//Vue Infra logiques
        $this->addStandardTab('PluginWebapplicationsDashboardPhysicalInfrastructure', $ong, $options);//Vue Infra physiques


        return $ong;
    }


}
