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
 * Class PluginWebapplicationsCertificate
 */
class PluginWebapplicationsCertificate extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";

    public static function getTypeName($nb = 0)
    {
        return _n("Certificate", 'Certificates', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0;;
            $self = new Certificate();
            $nb = count(PluginWebapplicationsDashboard::getObjects($self, $ApplianceId));
            return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $obj = new Certificate();
        PluginWebapplicationsDashboard::showList($obj);
        return true;
    }


    public function showForm($ID, $options = [])
    {
        $instance = new Certificate();
        $instance->showForm($ID, $options);

        return true;
    }

    public static function showListObjects($list)
    {
        $object = new Certificate();

        echo "<div style='display: flex;flex-wrap: wrap;'>";

        foreach ($list as $field) {
            $name = $field['name'];
            $id = $field['id'];
            $object->getFromDB($id);

            echo "<div class='card w-33' style='margin-right: 10px;margin-top: 10px;'>";
            echo "<div class='card-body'>";
            echo "<div style='display: inline-block;margin: 40px;'>";
            echo "<i class='fa-5x fas fa-certificate'></i>";
            echo "</div>";
            echo "<div style='display: inline-block;';>";
            echo "<h5 class='card-title' style='font-size: 14px;'>" . $object->getLink() . "</h5>";


            echo "<p class='card-text'>";
            echo Html::convDateTime($object->fields['date_expiration']);
            echo "</p>";

            $link = $object::getFormURLWithID($id);
            $link .= "&forcetab=main";
            $rand = mt_rand();
            echo "<span style='float: right'>";
            if ($object->canUpdate()) {
                echo Html::submit(
                    _sx('button', 'Edit'),
                    [
                        'name' => 'edit',
                        'class' => 'btn btn-secondary right',
                        'icon' => 'fas fa-edit',
                        'form' => '',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#edit' . $id . $rand
                    ]
                );

                echo Ajax::createIframeModalWindow(
                    'edit' . $id . $rand,
                    $link,
                    [
                        'display' => false,
                        'reloadonclose' => true
                    ]
                );
            }
            echo "</span>";
            echo "</div>";
            echo "</div>";
            echo "</div>";

        }
        echo "</div>";
    }
}
