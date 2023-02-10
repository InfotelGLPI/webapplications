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
 * Class PluginWebapplicationsProcess_Entity
 */
class PluginWebapplicationsProcess_Entity extends CommonDBTM
{
    use Glpi\Features\Inventoriable;
    public static $rightname         = "plugin_webapplications_processes";

    public static function getTypeName($nb = 0)
    {
        return _n('Process Entity', 'Processes Entity', $nb, 'webapplications');
    }


    public static function getIcon()
    {
        return PluginWebapplicationsProcess::getIcon();
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item::getType()) {
            case 'PluginWebapplicationsEntity':
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $dbu = new DbUtils();
                    return self::createTabEntry(PluginWebapplicationsProcess::getTypeName(), $dbu->countElementsInTable($this->getTable(), ["plugin_webapplications_entities_id" => $item->getID()]));
                }
                return _n('Process', 'Processes', 2, 'webapplications');
                break;
            case 'PluginWebapplicationsProcess':
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $dbu = new DbUtils();
                    return self::createTabEntry(PluginWebapplicationsEntity::getTypeName(), $dbu->countElementsInTable($this->getTable(), ["plugin_webapplications_processes_id" => $item->getID()]));
                }
                return _n('Entity', 'Entities', 2);
                break;
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $field = new self();

        if ($item->getType() == 'PluginWebapplicationsEntity') {
            $field->showForEntity($item);
        }
        if ($item->getType() == 'PluginWebapplicationsProcess') {
            $field->showForProcess($item);
        }
        return true;
    }

    public function showForEntity($item)
    {
        if (!$this->canView()) {
            return false;
        }
        if (!$this->canCreate()) {
            return false;
        }

        $entity    = new PluginWebapplicationsEntity();
        $canedit = $entity->can($item->fields['id'], UPDATE);
        if ($canedit) {
            echo "<form name='form' method='post' action='" .
                Toolbox::getItemTypeFormURL('PluginWebapplicationsProcess_Entity') . "'>";

            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='6'>" . __('Add a process', 'webapplications') . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            // Dropdown group
            echo "<td class='center'>";
            PluginWebapplicationsProcess::dropdown();
            echo "</td>";
            echo "<td class='tab_bg_2 center' colspan='6'>";
            echo Html::hidden('plugin_webapplications_entities_id', ['value' => $item->getID()]);
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

        echo "<th colspan='3'>" . _n('Process', 'Processes', 2, 'webapplications') . "</th>";

        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";

        $processes = $this->find(['plugin_webapplications_entities_id' => $item->getID()]);
        $processDBTM = new PluginWebapplicationsProcess();

        foreach ($processes as $process) {
            $processDBTM->getFromDB($process['plugin_webapplications_processes_id']);
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='3'>";
            echo Html::link($processDBTM->getName(), PluginWebapplicationsProcess::getFormURLWithID($process['plugin_webapplications_processes_id']));
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table></div>";

        Html::closeForm();
    }

    public function showForProcess($item)
    {
        if (!$this->canView()) {
            return false;
        }
        if (!$this->canCreate()) {
            return false;
        }

        $process    = new PluginWebapplicationsProcess();
        $canedit = $process->can($item->fields['id'], UPDATE);
        if ($canedit) {
            echo "<form name='form' method='post' action='" .
                Toolbox::getItemTypeFormURL('PluginWebapplicationsProcess_Entity') . "'>";

            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='6'>" . __('Add an entity', 'webapplications') . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            // Dropdown group
            echo "<td class='center'>";
            PluginWebapplicationsEntity::dropdown();
            echo "</td>";
            echo "<td class='tab_bg_2 center' colspan='6'>";
            echo Html::hidden('plugin_webapplications_processes_id', ['value' => $item->getID()]);
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

        echo "<th colspan='3'>" . _n('Entity', 'Entities', 2) . "</th>";

        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";

        $entities = $this->find(['plugin_webapplications_processes_id' => $item->getID()]);
        $entityDBTM = new PluginWebapplicationsEntity();

        foreach ($entities as $entity) {
            $entityDBTM->getFromDB($entity['plugin_webapplications_entities_id']);
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='3'>";
            echo Html::link($entityDBTM->getName(), PluginWebapplicationsEntity::getFormURLWithID($entity['plugin_webapplications_entities_id']));
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table></div>";

        Html::closeForm();
    }
}
