<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2009-2022 by the Webapplications Development Team.

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
use GlpiPlugin\Webapplications\Printpdf;
use GlpiPlugin\Webapplications\Pdf;

global $DB;

Session::checkRightsOr(Printpdf::$rightname, [READ, UPDATE]);

if (isset($_POST["PrintPdf"])) {
    $appliance = new Appliance();
    $appliance->getFromDB($_POST['plugin_webapplications_applicatif_id']);
    $date = new DateTime($appliance->fields['date_mod']);
    $datenow = new DateTime();
    $docPdf = new Pdf(
        __('Printed on ', 'webapplications') . $datenow->format('Y-m-d H:i:s') .
        __(' Last update ', 'webapplications') . $date->format('Y-m-d H:i:s')
        , $appliance->fields['name'], $_POST['plugin_webapplications_applicatif_id']);

    $docPdf->drawPdf($appliance);
//    Html::back();
}else {
    Html::back();
}