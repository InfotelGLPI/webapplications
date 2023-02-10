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
 * Class PluginWebapplicationsStream_Item
 */
class PluginWebapplicationsStream_Item extends CommonDBTM {

    use Glpi\Features\Inventoriable;
    static $rightname         = "plugin_webapplications_streams";

    static function getTypeName($nb = 0) {
        return _n('Item', 'Items', $nb);
    }


    static function getIcon() {
        return PluginWebapplicationsStream::getIcon();
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        switch ($item::getType()) {
            case 'PluginWebapplicationsStream':
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $dbu = new DbUtils();
                    $nbItems = $dbu->countElementsInTable($this->getTable(), ["plugin_webapplications_streams_id" => $item->getID()]);
                    return self::createTabEntry(self::getTypeName($nbItems), $nbItems);
                }
                return __('Databases', 'webapplications');
                break;
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        $field = new self();

        if ($item->getType() == 'PluginWebapplicationsStream') {
            $field->showForStream($item);
        }
        return true;
    }

    function showForStream($item) {

        global $DB;
        $ID = $item->fields['id'];
        $rand = mt_rand();

        if (!$this->canView()) {
            return false;
        }
        if (!$this->canCreate()) {
            return false;
        }

        $items = $DB->request([
            'FROM'   => self::getTable(),
            'WHERE'  => [
                'plugin_webapplications_streams_id' => $ID
            ]
        ]);

        $stream    = new PluginWebapplicationsStream();
        $canedit = $stream->can($item->fields['id'], UPDATE);
        if ($canedit) {
            echo "<form name='form' method='post' action='" .
                Toolbox::getItemTypeFormURL('PluginWebapplicationsStream_Item') . "'>";

            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='6'>" . __('Add an item') . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='center'>";
            Dropdown::showSelectItemFromItemtypes(
                ['items_id_name'   => 'items_id',
                    'itemtypes'       => 'Assets',
                    'checkright'      => true,
                ]
            );
            echo "</td>";
            echo "<td class='tab_bg_2 center' colspan='6'>";
            echo Html::hidden('plugin_webapplications_streams_id', ['value' => $item->getID()]);
            echo Html::submit(_sx('button', 'Add'), ['name' => 'add', 'class' => 'btn btn-primary']);
            echo "</td>";
            echo "</tr>";
            echo "</table></div>";
            Html::closeForm();
        }


        $items = iterator_to_array($items);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed'   => min($_SESSION['glpilist_limit'], count($items)),
                    'container'       => 'mass' . __CLASS__ . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __('Itemtype') . "</th>";
            $header .= "<th>" . _n('Item', 'Items', 1) . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $it = new $row['itemtype']();
                $it->getFromDB($row['items_id']);
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $it->getTypeName(1) . "</td>";
                echo "<td>" . $it->getLink() . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

            if ($canedit && count($items)) {
                 $massiveactionparams['ontop'] = false;
                 Html::showMassiveActions($massiveactionparams);
             }
            if ($canedit) {
                Html::closeForm();
            }
        }


    }



}
