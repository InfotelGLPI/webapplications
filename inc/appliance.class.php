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
 * Class PluginWebapplicationsAppliance
 */
class PluginWebapplicationsAppliance extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";
    public static function getTypeName($nb = 0)
    {
        return _n('Web application', 'Web applications', $nb, 'webapplications');
    }

    /**
     * @param $params
     */
    public static function addFields($params)
    {
        $item = $params['item'];
        $webapp_appliance = new self();
        $webapp_database = new PluginWebapplicationsDatabaseInstance();
        if ($item->getType() == 'Appliance') {
            if ($item->getID()) {
                $webapp_appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);
            } else {
                $webapp_appliance->getEmpty();
            }

            $hasPicture = $item->hasItemtypeOrModelPictures();
            $options = [];
            $options['hasPicture'] = $hasPicture;


            TemplateRenderer::getInstance()->display('@webapplications/webapplication_appliance_form.html.twig', [
                'item' => $webapp_appliance,
                'params' => $options,
                'nbusers' => self::getNbUsers(),
            ]);
        } else if ($item->getType() == 'DatabaseInstance') {
            if ($item->getID()) {
                $webapp_database->getFromDBByCrit(['databaseinstances_id' => $item->getID()]);
            } else {
                $webapp_database->getEmpty();
            }

            $options = [];

            if (isset($params["options"]["appliances_id"])) {
                $options = ['appliances_id' => $params["options"]["appliances_id"]];
            }

            TemplateRenderer::getInstance()->display('@webapplications/webapplication_database_form.html.twig', [
                'item' => $webapp_database,
                'params' => $options,
            ]);
        }
        return true;
    }

    public static function getNbUsers()
    {
        return [
            0 => Dropdown::EMPTY_VALUE,
            1 => __('1 to 100', 'webapplications'),
            2 => __('100 to 500', 'webapplications'),
            3 => __('500 to 1000', 'webapplications'),
            4 => __('1000 to 5000', 'webapplications'),
            5 => __('All users', 'webapplications'),
        ];
    }

    public static function getNbUsersValue($value)
    {
        $nb = [
            0 => Dropdown::EMPTY_VALUE,
            1 => __('1 to 100', 'webapplications'),
            2 => __('100 to 500', 'webapplications'),
            3 => __('500 to 1000', 'webapplications'),
            4 => __('1000 to 5000', 'webapplications'),
            5 => __('All users', 'webapplications'),
        ];
        return $value > 0 ? $nb[$value] : Dropdown::EMPTY_VALUE;
    }

    /**
     * @param \Appliance $item
     *
     * @return false
     */
    public static function applianceAdd(Appliance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setAppliance($item);
    }


    /**
     * @param \Appliance $item
     *
     * @return false
     */
    public static function applianceUpdate(Appliance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setAppliance($item);
    }

    /**
     * @param \Appliance $item
     */
    public static function setAppliance(Appliance $item)
    {
        $appliance = new PluginWebApplicationsAppliance();
        if (!empty($item->fields)) {
            $appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);
            $address = isset($item->input['address']) ? $item->input['address'] : $appliance->fields['address'];
            $backoffice = isset($item->input['backoffice']) ? $item->input['backoffice'] : $appliance->fields['backoffice'];
            $number_users = isset($item->input['number_users']) ? $item->input['number_users'] : $appliance->fields['number_users'];
            $version = isset($item->input['version']) ? $item->input['version'] : $appliance->fields['version'];
            $editor = isset($item->input['editor']) ? $item->input['editor'] : $appliance->fields['editor'];
            if (is_array($appliance->fields) && count($appliance->fields) > 0) {
                $appliance->update([
                    'id' => $appliance->fields['id'],
                    'address' => $address,
                    'version' => $version,
                    'editor' => $editor,
                    'backoffice' => $backoffice,
                    'number_users' => $number_users,
                    'webapplicationservertypes_id' => isset($item->input['webapplicationservertypes_id']) ? $item->input['webapplicationservertypes_id'] : $appliance->fields['webapplicationservertypes_id'],
                    'webapplicationtechnics_id' => isset($item->input['webapplicationtechnics_id']) ? $item->input['webapplicationtechnics_id'] : $appliance->fields['webapplicationtechnics_id'],
                    'webapplicationexternalexpositions_id' => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : $appliance->fields['webapplicationexternalexpositions_id'],
                    'webapplicationreferringdepartmentvalidation' => isset($item->input['webapplicationreferringdepartmentvalidation']) ? $item->input['webapplicationreferringdepartmentvalidation'] : $appliance->fields['webapplicationreferringdepartmentvalidation'],
                    'webapplicationciovalidation' => isset($item->input['webapplicationciovalidation']) ? $item->input['webapplicationciovalidation'] : $appliance->fields['webapplicationciovalidation'],
                    'webapplicationavailabilities' => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : $appliance->fields['webapplicationavailabilities'],
                    'webapplicationintegrities' => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : $appliance->fields['webapplicationintegrities'],
                    'webapplicationconfidentialities' => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : $appliance->fields['webapplicationconfidentialities'],
                    'webapplicationtraceabilities' => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : $appliance->fields['webapplicationtraceabilities']
                ]);
            } else {
                $appliance->add([
                    'webapplicationservertypes_id' => isset($item->input['webapplicationservertypes_id']) ? $item->input['webapplicationservertypes_id'] : 0,
                    'webapplicationtechnics_id' => isset($item->input['webapplicationtechnics_id']) ? $item->input['webapplicationtechnics_id'] : 0,
                    'webapplicationexternalexpositions_id' => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : 0,
                    'webapplicationreferringdepartmentvalidation' => isset($item->input['webapplicationreferringdepartmentvalidation']) ? $item->input['webapplicationreferringdepartmentvalidation'] : 0,
                    'webapplicationciovalidation' => isset($item->input['webapplicationciovalidation']) ? $item->input['webapplicationciovalidation'] : 0,
                    'webapplicationavailabilities' => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : 0,
                    'webapplicationintegrities' => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : 0,
                    'webapplicationconfidentialities' => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : 0,
                    'webapplicationtraceabilities' => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : 0,
                    'address' => $address,
                    'version' => $version,
                    'editor' => $editor,
                    'appliances_id' => $item->getID(),
                    'backoffice' => $backoffice,
                    'number_users' => $number_users,
                ]);
            }
        }
    }

    public function post_getEmpty()
    {
        $this->fields["webapplicationconfidentialities"] = 0;
    }

    /**
     * @param $item
     */
    public static function cleanRelationToAppliance($item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['appliances_id' => $item->getID()]);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return __('Appliance');
    }


    public static function showSupportPartFromDashboard($appliance)
    {
        echo "<div class='card-body child33'>";

        $ApplianceId = $appliance->getField('id');

        $supplier = new Supplier();

        $applianceplugin = new PluginWebapplicationsAppliance();
        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id' => $ApplianceId]);

        $supplier_id = $applianceplugin->fields['editor'] ?? 0;
        $title = __('Support', 'webapplications');
        PluginWebapplicationsDashboard::showTitleforDashboard($title, $supplier_id, $supplier, 'edit','editAppSupport');

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
        $options['editoremail'] = $editoremail ?? NOT_AVAILABLE;
        $options['editorephonenumber'] = $editorephonenumber ?? NOT_AVAILABLE;

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_support.html.twig', [
            'item' => $appliance,
            'params' => $options,
        ]);

        echo "</div>";
    }

    public static function showDocumentsAndContractsFromDashboard($appliance)
    {
        global $CFG_GLPI;

        echo "<div class='card-body child33'>";

        $ApplianceId = $appliance->getField('id');

        $documentItemDBTM = new Document_Item();
        $docuItems = $documentItemDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);

        $title = _n('Associated document', 'Associated documents', count($docuItems), 'webapplications');
        PluginWebapplicationsDashboard::showTitleforDashboard($title, $ApplianceId, $documentItemDBTM);

        $docuDBTM = new Document();

        if (count($docuItems) > 0) {
            echo "<div class='list-group' style='margin-top: 10px;'>";
            foreach ($docuItems as $docuItem) {
                $docuDBTM->getFromDB($docuItem['documents_id']);
                $name = $docuDBTM->getName();
                $open = $CFG_GLPI["root_doc"] . "/front/document.send.php";
                $open .= (strpos($open, '?') ? '&' : '?') . 'docid=' . $docuItem['documents_id'];
                echo "<a class='list-group-item list-group-item-action' href='$open'>$name</a>";
            }

            echo "</div>";
        } else {
            echo __("No associated documents", 'webapplications');
        }

        $contractItemDBTM = new Contract_Item();
        $contractItems = $contractItemDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);

        $title = _n('Associated contract', 'Associated contracts', count($contractItems), 'webapplications');
        PluginWebapplicationsDashboard::showTitleforDashboard($title, $ApplianceId, $contractItemDBTM);

        $contractDBTM = new Contract();

        if (count($contractItems) > 0) {
            echo "<div class='list-group' style='margin-top: 10px;'>";
            foreach ($contractItems as $contractItem) {
                $contractDBTM->getFromDB($contractItem['contracts_id']);
                $name = $contractDBTM->getName();
                $open = $CFG_GLPI["root_doc"] . "/front/contract.form.php";
                $open .= (strpos($open, '?') ? '&' : '?') . 'id=' . $contractItem['contracts_id'];
                echo "<a class='list-group-item list-group-item-action' href='$open'>$name</a>";
            }

            echo "</div>";
        } else {
            echo __("No associated contracts", 'webapplications');
        }


        echo "</div>";
    }


    static public function getColorForDICT($field)
    {
        switch ($field) {
            case '1':
                $background = "#00FF00";
                break;
            case '2':
                $background = "#FFFF00";
                break;
            case '3':
                $background = "#FF9900";
                break;
            case '4':
                $background = "#FF0000";
                break;
            default:
                $background = "#999999";
                break;
        }
        return $background;
    }
}
