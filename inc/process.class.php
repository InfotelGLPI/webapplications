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
 * Class PluginWebapplicationsProcess
 */
class PluginWebapplicationsProcess extends CommonDBTM
{
    use Glpi\Features\Inventoriable;
    public static $rightname         = "plugin_webapplications_processes";

    public static function getTypeName($nb = 0)
    {
        return _n('Process', 'Processes', $nb, 'webapplications');
    }

    public static function getMenuContent()
    {
        $menu = [];

        $menu['title']           = self::getMenuName();
        $menu['page']            = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);
        if (self::canCreate()) {
            $menu['links']['add'] = self::getFormURL(false);
        }

        $menu['icon'] = self::getIcon();

        return $menu;
    }


    public static function getIcon()
    {
        return "fas fa-cogs";
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);


        TemplateRenderer::getInstance()->display('@webapplications/webapplication_process_form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function post_getEmpty()
    {
        $this->fields["webapplicationconfidentialities"] = 0;
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        if (!is_null($appliance_id)&&$appliance_id!=0) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add(['appliances_id' => $appliance_id, 'items_id' => $this->getID(), 'itemtype' => 'PluginWebapplicationsProcess']);
        }
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        $this->addStandardTab('PluginWebapplicationsProcess_Entity', $ong, $options);
        return $ong;
    }
}
