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

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Webapplications\Printpdf;
use GlpiPlugin\Webapplications\Pdf;

global $DB;

Session::checkRightsOr(Printpdf::$rightname, [READ, UPDATE]);

if (isset($_POST["PrintPdf"])) {
    if (isset($_POST["plugin_webapplications_appliance_id"]) && $_POST["plugin_webapplications_appliance_id"] > 0) {
        $appliance_id = (int) $_POST['plugin_webapplications_appliance_id'];
        $appliance = new Appliance();
        // Enforce object-level right + entity access before exporting: the appliance id
        // is user-controlled and the Printpdf right is not scoped to the caller's entities.
        if (!$appliance->can($appliance_id, READ)) {
            throw new AccessDeniedHttpException();
        }

        $datenow = new DateTime();

        $title = __('Printed on', 'webapplications')." ".Html::convDateTime($datenow->format('Y-m-d H:i:s'));
        if ($appliance->fields['date_mod'] != null) {
            $title .= " - ".__('Last update')." ".Html::convDateTime($appliance->fields['date_mod']);
        }

        $docPdf = new Pdf(
            $title
            , $appliance->fields['name'], $appliance_id);

        $docPdf->drawPdf();
    } else {
        $message = __('An error has occurred','webapplications');
        Session::addMessageAfterRedirect($message, false, ERROR);
    }
}else {
    Html::back();
}
