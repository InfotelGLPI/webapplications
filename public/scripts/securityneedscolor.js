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


/* global $ */

/**
 * Background color of the DICT (availability, integrity, confidentiality, traceability) select2
 * fields, matching the level: same mapping as Appliance::getColorForDICT().
 *
 * The colors used to be applied only on `ajaxComplete`, i.e. after some later AJAX request of the
 * page: on load the fields stayed uncolored until one happened, and picking another level did not
 * recolor the field. They are now applied as soon as the fields are in the page (also when they
 * are injected later, in an AJAX loaded tab) and on every change.
 */
(() => {
    const FIELDS = [
        'webapplicationavailabilities',
        'webapplicationintegrities',
        'webapplicationconfidentialities',
        'webapplicationtraceabilities',
    ];
    const SELECTOR = FIELDS.map((name) => `select[name="${name}"]`).join(', ');
    const COLORS = {
        '1': '#00FF00',
        '2': '#FFFF00',
        '3': '#FF9900',
        '4': '#FF0000',
    };

    const colorize = (select) => {
        // Rendered selection box of the select2 widget that follows the <select>
        const selection = $(select).next('.select2-container').find('.select2-selection');
        if (selection.length === 0) {
            return false;
        }
        selection.css('background-color', COLORS[String(select.value)] ?? '#999999');
        selection.find('.select2-selection__rendered').css({
            color: 'black',
            'font-weight': 'bold',
        });
        return true;
    };

    const colorizeAll = (root) => {
        $(root).find(SELECTOR).addBack(SELECTOR).each(function () {
            colorize(this);
        });
    };

    // A new level picked by the user
    $(document).on('change', SELECTOR, function () {
        colorize(this);
    });

    $(() => {
        colorizeAll(document);

        // select2 builds its container right after the <select>, and the fields can arrive later
        // in an AJAX loaded tab: color them whenever they show up
        new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType !== Node.ELEMENT_NODE) {
                        continue;
                    }
                    if (node.matches('.select2-container')) {
                        const select = node.previousElementSibling;
                        if (select && select.matches(SELECTOR)) {
                            colorize(select);
                        }
                    } else {
                        colorizeAll(node);
                    }
                }
            }
        }).observe(document.body, {childList: true, subtree: true});
    });
})();
