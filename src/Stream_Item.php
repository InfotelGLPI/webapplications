<?php

/**
 * -------------------------------------------------------------------------
 * webapplications plugin for GLPI
 * Copyright (C) 2015-2026 by the webapplications Development Team.
 *
 * https://github.com/InfotelGLPI/webapplications
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of webapplications.
 *
 * webapplications is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * webapplications is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Webapplications;

use CommonDBTM;
use CommonGLPI;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Features\Inventoriable;
use Html;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Stream_Item
 */
class Stream_Item extends CommonDBTM
{


    public static $rightname = "plugin_webapplications_streams";

    public static function getTypeName($nb = 0)
    {
        return _n('Item', 'Items', $nb);
    }


    public static function getIcon()
    {
        return Stream::getIcon();
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item::getType()) {
            case Stream::class:
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $dbu = new DbUtils();
                    $nbItems = $dbu->countElementsInTable(
                        $this->getTable(),
                        ["plugin_webapplications_streams_id" => $item->getID()]
                    );
                    return self::createTabEntry(self::getTypeName($nbItems), $nbItems);
                }
                return _n("Database", 'Databases', 2);
                break;
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $field = new self();

        if ($item->getType() == Stream::class) {
            $field->showForStream($item);
        }
        return true;
    }

    public function prepareInputForAdd($input)
    {
        $allowed = ['id', 'plugin_webapplications_streams_id', 'items_id', 'itemtype'];
        return array_intersect_key($input, array_flip($allowed));
    }

    public function prepareInputForUpdate($input)
    {
        $allowed = ['id', 'plugin_webapplications_streams_id', 'items_id', 'itemtype'];
        $input = array_intersect_key($input, array_flip($allowed));
        return parent::prepareInputForUpdate($input);
    }

    public function showForStream($item)
    {
        global $DB;
        $ID = $item->fields['id'];
        $rand = mt_rand();

        if (!$this->canView()) {
            return false;
        }
        if (!$this->canCreate()) {
            return false;
        }

        $stream = new Stream();
        $canedit = $stream->can($item->fields['id'], UPDATE);

        $items = iterator_to_array($DB->request([
            'FROM' => self::getTable(),
            'WHERE' => [
                'plugin_webapplications_streams_id' => $ID,
            ],
        ]));

        $entries = [];
        foreach ($items as $row) {
            if (!class_exists($row['itemtype'])) {
                continue;
            }
            $it = new $row['itemtype']();
            $it->getFromDB($row['items_id']);
            $entries[] = [
                'itemtype' => self::class,
                'id'       => $row['id'],
                'type'     => $it->getTypeName(1),
                'name'     => $it->getLink(),
            ];
        }

        // Capture the GLPI itemtypes selector to inject it in the Twig template.
        $items_dropdown = Dropdown::showSelectItemFromItemtypes([
            'items_id_name' => 'items_id',
            'itemtypes'     => 'Assets',
            'checkright'    => true,
            'display'       => false,
        ]);

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_stream_item.html.twig', [
            'canedit'        => $canedit,
            'form_url'       => Toolbox::getItemTypeFormURL(Stream_Item::class),
            'items_dropdown' => $items_dropdown,
            'stream_id'      => (int) $item->getID(),
            'datatable_params' => [
                'is_tab'          => true,
                'nofilter'        => true,
                'nosort'          => true,
                'columns'         => [
                    'type' => __('Itemtype'),
                    'name' => _n('Item', 'Items', 1),
                ],
                'formatters'      => [
                    'name' => 'raw_html',
                ],
                'entries'         => $entries,
                'total_number'    => count($entries),
                'filtered_number' => count($entries),
                'showmassiveactions' => $canedit,
                'massiveactionparams' => [
                    'container' => 'mass' . str_replace('\\', '', self::class) . $rand,
                    'itemtype'  => self::class,
                ],
            ],
        ]);
    }
}
