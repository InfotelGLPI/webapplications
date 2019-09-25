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

/**
 * Class PluginWebapplicationsWebapplication
 */
class PluginWebapplicationsWebapplication extends CommonDBTM {

   public    $dohistory  = true;
   static    $rightname  = "plugin_webapplications";
   protected $usenotepad = true;

   static $types = ['Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone',
                         'Printer', 'Software', 'Entity', 'SoftwareLicense', 'PluginWebapplicationsWebapplication','Certificate'];
   static $tags  = '[WEBAPPLICATION_URL]';

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {

      return _n('Web application', 'Web applications', $nb, 'webapplications');
   }

   //clean if webapplications are deleted
   /**
    *
    */
   function cleanDBonPurge() {

      $temp = new PluginWebapplicationsWebapplication_Item();
      $temp->deleteByCriteria(['plugin_webapplications_webapplications_id' => $this->fields['id']]);
   }

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
      }
      return '';
   }


   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Supplier') {
         PluginWebapplicationsWebapplication_Item::showForSupplier($item);
      }
      return true;
   }

   /**
    * @param CommonDBTM $item
    *
    * @return int
    */
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_webapplications_webapplications',
                                        ["suppliers_id" => $item->getID()]);
   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => self::getTypeName(2)
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'itemlink_type'      => $this->getType()
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => 'glpi_plugin_webapplications_webapplicationtypes',
         'field'              => 'name',
         'name'               => PluginWebapplicationsWebapplicationType::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'address',
         'name'               => __('URL'),
         'datatype'           => 'weblink'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_plugin_webapplications_webapplicationservertypes',
         'field'              => 'name',
         'name'               => PluginWebapplicationsWebapplicationServerType::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_plugin_webapplications_webapplicationtechnics',
         'field'              => 'name',
         'name'               => PluginWebapplicationsWebapplicationTechnic::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

      $tab[] = [
         'id'                 => '7',
         'table'              => 'glpi_suppliers',
         'field'              => 'name',
         'name'               => __('Supplier'),
         'datatype'           => 'itemlink'
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'version',
         'name'               => __('Version')
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'users_id_tech',
         'name'               => __('Technician in charge of the hardware'),
         'datatype'           => 'dropdown',
         'right'              => 'interface'
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => 'glpi_groups',
         'field'              => 'name',
         'linkfield'          => 'groups_id_tech',
         'name'               => __('Group in charge of the hardware'),
         'condition'          => '`is_assign`',
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'backoffice',
         'name'               => __('Backoffice URL', 'webapplications'),
         'datatype'           => 'weblink'
      ];

      $tab[] = [
         'id'                 => '13',
         'table'              => 'glpi_plugin_webapplications_webapplications_items',
         'field'              => 'items_id',
         'nosearch'           => true,
         'massiveaction'      => false,
         'name'               => _n('Associated item', 'Associated items', 2),
         'forcegroupby'       => true,
         'joinparams'         => ['jointype'           => 'child']
      ];

      $tab[] = [
         'id'                 => '14',
         'table'              => 'glpi_manufacturers',
         'field'              => 'name',
         'name'               => __('Editor', 'webapplications'),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '15',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Child entities'),
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '16',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '17',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'massiveaction'      => false,
         'name'               => __('Last update'),
         'datatype'           => 'datetime'
      ];

      $tab[] = [
         'id'                 => '18',
         'table'              => $this->getTable(),
         'field'              => 'is_helpdesk_visible',
         'name'               => __('Associable to a ticket'),
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'number'
      ];

      $tab[] = [
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '81',
         'table'              => 'glpi_entities',
         'field'              => 'entities_id',
         'name'               => __('Entity') . "-" . __('ID')
      ];
      return $tab;
   }


   //define header form
   /**
    * @param array $options
    *
    * @return array
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginWebapplicationsWebapplication_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Change_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }


   /**
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    **/
   function getSelectLinkedItem() {

      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_webapplications_webapplications_items`
              WHERE `plugin_webapplications_webapplications_id`='" . $this->fields['id'] . "'";
   }


   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      //name of webapplications
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      //version of webapplications
      echo "<td>" . __('Version') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "version", ['size' => "15"]);
      echo "</td>";
    
      //version description
    	 echo "<tr class='tab_bg_1'>";
	     echo "<td>" . __('Descripción') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "descwebapp");
      echo "</td>";
    
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //type of webapplications
      echo "<td>" . PluginWebapplicationsWebapplicationType::getTypeName(1) . "</td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationType',
                     ['value'  => $this->fields["plugin_webapplications_webapplicationtypes_id"],
                           'entity' => $this->fields["entities_id"]]);
      echo "</td>";
      //server type of webapplications
      echo "<td>" . PluginWebapplicationsWebapplicationServerType::getTypeName(1) . "</td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationServerType',
                     ['value' => $this->fields["plugin_webapplications_webapplicationservertypes_id"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //location of webapplications
      echo "<td>" . __('Location') . "</td>";
      echo "<td>";
      Dropdown::show('Location', ['value'  => $this->fields["locations_id"],
                                       'entity' => $this->fields["entities_id"]]);
      echo "</td>";
      //language of webapplications
      echo "<td>" . PluginWebapplicationsWebapplicationTechnic::getTypeName(1) . "</td>";
      echo "<td>";
      Dropdown::show('PluginWebapplicationsWebapplicationTechnic',
                     ['value' => $this->fields["plugin_webapplications_webapplicationtechnics_id"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //users
      echo "<td>" . __('Technician in charge of the hardware') . "</td><td>";
      User::dropdown(['name'   => "users_id_tech",
                           'value'  => $this->fields["users_id_tech"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'interface']);
      echo "</td>";
      //supplier of webapplications
      echo "<td>" . __('Supplier') . "</td>";
      echo "<td>";
      Dropdown::show('Supplier', ['value'  => $this->fields["suppliers_id"],
                                       'entity' => $this->fields["entities_id"]]);
      echo "</td>";
      echo "</tr>";
    
      echo "<tr class='tab_bg_1'>";
      //version ddministrative manager
      echo "<td>Responsable Administrativo</td><td>";
      User::dropdown(['name'   => "users_id_resp_adm",
                           'value'  => $this->fields["users_id_resp_adm"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'interface']);
      echo "</td>";
      echo "<td></td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //groups
      echo "<td>" . __('Group in charge of the hardware') . "</td><td>";
      Dropdown::show('Group', ['name'      => "groups_id_tech",
                                    'value'     => $this->fields["groups_id_tech"],
                                    'entity'    => $this->fields["entities_id"],
                                    'condition' => ['is_assign' => 1]]);
      echo "</td>";

      //manufacturer of webapplications
      echo "<td>" . __('Editor', 'webapplications') . "</td>";
      echo "<td>";
      Dropdown::show('Manufacturer', ['value'  => $this->fields["manufacturers_id"],
                                           'entity' => $this->fields["entities_id"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //url of webapplications
      echo "<td>" . __('URL') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "address", ['size' => "65"]);
      echo "</td>";
      //is_helpdesk_visible
      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //backoffice of webapplications
      echo "<td>" . __('Backoffice URL', 'webapplications') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "backoffice", ['size' => "65"]);
      echo "</td>";
    
      // version server and database complements
      echo "<tr class=\"tab_bg_1 rowHover\" style=\"background-color: #f1f1f1;\">";
      echo "<td><h3>Servidor de Aplicaciones</h3></td>";
      echo "<td></td>";
      echo "<td><h3>Base de Datos</h3></td>";
      echo "<td></td>";
      echo "</tr>";
	  
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Nombre Servidor') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "nameappsrv");
      echo "</td>";
	     echo "<td>" . __('Nombre Base de Datos') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "namedbrv");
      echo "</td>";
	     echo "</tr>";
	  
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('IP Servidor') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "ipappsrv");
      echo "</td>";
	     echo "<td>" . __('IP Base de Datos') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "ipdbrv");
      echo "</td>";
	     echo "</tr>";
	  
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Puerto Servidor') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "puertoappsrv");
      echo "</td>";
	     echo "<td>" . __('Puerto Base de Datos') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "puertodbsrv");
      echo "</td>";
	     echo "</tr>";

      echo "<td class='center' colspan = '2'>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //comments of webapplications
      echo "<td class='top center' colspan='4'>" . __('Comments') . "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td class='top center' colspan='4'><textarea cols='125' rows='3' name='comment' >" .
           $this->fields["comment"] . "</textarea>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }


   /**
    * Make a select box for link webapplications
    *
    * Parameters which could be used in options array :
    *    - name : string / name of the select (default is plugin_webapplications_webapplications_id)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default -1 : no restriction)
    *    - used : array / Already used items ID: not to display in dropdown (default empty)
    *
    * @param $options array of possible options
    *
    * @return nothing (print out an HTML select box)
    **/
   static function dropdownWebapplication($options = []) {
      global $DB, $CFG_GLPI;

      $p['name']    = 'plugin_webapplications_webapplications_id';
      $p['entity']  = '';
      $p['used']    = [];
      $p['display'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }
      $dbu = new DbUtils();
      $where = " WHERE `glpi_plugin_webapplications_webapplications`.`is_deleted` = '0' " .
               $dbu->getEntitiesRestrictRequest("AND", "glpi_plugin_webapplications_webapplications", '', $p['entity'], true);

      $p['used'] = array_filter($p['used']);
      if (count($p['used'])) {
         $where .= " AND `id` NOT IN (0, " . implode(",", $p['used']) . ")";
      }

      $query  = "SELECT *
                FROM `glpi_plugin_webapplications_webapplicationtypes`
                WHERE `id` IN (SELECT DISTINCT `plugin_webapplications_webapplicationtypes_id`
                               FROM `glpi_plugin_webapplications_webapplications`
                             $where)
                ORDER BY `name`";
      $result = $DB->query($query);

      $values = [0 => Dropdown::EMPTY_VALUE];

      while ($data = $DB->fetch_assoc($result)) {
         $values[$data['id']] = $data['name'];
      }
      $rand     = mt_rand();
      $out      = Dropdown::showFromArray('_webapplicationtype', $values, ['width'   => '30%',
                                                                                'rand'    => $rand,
                                                                                'display' => false]);
      $field_id = Html::cleanId("dropdown__webapplicationtype$rand");

      $params = ['webapplicationtype' => '__VALUE__',
                      'entity'             => $p['entity'],
                      'rand'               => $rand,
                      'myname'             => $p['name'],
                      'used'               => $p['used']];

      $out .= Ajax::updateItemOnSelectEvent($field_id, "show_" . $p['name'] . $rand,
                                            $CFG_GLPI["root_doc"] . "/plugins/webapplications/ajax/dropdownTypeWebApplications.php",
                                            $params, false);
      $out .= "<span id='show_" . $p['name'] . "$rand'>";
      $out .= "</span>\n";

      $params['webapplicationtype'] = 0;
      $out .= Ajax::updateItem("show_" . $p['name'] . $rand,
                               $CFG_GLPI["root_doc"] . "/plugins/webapplications/ajax/dropdownTypeWebApplications.php",
                               $params, false);
      if ($p['display']) {
         echo $out;
         return $rand;
      }
      return $out;
   }


   /**
    * Show for PDF an webapplications
    *
    * @param $pdf object for the output
    *
    * @internal param of $ID the webapplications
    */
   function show_PDF($pdf) {
      $pdf->setColumnsSize(50, 50);
      $col1 = '<b>' . __('ID') . ' ' . $this->fields['id'] . '</b>';
      if (isset($this->fields["date_mod"])) {
         $col2 = printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      } else {
         $col2 = '';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>' . __('Name') . ':</i></b> ' . $this->fields['name'],
         '<b><i>' . PluginWebapplicationsWebapplicationType::getTypeName(1) . ' :</i></b> ' .
         Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationtypes',
                                               $this->fields['plugin_webapplications_webapplicationtypes_id'])));
      $dbu = new DbUtils();
      $pdf->displayLine(
         '<b><i>' . __('Technician in charge of the hardware') . ':</i></b> ' . $dbu->getUserName($this->fields['users_id_tech']),
         '<b><i>' . __('Group in charge of the hardware') . ':</i></b> ' . Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                                                                                 $this->fields['groups_id_tech'])));
      $pdf->displayLine(
         '<b><i>' . __('Location') . ':</i></b> ' .
         Html::clean(Dropdown::getDropdownName('glpi_locations', $this->fields['locations_id'])),
         '<b><i>' . PluginWebapplicationsWebapplicationServerType::getTypeName(1) . ':</i></b> ' .
         Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationservertypes',
                                               $this->fields["plugin_webapplications_webapplicationservertypes_id"])));
      $pdf->displayLine(
         '<b><i>' . PluginWebapplicationsWebapplicationTechnic::getTypeName(1) . ' :</i></b> ' .
         Html::clean(Dropdown::getDropdownName('glpi_plugin_webapplications_webapplicationtechnics',
                                               $this->fields['plugin_webapplications_webapplicationtechnics_id'])),
         '<b><i>' . __('Version') . ':</i></b> ' . $this->fields['version']);

      $pdf->displayLine(
         '<b><i>' . __('Supplier') . ':</i></b> ' .
         Html::clean(Dropdown::getDropdownName('glpi_suppliers', $this->fields['suppliers_id'])),
         '<b><i>' . __('Editor', 'webapplications') . ':</i></b> ' .
         Html::clean(Dropdown::getDropdownName('glpi_manufacturers',
                                               $this->fields["manufacturers_id"])));

      $pdf->displayLine(
         '<b><i>' . __('URL') . ':</i></b> ' . $this->fields['address'], '');

      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>' . __('Comments') . ':</i></b>', $this->fields['comment']);

      $pdf->displaySpace();
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
    **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    **/
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::getSpecificMassiveActions()
    *
    * @param null $checkitem
    *
    * @return an
    */
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if (Session::getCurrentInterface() == 'central') {
         if ($isadmin) {
            $actions['PluginWebapplicationsWebapplication' . MassiveAction::CLASS_ACTION_SEPARATOR . 'install']   = _x('button', 'Associate');
            $actions['PluginWebapplicationsWebapplication' . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall'] = _x('button', 'Dissociate');

            if (Session::haveRight('transfer', READ)
                && Session::isMultiEntitiesMode()) {
               $actions['PluginWebapplicationsWebapplication' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
            }
         }
      }
      return $actions;
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    *
    * @param MassiveAction $ma
    *
    * @return bool|false
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'plugin_webapplications_add_item':
            self::dropdownWebapplication([]);
            echo "&nbsp;" .
                 Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "install" :
            Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'item_item',
                                                        'itemtype_name' => 'typeitem',
                                                        'itemtypes'     => self::getTypes(true),
                                                        'checkright'
                                                                        => true,
                                                  ]);
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "uninstall" :
            Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'item_item',
                                                        'itemtype_name' => 'typeitem',
                                                        'itemtypes'     => self::getTypes(true),
                                                        'checkright'
                                                                        => true,
                                                  ]);
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    *
    * @param MassiveAction $ma
    * @param CommonDBTM    $item
    * @param array         $ids
    *
    * @return nothing|void
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $web_item = new PluginWebapplicationsWebapplication_Item();

      switch ($ma->getAction()) {
         case "plugin_webapplications_add_item":
            $input = $ma->getInput();
            foreach ($ids as $id) {
               $input = ['plugin_webapplications_webapplications_id' => $input['plugin_webapplications_webapplications_id'],
                              'items_id'                                  => $id,
                              'itemtype'                                  => $item->getType()];
               if ($web_item->can(-1, UPDATE, $input)) {
                  if ($web_item->add($input)) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
               }
            }

            return;
         case "transfer" :
            $input = $ma->getInput();
            if ($item->getType() == 'PluginWebapplicationsWebapplication') {
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  $type = PluginWebapplicationsWebapplicationType::transfer($item->fields["plugin_webapplications_webapplicationtypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"]                                            = $key;
                     $values["plugin_webapplications_webapplicationtypes_id"] = $type;
                     $item->update($values);
                  }

                  unset($values);
                  $values["id"]          = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;

         case 'install' :
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($item->can($key, UPDATE)) {
                  $values = ['plugin_webapplications_webapplications_id' => $key,
                                  'items_id'                                  => $input["item_item"],
                                  'itemtype'                                  => $input['typeitem']];
                  if ($web_item->add($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                  $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
               }
            }
            return;

         case 'uninstall':
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($web_item->deleteItemByWebApplicationsAndItem($key, $input['item_item'], $input['typeitem'])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   /**
    * @param string     $link
    * @param CommonDBTM $item
    *
    * @return array
    */
   static function generateLinkContents($link, CommonDBTM $item) {

      if (strstr($link, "[WEBAPPLICATION_URL]")) {
         $link = str_replace("[WEBAPPLICATION_URL]", $item->fields['address'], $link);
         return [$link];
      }

      return parent::generateLinkContents($link, $item);
   }
}
