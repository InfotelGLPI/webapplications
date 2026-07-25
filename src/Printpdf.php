<?php

/*
 -------------------------------------------------------------------------
 webapplications plugin for GLPI
 Copyright (C) 2015-2026 by the webapplications Development Team.

 https://github.com/InfotelGLPI/webapplications
 -------------------------------------------------------------------------

 LICENSE

 This file is part of webapplications.

 webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Webapplications;
use CommonDBTM;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Session;
use CommonGLPI;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}



/**
 * Class Printpdf
 */

class Printpdf extends CommonDBTM
{
    public static $itemtype = 'PluginWebapplicationsPrintpdf';

    public $dohistory = true;

    public static $rightname = 'plugin_webapplications_configs';



    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @param integer $nb Number of items
     *
     * @return string
     **/
    public static function getTypeName($nb = 0)
    {
        return __('Print PDF', 'webapplications');
    }

    public static function getIcon()
    {
        return "ti ti-printer";
    }

    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ);
    }

    /**
     * @return bool
     */
    public static function canCreate(): bool
    {
        return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
    }


    /**
     * @param $field
     * @param $name (default '')
     * @param $values (default '')
     * @param $options   array
     *
     * @return string
     **@since version 0.84
     *
     */
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    /**
     * @param \CommonGLPI $item
     * @param int $withtemplate
     *
     * @return array|string
     * @see CommonGLPI::getTabNameForItem()
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb = self::getNumberOfOptionsForItem($item);
        return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
    }

    /**
     * Return the number of translations for an item
     *
     * @param item
     *
     * @return int number of translations for this item
     */
    public static function getNumberOfOptionsForItem($item)
    {
        $dbu = new DbUtils();
//        return $dbu->countElementsInTable(
//            $dbu->getTableForItemType(__CLASS__),
//            ["plugin_metademands_metademands_id" => $item->getID()]
//        );
    }


    /**
     * @param $item            CommonGLPI object
     * @param $tabnum (default 1)
     * @param $withtemplate (default 0)
     **
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        $fieldDate = new Printpdf();
        $fieldDate->showPrintpdfForm($item);

        return true;
    }

    public function prepareInputForAdd($input)
    {
        return $input;
    }

    public function showPrintpdfForm(CommonGLPI $item)
    {
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_printpdf_form.html.twig', [
            'item_form_url' => PLUGIN_WEBAPPLICATIONS_WEBDIR . "/front/printpdf.form.php",
            'appliance_id'  => (int) ($_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0),
        ]);
    }
}