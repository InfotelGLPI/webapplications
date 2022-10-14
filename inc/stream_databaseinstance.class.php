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
 * Class PluginWebapplicationsStream_DatabaseInstance
 */
class PluginWebapplicationsStream_DatabaseInstance extends CommonDBTM {

    use Glpi\Features\Inventoriable;
    static $rightname         = "plugin_webapplications_streams";

    static function getTypeName($nb = 0) {
        return _n('Stream Database', 'Streams Database', $nb, 'webapplications');
    }


    static function getIcon() {
        return PluginWebapplicationsStream::getIcon();
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        switch ($item::getType()) {
            case 'PluginWebapplicationsStream':
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $dbu = new DbUtils();
                    return self::createTabEntry(DatabaseInstance::getTypeName(), $dbu->countElementsInTable($this->getTable(), ["plugin_webapplications_streams_id" => $item->getID()]));
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

        if (!$this->canView()) {
            return false;
        }
        if (!$this->canCreate()) {
            return false;
        }

        $stream    = new PluginWebapplicationsStream();
        $canedit = $stream->can($item->fields['id'], UPDATE);
        if ($canedit) {
            echo "<form name='form' method='post' action='" .
                Toolbox::getItemTypeFormURL('PluginWebapplicationsStream_DatabaseInstance') . "'>";

            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='6'>" . __('Add a database', 'webapplications') . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            // Dropdown group
            echo "<td class='center'>";
            DatabaseInstance::dropdown();
            echo "</td>";
            echo "<td class='tab_bg_2 center' colspan='6'>";
            echo Html::hidden('plugin_webapplications_streams_id', ['value' => $item->getID()]);
            echo Html::submit(_sx('button', 'Add'), ['name' => 'add', 'class' => 'btn btn-primary']);
            echo "</td>";
            echo "</tr>";
            echo "</table></div>";
            Html::closeForm();
        }

        echo "<div class='spaced' id='tabsbody'>";
        echo "<table class='tab_cadre_fixe'>";


        echo "<thead>";
        echo "<tr class='tab_bg_2'>";

        echo "<th colspan='3'>" . _n('Database', 'Databases', 2) . "</th>";

        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";

        $databases = $this->find(['plugin_webapplications_streams_id' => $item->getID()]);
        $databaseDBTM = new DatabaseInstance();

        foreach ($databases as $database) {
            $databaseDBTM->getFromDB($database['databaseinstances_id']);
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='3'>";
            echo Html::link($databaseDBTM->getName(), DatabaseInstance::getFormURLWithID($database['databaseinstances_id']));
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table></div>";

        Html::closeForm();

    }



}
