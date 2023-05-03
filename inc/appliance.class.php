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
    public static function getTypeName($nb = 0)
    {
        return _n('Web application', 'Web applications', $nb, 'webapplications');
    }

    /**
     * @param $params
     */
    public static function addFields($params)
    {
        $item             = $params['item'];
        $webapp_appliance = new self();
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
               'item'   => $webapp_appliance,
               'params' => $options,
            ]);
        }
        return true;
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
            $address    = isset($item->input['address']) ? $item->input['address'] : $appliance->fields['address'];
            $backoffice = isset($item->input['backoffice']) ? $item->input['backoffice'] : $appliance->fields['backoffice'];
            $version = isset($item->input['version']) ? $item->input['version'] : $appliance->fields['version'];
            $editor = isset($item->input['editor']) ? $item->input['editor'] : $appliance->fields['editor'];
            if (is_array($appliance->fields) && count($appliance->fields) > 0) {
                $appliance->update(['id'                           => $appliance->fields['id'],
                                    'address'                      => $address,
                                    'version'                      => $version,
                                    'editor'                       => $editor,
                                    'backoffice'                   => $backoffice,
                                    'webapplicationservertypes_id' => isset($item->input['webapplicationservertypes_id']) ? $item->input['webapplicationservertypes_id'] : $appliance->fields['plugin_webapplications_webapplicationservertypes_id'],
                                    'webapplicationtechnics_id'    => isset($item->input['webapplicationtechnics_id']) ? $item->input['webapplicationtechnics_id'] : $appliance->fields['plugin_webapplications_webapplicationtechnics_id'],
                                    'webapplicationexternalexpositions_id'    => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : $appliance->fields['webapplicationexternalexpositions_id'],
                                    'webapplicationreferringdepartmentvalidation'    => isset($item->input['webapplicationreferringdepartmentvalidation']) ? $item->input['webapplicationreferringdepartmentvalidation'] : $appliance->fields['webapplicationreferringdepartmentvalidation'],
                                    'webapplicationciovalidation'    => isset($item->input['webapplicationciovalidation']) ? $item->input['webapplicationciovalidation'] : $appliance->fields['webapplicationciovalidation'],
                                    'webapplicationavailabilities'    => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : $appliance->fields['webapplicationavailabilities'],
                                    'webapplicationintegrities'    => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : $appliance->fields['webapplicationintegrities'],
                                    'webapplicationconfidentialities'    => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : $appliance->fields['webapplicationconfidentialities'],
                                    'webapplicationtraceabilities'    => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : $appliance->fields['webapplicationtraceabilities']
                                   ]);
            } else {
                $appliance->add(['webapplicationservertypes_id' => isset($item->input['webapplicationservertypes_id']) ? $item->input['webapplicationservertypes_id'] : 0,
                                 'webapplicationtechnics_id'    => isset($item->input['webapplicationtechnics_id']) ? $item->input['webapplicationtechnics_id'] : 0,
                                 'webapplicationexternalexpositions_id' => isset($item->input['webapplicationexternalexpositions_id']) ? $item->input['webapplicationexternalexpositions_id'] : 0,
                                 'webapplicationreferringdepartmentvalidation' => isset($item->input['webapplicationreferringdepartmentvalidation']) ? $item->input['webapplicationreferringdepartmentvalidation'] : 0,
                                 'webapplicationciovalidation' => isset($item->input['webapplicationciovalidation']) ? $item->input['webapplicationciovalidation'] : 0,
                                 'webapplicationavailabilities' => isset($item->input['webapplicationavailabilities']) ? $item->input['webapplicationavailabilities'] : 0,
                                 'webapplicationintegrities' => isset($item->input['webapplicationintegrities']) ? $item->input['webapplicationintegrities'] : 0,
                                 'webapplicationconfidentialities' => isset($item->input['webapplicationconfidentialities']) ? $item->input['webapplicationconfidentialities'] : 0,
                                 'webapplicationtraceabilities' => isset($item->input['webapplicationtraceabilities']) ? $item->input['webapplicationtraceabilities'] : 0,
                                 'address'                      => $address,
                                 'version'                      => $version,
                                 'editor'                       => $editor,
                                 'appliances_id'                => $item->getID(),
                                 'backoffice'                   => $backoffice]);
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
}
