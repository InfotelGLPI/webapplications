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

$(document).ready(function () {
    $(document).ajaxComplete(function () {
        document.querySelectorAll('[name="webapplicationavailabilities"], [name="webapplicationintegrities"], ' +
            '[name="webapplicationconfidentialities"], [name="webapplicationtraceabilities"]').forEach(function (e) {

            let select2id = e.id;
            let select2obj = "#" + select2id;

            var childSpan = $(select2obj).next('span').find('span:first-child');

            if (typeof (childSpan.children()[0]) !== 'undefined') {
                childSpan.children()[0].style = "color: black; font-weight: bold";
            }

            switch ($(select2obj).text()) {
                case '1':
                    childSpan.css("background-color", "#00FF00");
                    break;
                case '2':
                    childSpan.css("background-color", "#FFFF00");
                    break;
                case '3':
                    childSpan.css("background-color", "#FF9900");
                    break;
                case '4':
                    childSpan.css("background-color", "#FF0000");
                    break;
                default:
                    childSpan.css("background-color", "#999999");
                    break;
            }
        });
    });
});
