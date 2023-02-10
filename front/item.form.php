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

include('../../../inc/includes.php');


Session::checkLoginUser();

use Glpi\Event;

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

if (isset($_GET['_in_modal']) && isset($_GET['type'])) {

    $_SESSION['reload']=true;
    Html::popHeader($_GET['type']::getTypeName(2), $_SERVER['PHP_SELF']);

    $options = ['withtemplate' => $_GET["withtemplate"], 'formoptions'  => "data-track-changes=true"];
    if(isset($_GET['appliance_id'])) {
        $options['appliances_id'] = $_GET['appliance_id'];
    }
    $_GET['type']::displayFullPageForItem($_GET['id'], $options );


    Html::popFooter();

}
else {

    $_GET['type']::displayFullPageForItem($_GET['id']);
}