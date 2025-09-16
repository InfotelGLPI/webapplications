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

namespace GlpiPlugin\Webapplications;

use CommonGLPI;
use DbUtils;
use Html;
use ProfileRight;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Profile
 */
class Profile extends \Profile
{
    public static $rightname = "profile";

    /**
     * @param CommonGLPI $item
     * @param int $withtemplate
     *
     * @return string|translated
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            if ($item->getField('interface') == 'central') {
                return self::createTabEntry(Webapplication::getTypeName(2));
            }
        }
        return '';
    }


    /**
     * @return string
     */
    public static function getIcon()//self::createTabEntry(
    {
        return "ti ti-border-all";
    }

    /**
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            $ID = $item->getID();
            $prof = new self();

            self::addDefaultProfileInfos(
                $ID,
                [
                    'plugin_webapplications_appliances' => 0,
                    'plugin_webapplications_streams' => 0,
                    'plugin_webapplications_entities' => 0,
                    'plugin_webapplications_processes' => 0,
                    'plugin_webapplications_entities' => 0,
                ]
            );
            $prof->showForm($ID);
        }
        return true;
    }

    /**
     * @param $ID
     */
    public static function createFirstAccess($ID)
    {
        //85
        self::addDefaultProfileInfos(
            $ID,
            [
                'plugin_webapplications_appliances' => 127,
                'plugin_webapplications_streams' => READ + CREATE + UPDATE + PURGE,
                'plugin_webapplications_entities' => READ + CREATE + UPDATE + PURGE,
                'plugin_webapplications_dashboards' => READ + CREATE + UPDATE + PURGE,
                'plugin_webapplications_processes' => READ + CREATE + UPDATE + PURGE,
            ],
            true
        );
    }

    /**
     * @param      $profiles_id
     * @param      $rights
     * @param bool $drop_existing
     *
     * @internal param $profile
     */
    public static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false)
    {
        $dbu = new DbUtils();
        $profileRight = new ProfileRight();
        foreach ($rights as $right => $value) {
            if ($dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
            ) && $drop_existing) {
                $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
            }
            if (!$dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
            )) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name'] = $right;
                $myright['rights'] = $value;
                $profileRight->add($myright);

                //Add right to the current session
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }


    /**
     * Show profile form
     *
     * @param int $profiles_id
     * @param bool $openform
     * @param bool $closeform
     *
     * @return nothing
     * @internal param int $items_id id of the profile
     * @internal param value $target url of target
     */
    public function showForm($profiles_id = 0, $openform = true, $closeform = true)
    {
        echo "<div class='firstbloc'>";
        if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
            && $openform) {
            $profile = new \Profile();
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        $profile = new \Profile();
        $profile->getFromDB($profiles_id);
        if ($profile->getField('interface') == 'central') {
            $rights = $this->getAllRights();
            $profile->displayRightsChoiceMatrix($rights, [
                'canedit' => $canedit,
                'default_class' => 'tab_bg_2',
                'title' => __('General'),
            ]);
        }

        if ($canedit
            && $closeform) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>\n";
            Html::closeForm();
        }
        echo "</div>";
    }

    /**
     * @param bool $all
     *
     * @return array
     */
    public static function getAllRights($all = false)
    {
        $rights = [
            [
                'itemtype' => Appliance::class,
                'label' => _n('Web application', 'Web applications', 2, 'webapplications'),
                'field' => 'plugin_webapplications_appliances',
            ],
            [
                'itemtype' => Dashboard::class,
                'label' => __('Appliance dashboard', 'webapplications'),
                'field' => 'plugin_webapplications_dashboards',
            ],
            [
                'itemtype' => Stream::class,
                'label' => _n('Stream', 'Streams', 2, 'webapplications'),
                'field' => 'plugin_webapplications_streams',
            ],
            [
                'itemtype' => Process::class,
                'label' => _n('Process', 'Processes', 2, 'webapplications'),
                'field' => 'plugin_webapplications_processes',
            ],
            [
                'itemtype' => Entity::class,
                'label' => _n('Entity', 'Entities', 2),
                'field' => 'plugin_webapplications_entities',
            ],
        ];

        return $rights;
    }

    /**
     * Init profiles
     *
     * @param $old_right
     *
     * @return int
     */

    public static function translateARight($old_right)
    {
        switch ($old_right) {
            case '':
                return 0;
            case 'r':
                return READ;
            case 'w':
                return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
            case '0':
            case '1':
                return $old_right;

            default:
                return 0;
        }
    }

    /**
     * @param $profiles_id the profile ID
     *
     * @return bool
     * @since 0.85
     * Migration rights from old system to the new one for one profile
     *
     */
    public static function migrateOneProfile($profiles_id)
    {
        global $DB;
        //Cannot launch migration if there's nothing to migrate...
        if (!$DB->tableExists('glpi_plugin_webapplications_profiles')) {
            return true;
        }

        foreach (
            $DB->request([
                'FROM' => 'glpi_plugin_webapplications_profiles',
                'WHERE' => ['profiles_id' => $profiles_id]]) as $profile_data
        ) {
            $matching = [
                'webapplications' => 'plugin_webapplications',
            ];
            $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
            foreach ($matching as $old => $new) {
                if (!isset($current_rights[$old])) {
                    $DB->update('glpi_profilerights', ['rights' => self::translateARight($profile_data[$old])], [
                        'name'        => $new,
                        'profiles_id' => $profiles_id,
                    ]);
                }
            }
        }
    }

    /**
     * Initialize profiles, and migrate it necessary
     */
    public static function initProfile()
    {
        global $DB;
        $profile = new self();
        $dbu = new DbUtils();
        //Add new rights in glpi_profilerights table
        foreach ($profile->getAllRights(true) as $data) {
            if ($dbu->countElementsInTable(
                "glpi_profilerights",
                ["name" => $data['field']]
            ) == 0) {
                ProfileRight::addProfileRights([$data['field']]);
            }
        }

        //Migration old rights in new ones
        $it = $DB->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_profiles',
        ]);
        foreach ($it as $prof) {
            self::migrateOneProfile($prof['id']);
        }

        $it = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $_SESSION['glpiactiveprofile']['id'],
                'name' => ['LIKE', '%plugin_webapplications%'],
            ],
        ]);
        foreach ($it as $prof) {
            if (isset($_SESSION['glpiactiveprofile'])) {
                $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
            }
        }
    }


    public static function removeRightsFromSession()
    {
        foreach (self::getAllRights(true) as $right) {
            if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
                unset($_SESSION['glpiactiveprofile'][$right['field']]);
            }
        }
    }
}
