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
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Plugin;
use PluginFieldsContainer;
use PluginFieldsField;
use Session;
use Toolbox;

$GLPI_TYPES = [];

/* Since a table in the MySQL database matches the class name, the fields can be
 accessed as follows:
 $this->fields['field_name_in_the_table']
table: glpi_plugin_webapplications_configs
class: Config
*/
class Config extends CommonDBTM
{
    public static $rightname = 'plugin_webapplications_configs';
    public $dohistory = true;

    public function __construct()
    {
        /** @var \DBmysql $DB */
        global $DB;
        if ($DB->tableExists(self::getTable())) {
            $this->getFromDB(1);
        }
    }

    public static function canView(): bool
    {
        return Session::haveRight('config', READ);
    }

    public static function canCreate(): bool
    {
        return Session::haveRight('config', UPDATE);
    }

    public static function getConfig($update = false)
    {
        static $config = null;

        if (is_null($config)) {
            $config = new self();
        }
        if ($update) {
            $config->getFromDB(1);
        }

        return $config;
    }

    public static function getTypeName($nb = 0)
    {
        return __("Setup", "webapplications");
    }

    /**
     * @see CommonGLPI::defineTabs()
     */
    public function defineTabs($options = [])
    {
        $ong = [];
        // Main form tab of the current object
        //$this->addDefaultFormTab($ong);

        // Standard tabs linked to other classes or to your own
        $this->addStandardTab(Config::class, $ong, $options);
        $this->addStandardTab(__CLASS__, $ong, $options);
        //$this->addStandardTab('Log', $ong, $options); // Uncomment to add the "History" tab
        return $ong;
    }

    /* This method returning "false" must be present for the tabs to be displayed */
    public function isNewItem()
    {
        return false;
    }

    public function prepareInputForAdd($input)
    {
        $allowed = ['id', 'use_fields_description', 'fields_description_table', 'fields_description_name'];
        $input = array_intersect_key($input, array_flip($allowed));
        return parent::prepareInputForAdd($input);
    }

    public function prepareInputForUpdate($input)
    {
        $allowed = ['id', 'use_fields_description', 'fields_description_table', 'fields_description_name'];
        $input = array_intersect_key($input, array_flip($allowed));
        return parent::prepareInputForUpdate($input);
    }

    /**
     * Allowed "fields" choices for the PDF description selector, keyed by the
     * "table|name" token that gets persisted in configuration. Used both to
     * render the dropdown and to whitelist the submitted value server-side.
     *
     * @return array<string,string>
     */
    public static function getFieldsChoices(): array
    {
        $array = [
            'Appliance|name' => __('Appliance') . ' - ' . __('Name'),
            'Appliance|comment' => __('Appliance') . ' - ' . __('Comment'),
        ];

        $plugin = new Plugin();
        if ($plugin->isActivated('fields')) {
            $fieldsContainer = new PluginFieldsContainer();
            foreach ($fieldsContainer->find(['type' => 'dom']) as $row) {
                if (strpos($row['itemtypes'], 'Appliance') !== false) {
                    $fieldsfields = new PluginFieldsField();
                    $rowfields = $fieldsfields->find(['plugin_fields_containers_id' => $row['id']], ['ranking ASC']);
                    foreach ($rowfields as $rowfield) {
                        switch ($rowfield['type']) {
                            case 'text':
                            case 'textarea':
                            case 'richtext':
                                $array['Fields' . '|' .$rowfield['id']] = 'Fields - ' . $rowfield['label'];
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        return $array;
    }

    public function showForm($ID, array $options = [])
    {
        $this->getFromDB($ID);

        $array = self::getFieldsChoices();

        // Capture the GLPI-rendered dropdowns so they can be injected in the Twig template.
        $use_fields_description_dropdown = Dropdown::showYesNo(
            'use_fields_description',
            $this->fields['use_fields_description'],
            -1,
            ['display' => false]
        );
        $fields_dropdown = Dropdown::showFromArray(
            'fields',
            $array,
            [
                'value'   => $this->fields['fields_description_table'] . '|' . $this->fields['fields_description_name'],
                'display' => false,
            ]
        );

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_config_form.html.twig', [
            'item_form_url'                   => Toolbox::getItemTypeFormURL(self::class),
            'item_id'                         => (int) $this->fields['id'],
            'use_fields_description_dropdown' => $use_fields_description_dropdown,
            'fields_dropdown'                 => $fields_dropdown,
        ]);
    }
}
