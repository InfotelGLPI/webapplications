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

use Ajax;
use Appliance_Item;
use CommonDBTM;
use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Features\Inventoriable;
use Html;
use User;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Entity
 */
class Entity extends CommonDBTM
{

    public static $rightname = "plugin_webapplications_entities";

    public static function getTypeName($nb = 0)
    {
        return __('Ecosystem', 'webapplications');
    }

    public static function getMenuContent()
    {
        $menu = [];

        $menu['title'] = self::getMenuName();
        $menu['page'] = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);
        if (self::canCreate()) {
            $menu['links']['add'] = self::getFormURL(false);
        }

        $menu['icon'] = self::getIcon();

        return $menu;
    }

    public static function getIcon()
    {
        return "ti ti-users";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;;
            $self = new self();
            $nb = count(Dashboard::getObjects($self, $ApplianceId));
            return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new self();
        Dashboard::showList($obj);
        return true;
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        $options['appliances_id'] = $_SESSION['plugin_webapplications_loaded_appliances_id'];
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_entity_form.html.twig', [
            'item' => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function prepareInputForAdd($input)
    {
        $allowed = ['id', 'entities_id', 'is_recursive', 'name', 'appliances_id',
                    'owner', 'security_contact', 'relation_nature'];
        $input = array_intersect_key($input, array_flip($allowed));
        if (isset($input['appliances_id']) && !empty($input['appliances_id'])) {
            $item = new \Appliance();
            if ($item->getFromDB($input['appliances_id'])) {
                $input['entities_id'] = $item->fields['entities_id'];
                $input['is_recursive'] = $item->fields['is_recursive'];
            }
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $allowed = ['id', 'entities_id', 'is_recursive', 'name',
                    'owner', 'security_contact', 'relation_nature'];
        $input = array_intersect_key($input, array_flip($allowed));
        return parent::prepareInputForUpdate($input);
    }

    public function post_addItem()
    {
        $appliance_id = $this->input['appliances_id'];
        if (isset($appliance_id) && !empty($appliance_id)) {
            $itemDBTM = new Appliance_Item();
            $itemDBTM->add(
                [
                    'appliances_id' => $appliance_id,
                    'items_id' => $this->getID(),
                    'itemtype' => Entity::class
                ]
            );
        }
    }

    /**
     * @return array
     */
    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => self::getTypeName(2)
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink',
            'itemlink_type' => $this->getType(),
        ];

        $tab[] = [
            'id' => '2',
            'table' => User::getTable(),
            'field' => 'name',
            'linkfield' => 'owner',
            'name' => __('Owner', 'webapplications'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '3',
            'table' => User::getTable(),
            'field' => 'name',
            'linkfield' => 'security_contact',
            'name' => __('Security Contact', 'webapplications'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'relation_nature',
            'name' => __('Relation nature', 'webapplications'),
            'datatype' => 'text'
        ];

        return $tab;
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab(Process_Entity::class, $ong, $options);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        return $ong;
    }

    public static function showListObjects($list)
    {
        $object = new self();
        $cards = [];

        foreach ($list as $field) {
            $id = $field['id'];
            $object->getFromDB($id);

            $blocks = [];
            if ($object->fields['owner'] > 0) {
                $blocks[] = [
                    'kind'  => 'info',
                    'label' => __('Owner', 'webapplications'),
                    'value' => getUserName($object->fields['owner']),
                ];
            }
            if ($object->fields['security_contact'] > 0) {
                $blocks[] = [
                    'kind'  => 'info',
                    'label' => __('Security Contact', 'webapplications'),
                    'value' => getUserName($object->fields['security_contact']),
                ];
            }
            if (!empty($object->fields['relation_nature'])) {
                $blocks[] = [
                    'kind'  => 'info',
                    'label' => __('Relation nature', 'webapplications'),
                    'value' => $object->fields['relation_nature'],
                ];
            }

            $cards[] = [
                'width_class' => 'w-33',
                'icon'        => self::getIcon(),
                'icon_size'   => '5em',
                'title_html'  => $object->getLink(),
                'blocks'      => $blocks,
                'edit_html'   => Dashboard::getCardEditHtml($object, (int) $id),
            ];
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_object_cards.html.twig', [
            'cards' => $cards,
        ]);
    }
}
