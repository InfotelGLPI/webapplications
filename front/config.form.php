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

include("../../../inc/includes.php");

global $DB;
use GlpiPlugin\Webapplications\Config;
$config = new Config();
Session::checkRightsOr(Config::$rightname, [READ, UPDATE]);

if (isset($_POST["add"])) {
    $fields = explode('|', $_POST["fields"]);
    $_POST['fields_description_table'] = $fields[0];
    $_POST['fields_description_name'] = $fields[1];
    $config->add($_POST);
    Html::back();
} elseif (isset($_POST["update"])) {
    $fields = explode('|', $_POST["fields"]);
    $_POST['fields_description_table'] = $fields[0];
    $_POST['fields_description_name'] = $fields[1];
    $config->update($_POST);
    Html::back();
}else {
    Html::header(__('Setup', 'webapplications'), $_SERVER['PHP_SELF'], 'config', 'webapplications');

    /* showForm() affiche seulement le formulaire */
    $config->showForm(1);

    Html::footer();
}