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
use CommonGLPI;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Features\Inventoriable;
use GlpiPlugin\Webapplications\Entity;
use Html;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Process_Entity
 */
class Process_Entity extends CommonDBTM
{


    public static $rightname = "plugin_webapplications_processes";

    public static function getTypeName($nb = 0)
    {
        return _n('Process Entity', 'Processes Entity', $nb, 'webapplications');
    }


    public static function getIcon()
    {
        return Process::getIcon();
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item::getType()) {
            case Entity::class:
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $dbu = new DbUtils();
                    $nb = $dbu->countElementsInTable(
                        $this->getTable(),
                        ["plugin_webapplications_entities_id" => $item->getID()]
                    );
                    return self::createTabEntry(
                        Process::getTypeName($nb),
                        $nb
                    );
                }
                return _n('Process', 'Processes', 2, 'webapplications');
                break;
            case Process::class:
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $dbu = new DbUtils();
                    $nb = $dbu->countElementsInTable(
                        $this->getTable(),
                        ["plugin_webapplications_processes_id" => $item->getID()]
                    );
                    return self::createTabEntry(
                        Entity::getTypeName($nb),
                        $nb
                    );
                }
                return _n('Entity', 'Entities', 2);
                break;
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $field = new self();

        if ($item->getType() == Entity::class) {
            $field->showForEntity($item);
        }
        if ($item->getType() == Process::class) {
            $field->showForProcess($item);
        }
        return true;
    }

    public function prepareInputForAdd($input)
    {
        $allowed = ['id', 'plugin_webapplications_entities_id', 'plugin_webapplications_processes_id'];
        return array_intersect_key($input, array_flip($allowed));
    }

    public function prepareInputForUpdate($input)
    {
        $allowed = ['id', 'plugin_webapplications_entities_id', 'plugin_webapplications_processes_id'];
        $input = array_intersect_key($input, array_flip($allowed));
        return parent::prepareInputForUpdate($input);
    }

    public function showForEntity($item)
    {
        if (!$this->canView()) {
            return false;
        }

        $entity = new Entity();
        $canedit = $entity->can($item->fields['id'], UPDATE);

        $dropdown_html = Process::dropdown(['display' => false]);

        $rows = [];
        $processes = $this->find(['plugin_webapplications_entities_id' => $item->getID()]);
        $processDBTM = new Process();
        foreach ($processes as $process) {
            $processDBTM->getFromDB($process['plugin_webapplications_processes_id']);
            $rows[] = [
                'name' => $processDBTM->getName(),
                'url'  => Process::getFormURLWithID($process['plugin_webapplications_processes_id']),
            ];
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_process_entity.html.twig', [
            'canedit'       => $canedit,
            'form_url'      => Toolbox::getItemTypeFormURL(Process_Entity::class),
            'add_title'     => __('Add a process', 'webapplications'),
            'dropdown_html' => $dropdown_html,
            'hidden_name'   => 'plugin_webapplications_entities_id',
            'hidden_value'  => (int) $item->getID(),
            'list_title'    => _n('Process', 'Processes', 2, 'webapplications'),
            'rows'          => $rows,
        ]);
    }

    public function showForProcess($item)
    {
        if (!$this->canView()) {
            return false;
        }

        $process = new Process();
        $canedit = $process->can($item->fields['id'], UPDATE);

        $dropdown_html = Entity::dropdown(['display' => false]);

        $rows = [];
        $entities = $this->find(['plugin_webapplications_processes_id' => $item->getID()]);
        $entityDBTM = new Entity();
        foreach ($entities as $entity) {
            $entityDBTM->getFromDB($entity['plugin_webapplications_entities_id']);
            $rows[] = [
                'name' => $entityDBTM->getName(),
                'url'  => Entity::getFormURLWithID($entity['plugin_webapplications_entities_id']),
            ];
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_process_entity.html.twig', [
            'canedit'       => $canedit,
            'form_url'      => Toolbox::getItemTypeFormURL(Process_Entity::class),
            'add_title'     => __('Add an entity', 'webapplications'),
            'dropdown_html' => $dropdown_html,
            'hidden_name'   => 'plugin_webapplications_processes_id',
            'hidden_value'  => (int) $item->getID(),
            'list_title'    => _n('Entity', 'Entities', 2),
            'rows'          => $rows,
        ]);
    }
}
