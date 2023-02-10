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
 * Class PluginWebapplicationsDashboard
 */
class PluginWebapplicationsDashboard extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_dashboards";

    public static function getTypeName($nb = 0)
    {
        return __('Appliance dashboard', 'webapplications');
    }

    public function getHeaderName($options = []): string
    {
        $appId = $_SESSION['plugin_webapplications_loaded_appliances_id'];
        $appliance = new Appliance();
        $appliance->getFromDB($appId);
        return $appliance->getName();
    }


    public static function getMenuContent()
    {
        $menu = [];
        $menu['title'] = self::getMenuName();
        $menu['page'] = self::getSearchURL(false);

        $menu['icon'] = self::getIcon();

        return $menu;
    }

    public static function getIcon()
    {
        return "fas fa-fw fa-border-all";
    }


    public static function selectAppliance()
    {
        global $CFG_GLPI;
        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance') . "</td>";

        echo "<td >";

        $rand = Appliance::dropdown(['name' => 'applianceDropdown']);

        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        echo "<div id=lists-dashboard></div>";

        $array['value'] = '__VALUE__';
        $array['type'] = self::getType();
        $array['reload'] = false;
        Ajax::updateItemOnSelectEvent(
            'dropdown_applianceDropdown' . $rand,
            'lists-dashboard',
            $CFG_GLPI['root_doc'] . PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . '/ajax/getLists.php',
            $array
        );


        if (isset($_SESSION['reload']) && $_SESSION['reload']) {
            unset($_SESSION['reload']);
            $array['reload'] = true;
            $array['value'] = $_SESSION['plugin_webapplications_loaded_appliances_id'];
            Ajax::updateItem('lists-dashboard', $CFG_GLPI['root_doc'] . PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . '/ajax/getLists.php', $array, 'dropdown_applianceDropdown' . $rand);
        }
    }

    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        $options['candel'] = false;
        $options['colspan'] = 1;

        $ApplianceId = $options['appliances_id'];

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);


        echo '<div class="card-header main-header d-flex flex-wrap mx-n2 mt-n2 align-items-stretch">
              <h3 class="card-title d-flex align-items-center ps-4">
              <div class="ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1">
              <i class="ti ti-versions fa-2x"></i>
              </div>
               </h3>';

        $pictures = importArrayFromDB($appliance->getField('pictures'));


        if (!empty($pictures)) {
            $urlPicture = $pictures[0];

            $rand = mt_rand();

            echo "<div style='width:150px; text-align:center;' id='picture$rand'>";
            echo "<img alt=\"" . __s('Picture') . "\" src='" .
                $CFG_GLPI["root_doc"] . "/front/document.send.php?file=_pictures/" . $urlPicture . "'>";
            echo "</div>";
        }

        echo '<h1 style="margin: auto">';
        $linkApp = Appliance::getFormURLWithID($ApplianceId);
        $name = $appliance->getName();
        echo "<a href=$linkApp>$name</a>";

        echo ' </h1>';

        $linkApp = PluginWebapplicationsAppliance::getFormURLWithID($ApplianceId);
        $linkApp .= "&forcetab=main";

        echo "<div style='align-self: center'>";

        echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'style' => 'float: right', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#editApp' . $ApplianceId]);

        echo Ajax::createIframeModalWindow(
            'editApp' . $ApplianceId,
            $linkApp,
            ['display' => false,
                'reloadonclose' => true]
        );


        echo "</div>";

        echo '</div>';


        echo "<table class='tab_cadre_fixe'><tr><td>";

        $groupId = $appliance->getField('groups_id');
        $groupUserDBTM = new Group_User();
        $groupUser = $groupUserDBTM->find(['groups_id' => $groupId]);

        $groupAdminId = $appliance->getField('groups_id_tech');
        $groupUserAdminDBTM = new Group_User();
        $groupUserAdmin = $groupUserAdminDBTM->find(['groups_id' => $groupAdminId]);
        $numberAdmin = count($groupUserAdmin);

        $listUser = array_merge($groupUser, $groupUserAdmin);
        $listUniqueUser = array();
        foreach ($listUser as $user) {
            array_push($listUniqueUser, $user['users_id']);
        }
        $numberUser = count(array_unique($listUniqueUser));

        echo "<td>";
        echo "<div style='text-align:center'>";
        echo "<i class='fa fa-users fa-3x'>";
        echo "<br>$numberUser</i>";
        echo "<br>" . User::getTypeName($numberUser);
        echo "</div>";
        echo "</td>";

        echo "<td>";
        echo "<div style='text-align:center'>";
        echo "<i class='fa fa-circle-user fa-3x'>";
        echo "<br>$numberAdmin</i>";
        echo "<br>" . _n('Administrator', 'Administrators', $numberAdmin, 'webapplications');
        echo "</div>";
        echo "</td>";


        $documentItemDBTM = new Document_Item();
        $docuItems = $documentItemDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);
        $docuDBTM = new Document();

        echo "<td>";
        if (!empty($docuItems)) {
            echo "<div>";
            echo _n('Associated document', 'Associated documents', count($docuItems), 'webapplications');
            echo "<br><select name='documents' id='list' Size='3' ondblclick='location = this.value;' style='max-width: 400px'>";
            foreach ($docuItems as $docuItem) {
                $docuDBTM->getFromDB($docuItem['documents_id']);
                $name = $docuDBTM->getName();
                $link = Document::getFormURLWithID($docuItem['documents_id']);
                echo "<option value=$link>$name</option>";
            }
            echo "</select>";
            echo "</div>";
        } else {
            echo __("No associated documents");
        }


        echo "</td></tr></table>";


        echo "<div class=accueilDashboard>";
        echo "<h1>" . __('Summary', 'webapplications') . "</h1>";

        echo "<hr>";

        self::showEcosystem($appliance);

        echo "<hr>";

        self::showProcess($appliance);

        echo "<hr>";
        self::showApplication($appliance);

        echo "<hr>";
        self::showSupportPart($appliance);


        echo "</div>";
    }

    public static function getURLForPicture()
    {
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


    public static function showEcosystem($appliance)
    {
        echo "<h3>" . __('Ecosystem', 'webapplications') . "</h3>";

        $ApplianceId = $appliance->getField('id');

        $applianceplugin = new PluginWebapplicationsAppliance();
        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id' => $ApplianceId]);

        $extexpoid = $applianceplugin->getField('webapplicationexternalexpositions_id');

        $extexpo = new PluginWebapplicationsWebapplicationExternalExposition();
        $extexpo->getFromDB($extexpoid);
        $extexpoName = $extexpo->getName();

        $respSecurityid = $appliance->getField('users_id_tech');
        $respSecurity = new User();
        $respSecurity->getFromDB($respSecurityid);

        $link = User::getFormURLWithID($respSecurityid);
        $respSec = $respSecurity->getName();

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<td><h4>" . _n('External exposition', 'External exposition', 1, 'webapplications').
        "</h4></td>";
        echo "<td>$extexpoName</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td><h4>" . __('Technician in charge of the hardware') . "</h4></td>";
        if ($respSecurityid > 0) {
            echo "<td><a href=$link>$respSec</a></td>";
        } else {
            echo "<td>$respSec</td>";
        }
        echo "</tr>";


        $stateId = $appliance->getField('states_id');
        $state = new State();
        $state->getFromDB($stateId);
        $stateName = $state->getName();

        echo "<tr>";
        echo "<td><h4>" . __('Status') . "</h4></td>";
        echo "<td>$stateName</td>";
        echo "</tr>";

        $serverTypeId = $applianceplugin->getField('webapplicationservertypes_id');
        $serverType = new PluginWebapplicationsWebapplicationServerType();
        $serverType->getFromDB($serverTypeId);
        $serverTypeName = $serverType->getName();

        echo "<tr>";
        echo "<td><h4>" . __('Type of treatment server', 'webapplications') . "</h4></td>";
        echo "<td>$serverTypeName</td>";
        echo "</tr>";


        $technicId = $applianceplugin->getField('webapplicationtechnics_id');
        $technic = new PluginWebapplicationsWebapplicationTechnic();
        $technic->getFromDB($technicId);
        $technicName = $technic->getName();

        echo "<tr>";
        echo "<td><h4>" . __('Language of treatment', 'webapplications') . "</h4></td>";
        echo "<td>$technicName</td>";
        echo "</tr>";


        echo "<tr>";
        echo "<td>";
        echo "<h4>" . __('DICT', 'webapplications') . "</h4>";
        echo "</td>";
        echo "<td class='inTable'>";


        if ($is_known) {
            $disp = $applianceplugin->fields['webapplicationavailabilities'];
            $int = $applianceplugin->fields['webapplicationintegrities'];
            $conf = $applianceplugin->fields['webapplicationconfidentialities'];
            $tra = $applianceplugin->fields['webapplicationtraceabilities'];


            echo "<table style='text-align : center; width: 60%'>";
            echo "<td class='dict'>";
            echo __('Availability') . "&nbsp";
            echo "</td>";

            echo "<td name='webapplicationavailabilities' id='5'>";
            echo $disp;
            echo "</td>";

            echo "<td></td>";

            echo "<td class='dict'>";
            echo __('Integrity', 'webapplications') . "&nbsp";
            echo "</td>";
            echo "<td name='webapplicationintegrities' id='6'>";
            echo $int;
            echo "</td>";

            echo "<td></td>";

            echo "<td class='dict'>";
            echo __('Confidentiality', 'webapplications') . "&nbsp";
            echo "</td>";
            echo "<td name='webapplicationconfidentialities' id='7'>";
            echo $conf;
            echo "</td>";

            echo "<td></td>";

            echo "<td class='dict'>";
            echo __('Tracabeality', 'webapplications') . "&nbsp";
            echo "</td>";
            echo "<td name='webapplicationtraceabilities' id='8'>";
            echo $tra;
            echo "</td>";

            echo "</table>";
        } else {
            echo NOT_AVAILABLE;
        }
        echo "</td>";
        echo "</tr>";

        $version = $applianceplugin->getField('version');

        echo "<tr>";
        echo "<td><h4>" . __('Installed version', 'webapplications') . "</h4></td>";
        echo "<td>$version</td>";
        echo "</tr>";


        $backoffice = $applianceplugin->getField('backoffice');

        echo "<tr>";
        echo "<td><h4>" . __('Backoffice URL', 'webapplications') . "</h4></td>";
        echo "<td><a href=$backoffice>$backoffice</a></td>";
        echo "</tr>";

        $comment = $appliance->getField('comment');

        echo "<tr>";
        echo "<td>";
        echo "<h4>" . __('Comment') . "</h4>";
        echo "</td>";
        echo "<td>";
        if (!empty($comment)) {
            echo "<table style='border:1px solid; width:60%'>";
            echo "<td>" . $comment . "</td>";
            echo "</table>";
        }
        echo "</td>";
        echo "</tr>";


        echo "</table>";
    }

    public static function showProcess($appliance)
    {
        echo "<h3>" . __('Process', 'webapplications') . "</h3>";

        $ApplianceId = $appliance->getField('id');

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsProcess']);
        $processDBTM = new PluginWebapplicationsProcess();

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";

        echo "<td>";
        echo "<h4>" . __('List Processes', 'webapplications') . "</h4>";
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
        } else {
            echo __("No associated process", 'webapplications');
        }
        echo "</td>";
        echo "</tr>";
        echo "</table>";
    }

    public static function showApplication($appliance)
    {
        echo "<h3>" . __('Application', 'webapplications') . "</h3>";

        $ApplianceId = $appliance->getField('id');

        $databasesAppDBTM = new Appliance_Item();
        $databasesApp = $databasesAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'DatabaseInstance']);
        $databaseDBTM = new DatabaseInstance();

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";

        echo "<td>";
        echo "<h4>" . __('List Databases', 'webapplications') . "</h4>";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        if (!empty($databasesApp)) {
            echo "<select name='databases' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($databasesApp as $dbApp) {
                $databaseDBTM->getFromDB($dbApp['items_id']);
                $name = $databaseDBTM->getName();
                $link = DatabaseInstance::getFormURLWithID($dbApp['items_id']);
                echo "<option value=$link>$name</option>";
            }
            echo "</select>";
        } else {
            echo __("No associated database", 'webapplications');
        }
        echo "</td>";
        echo "</tr>";

        echo "</table>";
    }

    public static function showSupportPart($appliance)
    {
        echo "<h2 style='margin-bottom: 15px'>";
        echo __("Support");

        $ApplianceId = $appliance->getField('id');

        $linkApp = PluginWebapplicationsAppliance::getFormURLWithID($ApplianceId);
        $linkApp .= "&forcetab=main";

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);

        echo Html::submit(_sx('button', 'Edit'), ['name' => 'edit', 'class' => 'btn btn-secondary', 'icon' => 'fas fa-edit', 'style' => 'float: right', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#editAppSupport' . $ApplianceId]);

        echo Ajax::createIframeModalWindow(
            'editAppSupport' . $ApplianceId,
            $linkApp,
            ['display' => false,
                'reloadonclose' => true]
        );
        echo "</h2>";

        echo "<div id=supportApp>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tbody>";

        $refEditid = $appliance->getField('manufacturers_id');
        $refEdit = new Manufacturer();
        $refEdit->getFromDB($refEditid);
        echo "<tr>";
        echo "<th>";
        echo __("Referent editor", 'webapplications');
        echo "</th>";
        echo "<td>";
        echo $refEdit->getName();
        echo "</td>";
        echo "</tr>";

        $applianceplugin = new PluginWebapplicationsAppliance();
        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id' => $ApplianceId]);

        echo "<tr>";
        echo "<th>";
        echo __("Mail support", 'webapplications');
        echo "</th>";
        echo "<td>";

        $mail = null;
        if ($is_known) {
            $mail = $applianceplugin->fields['webapplicationmailsupport'];
        }

        if (!$is_known || $mail == null) {
            $mail = NOT_AVAILABLE;
        }

        echo $mail;
        echo "</td>";

        echo "<th>";
        echo __("Phone support", 'webapplications');
        echo "</th>";
        echo "<td>";

        $phone = null;
        if ($is_known) {
            $phone = $applianceplugin->fields['webapplicationphonesupport'];
        }

        if (!$is_known || $phone == null) {
            $phone = NOT_AVAILABLE;
        }

        echo $phone;
        echo "</td>";
        echo "</tr>";

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }

    public function defineTabs($options = [])
    {
        echo Html::css(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/lib/jquery-ui/jquery-ui.min.css");
        echo Html::script(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/lib/jquery-ui/jquery-ui.min.js");
        echo Html::css(PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . "/css/webapplications.css");

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
        $this->addStandardTab('PluginWebapplicationsDashboardPhysicalInfrastructure', $ong, $options);//Vue Infra physiques
        $this->addStandardTab('PluginWebapplicationsDashboardDatabase', $ong, $options);//Vue Base de données
        $this->addStandardTab('PluginWebapplicationsDashboardStream', $ong, $options);//Vue Flux

        return $ong;
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }
}
