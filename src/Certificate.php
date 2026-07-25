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

use Ajax;
use CommonDBTM;
use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Html;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Certificate
 */
class Certificate extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";

    public static function getTypeName($nb = 0)
    {
        return _n("Certificate", 'Certificates', $nb);
    }

    public static function getIcon()
    {
        return "ti ti-certificate";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;;
            $self = new \Certificate();
            $nb = count(Dashboard::getObjects($self, $ApplianceId));
            return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new \Certificate();
        Dashboard::showList($obj);
        return true;
    }


    public function showForm($ID, $options = [])
    {
        $instance = new \Certificate();
        $instance->showForm($ID, $options);

        return true;
    }

    public static function showListObjects($list)
    {
        $object = new \Certificate();
        $cards = [];

        foreach ($list as $field) {
            $id = $field['id'];
            $object->getFromDB($id);

            $cards[] = [
                'width_class' => 'w-33',
                'icon'        => 'ti ti-certificate',
                'icon_size'   => '3em',
                'title_html'  => $object->getLink(),
                'blocks'      => [
                    [
                        'kind'  => 'text',
                        'value' => Html::convDateTime($object->fields['date_expiration']),
                    ],
                ],
                'edit_html'   => Dashboard::getCardEditHtml($object, (int) $id),
            ];
        }

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_object_cards.html.twig', [
            'cards' => $cards,
        ]);
    }
}
