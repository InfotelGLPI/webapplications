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
        $appId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;
        if (!isset($_SESSION['plugin_webapplications_loaded_appliances_id'])) {
            $_SESSION['plugin_webapplications_loaded_appliances_id'] = 0;
        }
        if ($appId > 0) {
            $appliance = new Appliance();
            $appliance->getFromDB($appId);
            return $appliance->getName();
        }
        return "";
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


    public static function selectAppliance($id)
    {
        global $CFG_GLPI;

        echo "<div align='center'>
        <table class='tab_cadre'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance') . "</td>";

        echo "<td >";

        $rand = Appliance::dropdown(['name' => 'applianceDropdown', 'value' => $id]);

        echo "</td>";
        echo "</tr>";
        echo "</table></div>";


        echo "<div id='lists-dashboard'>";
        if ($id > 0) {
            $dashboard = new PluginWebapplicationsDashboard();
            $dashboard->display(['id' => 1, 'appliances_id' => $id]);
        }

        echo "</div>";

        $array['value'] = '__VALUE__';
        $array['type'] = self::getType();
        $array['reload'] = false;
        Ajax::updateItemOnSelectEvent(
            'dropdown_applianceDropdown' . $rand,
            'lists-dashboard',
            $CFG_GLPI['root_doc'] . PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . '/ajax/getLists.php',
            $array
        );
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

//        $pictures = importArrayFromDB($appliance->getField('pictures'));
//
//
//        if (!empty($pictures)) {
//            $urlPicture = $pictures[0];
//
//            $rand = mt_rand();
//
//            echo "<div style='width:150px; text-align:center;' id='picture$rand'>";
//            echo "<img alt=\"" . __s('Picture') . "\" src='" .
//                $CFG_GLPI["root_doc"] . "/front/document.send.php?file=_pictures/" . $urlPicture . "'>";
//            echo "</div>";
//        }

        echo '<h1 style="margin: auto">';
        $linkApp = Appliance::getFormURLWithID($ApplianceId);
        $name = $appliance->getName();
        echo "<a href='$linkApp'>$name</a>";
        echo "</h1>";

        $linkApp = PluginWebapplicationsAppliance::getFormURLWithID($ApplianceId);
        $linkApp .= "&forcetab=main";

        echo "<div style='align-self: center'>";
        echo Html::submit(
            _sx('button', 'Edit'),
            [
                'name' => 'edit',
                'class' => 'btn btn-secondary',
                'icon' => 'fas fa-edit',
                'style' => 'float: right',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#editApp' . $ApplianceId
            ]
        );
        echo Ajax::createIframeModalWindow(
            'editApp' . $ApplianceId,
            $linkApp,
            [
                'display' => false,
                'reloadonclose' => true
            ]
        );
        echo "</div>";

        echo '</div>';

        echo '<div class="card-body">';
        echo "<table class='tab_cadre_fixe'><tr>";

        $groupId = $appliance->getField('groups_id');
        $groupUserDBTM = new Group_User();
        $groupUser = array_column($groupUserDBTM->find(['groups_id' => $groupId]), 'users_id');

        $userAdminId = $appliance->getField('users_id_tech');
        $groupAdminId = $appliance->getField('groups_id_tech');

        $groupUserAdminDBTM = new Group_User();
        $groupUserAdmin = array_column($groupUserAdminDBTM->find(['groups_id' => $groupAdminId]), 'users_id');
        $numberAdmin = count(array_unique($groupUserAdmin));

        $listUser = array_merge($groupUser, $groupUserAdmin);
        $numberUser = count(array_unique($listUser));

        echo "<td>";
        echo "<div style='text-align:center;font-weight: bold'>";
        echo "<h2>";
        echo User::getTypeName($numberUser);
        echo "</h2>";
        echo "<i class='fa fa-users fa-3x'></i>";
        echo "<br>";
        echo "<h1>";
        echo $numberUser;
        echo "</h1>";
        echo "</div>";
        echo "</td>";

        if ($userAdminId > 0) {
            echo "<td>";
            echo "<div style='text-align:center;font-weight: bold'>";
            echo "<h2>";
            echo __('Project leader', 'webapplications');
            echo "</h2>";
            echo "<i class='fa fa-user-cog fa-3x'></i>";
            echo "<br>";
            echo "<h2>";
            echo getUserName($userAdminId);
            echo "</h2>";
            echo "</div>";
            echo "</td>";
        }

        if ($numberAdmin > 0) {
            echo "<td>";
            echo "<div style='text-align:center;font-weight: bold'>";
            echo "<h2>";
            echo __('Project team', 'webapplications');
            echo "</h2>";
            echo "<i class='fa fa-users-cog fa-3x'></i>";
            echo "<br>";
            echo "<h1>";
            echo $numberAdmin;
            echo "</h1>";
            echo "</div>";
            echo "</td>";
        }

        $documentItemDBTM = new Document_Item();
        $docuItems = $documentItemDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);
        $docuDBTM = new Document();

        echo "<td>";
        if (count($docuItems) > 0) {
            echo "<div>";
            echo _n('Associated document', 'Associated documents', count($docuItems), 'webapplications');

            echo "<br><select name='documents' id='list' Size='3' onclick=\"window.open(this.value, '_blank');\" style='max-width: 400px'>";
            foreach ($docuItems as $docuItem) {
                $docuDBTM->getFromDB($docuItem['documents_id']);
                $name = $docuDBTM->getName();
                $open  = $CFG_GLPI["root_doc"] . "/front/document.send.php";
                $open   .= (strpos($open, '?') ? '&' : '?') . 'docid=' . $docuItem['documents_id'];
                echo "<option value='$open'>$name</option>";
            }
            echo "</select>";

            echo "</div>";
        } else {
            echo __("No associated documents", 'webapplications');
        }

        echo "</td></tr></table>";
        echo "</div>";

        echo "<div class='card-body border-0'>";
        echo "<h3 class='card-subtitle mb-2 text-muted'>" . __('Summary', 'webapplications') . "</h3>";
        echo "</div>";

        $options = [];
        $options['canedit'] = false;
        $options['candel'] = false;

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_summary.html.twig', [
            'item'   => $appliance,
            'params' => $options,
            'no_header'     => true,
        ]);


//        $ApplianceId = $appliance->getField('id');
//
//        $applianceplugin = new PluginWebapplicationsAppliance();
//        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id' => $ApplianceId]);
//
//        $extexpoid = $applianceplugin->getField('webapplicationexternalexpositions_id');
//
//        $extexpo = new PluginWebapplicationsWebapplicationExternalExposition();
//        $extexpo->getFromDB($extexpoid);
//        $extexpoName = $extexpo->getName();
//
//        $respSecurityid = $appliance->getField('users_id_tech');
//        $respSecurity = new User();
//        $respSecurity->getFromDB($respSecurityid);
//
//        $link = User::getFormURLWithID($respSecurityid);
//        $respSec = $respSecurity->getName();



//        echo "<table class='tab_cadre_fixe'>";
//        echo "<tr>";
//        echo "<td><h4>" . _n('External exposition', 'External exposition', 1, 'webapplications') .
//            "</h4></td>";
//        echo "<td>$extexpoName</td>";
//        echo "</tr>";
//        echo "<tr>";
//        echo "<td><h4>" . __('Technician in charge') . "</h4></td>";
//        if ($respSecurityid > 0) {
//            echo "<td><a href=$link>$respSec</a></td>";
//        } else {
//            echo "<td>$respSec</td>";
//        }
//        echo "</tr>";
//
//
//        $stateId = $appliance->getField('states_id');
//        $state = new State();
//        $state->getFromDB($stateId);
//        $stateName = $state->getName();
//
//        echo "<tr>";
//        echo "<td><h4>" . __('Status') . "</h4></td>";
//        echo "<td>$stateName</td>";
//        echo "</tr>";
//
//        $serverTypeId = $applianceplugin->getField('webapplicationservertypes_id');
//        $serverType = new PluginWebapplicationsWebapplicationServerType();
//        $serverType->getFromDB($serverTypeId);
//        $serverTypeName = $serverType->getName();
//
//        echo "<tr>";
//        echo "<td><h4>" . __('Type of treatment server', 'webapplications') . "</h4></td>";
//        echo "<td>$serverTypeName</td>";
//        echo "</tr>";
//
//
//        $technicId = $applianceplugin->getField('webapplicationtechnics_id');
//        $technic = new PluginWebapplicationsWebapplicationTechnic();
//        $technic->getFromDB($technicId);
//        $technicName = $technic->getName();
//
//        echo "<tr>";
//        echo "<td><h4>" . __('Language of treatment', 'webapplications') . "</h4></td>";
//        echo "<td>$technicName</td>";
//        echo "</tr>";
//
//
//        echo "<tr>";
//        echo "<td>";
//        echo "<h4>" . __('DICT', 'webapplications') . "</h4>";
//        echo "</td>";
//        echo "<td class='inTable'>";
//
//
//        if ($is_known) {
//            $disp = $applianceplugin->fields['webapplicationavailabilities'];
//            $int = $applianceplugin->fields['webapplicationintegrities'];
//            $conf = $applianceplugin->fields['webapplicationconfidentialities'];
//            $tra = $applianceplugin->fields['webapplicationtraceabilities'];
//
//
//            echo "<table style='text-align : center; width: 60%'>";
//            echo "<td class='dict'>";
//            echo __('Availability', 'webapplications') . "&nbsp";
//            echo "</td>";
//
//            echo "<td name='webapplicationavailabilities' id='5'>";
//            echo $disp;
//            echo "</td>";
//
//            echo "<td></td>";
//
//            echo "<td class='dict'>";
//            echo __('Integrity', 'webapplications') . "&nbsp";
//            echo "</td>";
//            echo "<td name='webapplicationintegrities' id='6'>";
//            echo $int;
//            echo "</td>";
//
//            echo "<td></td>";
//
//            echo "<td class='dict'>";
//            echo __('Confidentiality', 'webapplications') . "&nbsp";
//            echo "</td>";
//            echo "<td name='webapplicationconfidentialities' id='7'>";
//            echo $conf;
//            echo "</td>";
//
//            echo "<td></td>";
//
//            echo "<td class='dict'>";
//            echo __('Traceability', 'webapplications') . "&nbsp";
//            echo "</td>";
//            echo "<td name='webapplicationtraceabilities' id='8'>";
//            echo $tra;
//            echo "</td>";
//
//            echo "</table>";
//        } else {
//            echo NOT_AVAILABLE;
//        }
//        echo "</td>";
//        echo "</tr>";
//
//        $version = $applianceplugin->getField('version');
//
//        echo "<tr>";
//        echo "<td><h4>" . __('Installed version', 'webapplications') . "</h4></td>";
//        echo "<td>$version</td>";
//        echo "</tr>";
//
//
//        $backoffice = $applianceplugin->getField('backoffice');
//
//        echo "<tr>";
//        echo "<td><h4>" . __('Backoffice URL', 'webapplications') . "</h4></td>";
//        echo "<td><a href=$backoffice>$backoffice</a></td>";
//        echo "</tr>";
//
//        $comment = $appliance->getField('comment');
//
//        echo "<tr>";
//        echo "<td>";
//        echo "<h4>" . __('Comment') . "</h4>";
//        echo "</td>";
//        echo "<td>";
//        if (!empty($comment)) {
//            echo "<table style='border:1px solid; width:60%'>";
//            echo "<td>" . $comment . "</td>";
//            echo "</table>";
//        }
//        echo "</td>";
//        echo "</tr>";
//
//
//        echo "</table>";
//        echo "</p>";

        self::showEcosystem($appliance);

        self::showProcess($appliance);

        self::showDatabase($appliance);

        self::showSupportPart($appliance);
    }


    public static function showEcosystem($appliance)
    {
        echo "<div class='card-body'>";
        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>" . __(
                'Ecosystem',
                'webapplications'
            ) . "</h2>";


        $ApplianceId = $appliance->getField('id');

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsEntity']
        );
        $processDBTM = new PluginWebapplicationsEntity();

        echo "<div class='row flex-row'>";
        echo "<div class='form-field row col-12 col-sm-6  mb-2'>";

        echo "<label class='col-form-label col-xxl-5 text-xxl-end'>";
        echo __('Entities list', 'webapplications');
        echo "</label>";

        echo "<div class='col-xxl-7 field-container'>";
        if (!empty($procsApp)) {
            echo "<select name='processes' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($procsApp as $procApp) {
                if ($processDBTM->getFromDB($procApp['items_id'])) {
                    $name = $processDBTM->getName();
                    $link = PluginWebapplicationsEntity::getFormURLWithID($procApp['items_id']);
                    echo "<option value='$link'>$name</option>";
                }
            }
            echo "</select>";
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";

    }

    public static function showProcess($appliance)
    {
        echo "<div class='card-body'>";

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>" . __(
                'Process',
                'webapplications'
            ) . "</h2>";


        $ApplianceId = $appliance->getField('id');

        $procsAppDBTM = new Appliance_Item();
        $procsApp = $procsAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'PluginWebapplicationsProcess']
        );
        $processDBTM = new PluginWebapplicationsProcess();

        echo "<div class='row flex-row'>";
        echo "<div class='form-field row col-12 col-sm-6  mb-2'>";

        echo "<label class='col-form-label col-xxl-5 text-xxl-end'>";
        echo __('Processes list', 'webapplications');
        echo "</label>";

        echo "<div class='col-xxl-7 field-container'>";
        if (!empty($procsApp)) {
            echo "<select name='processes' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($procsApp as $procApp) {
                if ($processDBTM->getFromDB($procApp['items_id'])) {
                    $name = $processDBTM->getName();
                    $link = PluginWebapplicationsProcess::getFormURLWithID($procApp['items_id']);
                    echo "<option value='$link'>$name</option>";
                }
            }
            echo "</select>";
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }

    public static function showDatabase($appliance)
    {
        echo "<div class='card-body'>";
        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>" . _n('Database', 'Databases', 2) . "</h2>";

        $ApplianceId = $appliance->getField('id');

        $databasesAppDBTM = new Appliance_Item();
        $databasesApp = $databasesAppDBTM->find(['appliances_id' => $ApplianceId, 'itemtype' => 'DatabaseInstance']);
        $databaseDBTM = new DatabaseInstance();

        echo "<div class='row flex-row'>";
        echo "<div class='form-field row col-12 col-sm-6  mb-2'>";

        echo "<label class='col-form-label col-xxl-5 text-xxl-end'>";
        echo __('Databases list', 'webapplications');
        echo "</label>";

        echo "<div class='col-xxl-7 field-container'>";
        if (!empty($databasesApp)) {
            echo "<select name='databases' id='list' Size='3' ondblclick='location = this.value;'>";
            foreach ($databasesApp as $dbApp) {
                if ($databaseDBTM->getFromDB($dbApp['items_id'])) {
                    $name = $databaseDBTM->getName();
                    $link = DatabaseInstance::getFormURLWithID($dbApp['items_id']);
                    echo "<option value='$link'>$name</option>";
                }
            }
            echo "</select>";
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }

    public static function showSupportPart($appliance)
    {
        echo "<div class='card-body'>";

        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>" . __(
                'Support',
                'webapplications'
            );


//        echo "<p class='card-text'>";
        $ApplianceId = $appliance->getField('id');

        $linkApp = PluginWebapplicationsAppliance::getFormURLWithID($ApplianceId);
        $linkApp .= "&forcetab=main";

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);
        echo "<span style='float: right'>";
        echo Html::submit(
            _sx('button', 'Edit'),
            [
                'name' => 'edit',
                'class' => 'btn btn-secondary',
                'icon' => 'fas fa-edit',
                'style' => 'float: right',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#editAppSupport' . $ApplianceId
            ]
        );

        echo Ajax::createIframeModalWindow(
            'editAppSupport' . $ApplianceId,
            $linkApp,
            [
                'display' => false,
                'reloadonclose' => true
            ]
        );
        echo "</span>";
        echo "</h2>";


        $applianceplugin = new PluginWebapplicationsAppliance();
        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id' => $ApplianceId]);

        $refEditId = 0;
        $editor = null;
        $editorName = null;
        $editoremail = null;
        $editorephonenumber = null;

        $linkEdit = "";
        if ($is_known) {
            $refEditId = $applianceplugin->fields['editor'];

            $editor = new Supplier();
            $editor->getFromDB($refEditId);
            $editorName = $editor->getName();
            $editoremail = $editor->getField('email');
            $editorephonenumber = $editor->getField('phonenumber');
        }

        $options['itemtype'] = 'Supplier';
        $options['items_id'] = $refEditId;
        $options['editorName'] = $editorName ?? NOT_AVAILABLE;
        $options['editoremail'] =  $editoremail ?? NOT_AVAILABLE;
        $options['editorephonenumber'] = $editorephonenumber ?? NOT_AVAILABLE;

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_support.html.twig', [
            'item'   => $appliance,
            'params' => $options,
        ]);

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
        $this->addStandardTab(
            'PluginWebapplicationsDashboardPhysicalInfrastructure',
            $ong,
            $options
        );//Vue Infra physiques
        $this->addStandardTab('PluginWebapplicationsDashboardDatabase', $ong, $options);//Vue Base de données
        $this->addStandardTab('PluginWebapplicationsDashboardStream', $ong, $options);//Vue Flux
        $this->addStandardTab('KnowbaseItem_Item', $ong, $options);

        return $ong;
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }
}
