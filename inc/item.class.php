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
 * Class PluginWebapplicationsItem
 */
class PluginWebapplicationsItem extends CommonDBTM
{
    public static function getTypeName($nb = 0)
    {
        return _n('Item', 'Items', $nb);
    }
    

    public function addApplianceItem(Item $item)
    {
        $items_id = $item->getID();
        $appliance_id = $item->input['appliances_id'];
        if (!is_null($appliance_id)&&$appliance_id!=0) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add(['appliances_id' => $appliance_id, 'items_id' => $items_id, 'itemtype' => 'Item']);
        }
    }
}
