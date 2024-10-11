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
 * Class PluginWebapplicationsDatabaseInstance
 */
class PluginWebapplicationsDatabaseInstance extends CommonDBTM
{
    public static $rightname = "plugin_webapplications";

    public static function getTypeName($nb = 0)
    {
        return _n("Database", 'Databases', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];
            $self = new DatabaseInstance();
            $nb = count(PluginWebapplicationsDashboard::getObjects($self, $ApplianceId));
            return self::createTabEntry(self::getTypeName(), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new DatabaseInstance();
        PluginWebapplicationsDashboard::showList($obj);
        return true;
    }

    /**
     * @param $params
     */
    public static function addFields($params)
    {
        $item = $params['item'];
        $webapp_database = new self();
        if ($item->getType() == 'DatabaseInstance') {
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

    public function showForm($ID, $options = [])
    {
        $instance = new DatabaseInstance();
        $instance->showForm($ID, $options);

        return true;
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        $items_id = $this->input['databaseinstances_id'];
        if (isset($appliance_id) && !empty($appliance_id)) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add([
                'appliances_id' => $appliance_id,
                'items_id' => $items_id,
                'itemtype' => 'DatabaseInstance'
            ]);
        }
    }

    /**
     * @param \Database $item
     *
     * @return false
     */
    public static function databaseAdd(DatabaseInstance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setDatabase($item);
    }


    /**
     * @param \Database $item
     *
     * @return false
     */
    public static function databaseUpdate(DatabaseInstance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setDatabase($item);
    }

    /**
     * @param \Database $item
     */
    public static function setDatabase(DatabaseInstance $item)
    {
        $database = new PluginWebapplicationsDatabaseInstance();
        if (!empty($item->fields)) {
            $database->getFromDBByCrit(['databaseinstances_id' => $item->getID()]);
            if (is_array($database->fields) && count($database->fields) > 0) {
                $database->update([
                    'id' => $database->fields['id'],
                    'webapplicationexternalexpositions_id' => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : $database->fields['plugin_webapplications_webapplicationexternalexpositions_id'],
                    'webapplicationavailabilities' => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : $database->fields['plugin_webapplications_webapplicationavailabilities'],
                    'webapplicationintegrities' => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : $database->fields['plugin_webapplications_webapplicationintegrities'],
                    'webapplicationconfidentialities' => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : $database->fields['plugin_webapplications_webapplicationconfidentialities'],
                    'webapplicationtraceabilities' => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : $database->fields['plugin_webapplications_webapplicationtraceabilities']
                ]);
            } else {
                $database->add([
                    'webapplicationexternalexpositions_id' => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : 0,
                    'webapplicationavailabilities' => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : 0,
                    'webapplicationintegrities' => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : 0,
                    'webapplicationconfidentialities' => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : 0,
                    'webapplicationtraceabilities' => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : 0,
                    'appliances_id' => isset($item->input['appliances_id']) ? $item->input['appliances_id'] : 0,
                    'databaseinstances_id' => $item->getID()
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
    public static function cleanRelationToDatabase($item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['databaseinstances_id' => $item->getID()]);
    }
}
