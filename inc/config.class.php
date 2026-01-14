<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2009-2025 by the Webapplications Development Team.

 https://github.com/InfotelGLPI/webapplications
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
$GLPI_TYPES = [];

/* Comme il existe une table dans la base mySQL qui correspond au nom de la classe alors on peut accéder aux champs de la façon suivante :
 $this->fields['nom_du_champ_dans_la_table']
table : glpi_plugin_webapplications_configs
classe: PluginWebapplicationsConfig
*/
class PluginWebapplicationsConfig extends CommonDBTM
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

    public static function canView()
    {
        return Session::haveRight('config', READ);
    }

    public static function canCreate()
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
        return __("Setup", "webapplication");
    }

    /**
     * @see CommonGLPI::defineTabs()
     */
    public function defineTabs($options = [])
    {
        $ong = [];
        // Onglet principal du formulaire de l'objet courant
        //$this->addDefaultFormTab($ong);

        // Onglets standards liés à d'autres classes ou à la vôtre
        $this->addStandardTab(PluginWebapplicationConfig::class, $ong, $options);
        $this->addStandardTab(__CLASS__, $ong, $options);
        //$this->addStandardTab('Log', $ong, $options); // Décommenter pour ajouter l'onglet "Historique"
        return $ong;
    }

    /* Pour que les onglets s'affichent, il faut la présence de cette méthode qui retourne "false" */
    public function isNewItem()
    {
        return false;
    }

    public function showForm($ID, array $options = [])
    {
        $this->getFromDB($ID);

        //The configuration is not deletable
        $options['candel']  = false;
        $options['colspan'] = 1;

        $this->showFormHeader($options);

        echo "<form name='form' method='post' action='" . $this->getFormURL() . "'>";

        echo "<div align='center'><table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2'>".self::getTypeName()."</th></tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Use description field', 'webapplications');
        echo "</td>";
        echo "<td>";
        Dropdown::showYesNo('use_fields_description', $this->fields['use_fields_description']);
        echo "</td>";
        echo "</tr>";

        $array = [
            'Appliance|name' => __('Appliance') . ' - ' . __('Name', 'webapplications'),
            'Appliance|comment' => __('Appliance') . ' - ' . __('Comment', 'webapplications'),
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

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Use field', 'webapplications');
        echo "</td>";
        echo "<td>";
        Dropdown::showFromArray('fields', $array, ['value' => $this->fields['fields_description_table'] . '|' . $this->fields['fields_description_name']]);
        echo "</td>";
        echo "</tr>";


        $this->showFormButtons($options);
        echo "</table></div>";
        Html::closeForm();
    }
}
