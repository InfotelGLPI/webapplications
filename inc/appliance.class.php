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

use Glpi\Application\View\TemplateRenderer;

/**
 * Class PluginWebapplicationsAppliance
 */
class PluginWebapplicationsAppliance extends CommonDBTM {

   static function getTypeName($nb = 0) {

      return _n('Web application', 'Web applications', $nb, 'webapplications');
   }

   /**
    * @param $params
    */
   static function addFields($params) {

      $item             = $params['item'];
      $webapp_appliance = new self();
      if ($item->getType() == 'Appliance') {

         if ($item->getID()) {
            $webapp_appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);
         } else {
            $webapp_appliance->getEmpty();
         }
         $options = [];

         TemplateRenderer::getInstance()->display('@webapplications/webapplication_form.html.twig', [
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
   static function applianceAdd(Appliance $item) {
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
   static function applianceUpdate(Appliance $item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }
      self::setAppliance($item);
   }

   /**
    * @param \Appliance $item
    */
   static function setAppliance(Appliance $item) {
      $appliance = new self();
      if (!empty($item->fields)) {
         $appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);
         $address    = isset($item->input['address']) ? $item->input['address'] : $appliance->fields['address'];
         $backoffice = isset($item->input['backoffice']) ? $item->input['backoffice'] : $appliance->fields['backoffice'];
         if (is_array($appliance->fields) && count($appliance->fields) > 0) {
            $appliance->update(['id'                           => $appliance->fields['id'],
                                'address'                      => $address,
                                'backoffice'                   => $backoffice,
                                'webapplicationservertypes_id' => isset($item->input['webapplicationservertypes_id']) ? $item->input['webapplicationservertypes_id'] : $appliance->fields['plugin_webapplications_webapplicationservertypes_id'],
                                'webapplicationtechnics_id'    => isset($item->input['webapplicationtechnics_id']) ? $item->input['webapplicationtechnics_id'] : $appliance->fields['plugin_webapplications_webapplicationtechnics_id']
                               ]);
         } else {
            $appliance->add(['webapplicationservertypes_id' => isset($item->input['webapplicationservertypes_id']) ? $item->input['webapplicationservertypes_id'] : 0,
                             'webapplicationtechnics_id'    => isset($item->input['webapplicationtechnics_id']) ? $item->input['webapplicationtechnics_id'] : 0,
                             'address'                      => $address,
                             'appliances_id'                => $item->getID(),
                             'backoffice'                   => $backoffice]);
         }
      }
   }


   /**
    * @param $item
    */
   static function cleanRelationToAppliance($item) {

      $temp = new self();
      $temp->deleteByCriteria(['appliances_id' => $item->getID()]);

   }
}
