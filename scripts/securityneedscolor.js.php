<?php
use Glpi\Event;
include('../../../inc/includes.php');
header('Content-Type: text/javascript');

?>

(function ($) {
    $.fn.webapplications_change_securityneedscolor = function () {


        init();

        // Start the plugin
        function init() {
            $(document).ajaxStop(function(){

                var select_a = document.getElementById($("*[name='webapplicationavailabilities_id']").attr('id'));
                var select_i = document.getElementById($("*[name='webapplicationintegrities_id']").attr('id'));
                var select_c = document.getElementById($("*[name='webapplicationconfidentialities_id']").attr('id'));
                var select_t = document.getElementById($("*[name='webapplicationtraceabilities_id']").attr('id'));

                select_a.onchange = switchColor;
                select_i.onchange = switchColor;
                select_c.onchange = switchColor;
                select_t.onchange = switchColor;

                var event = new Event('change');

                select_a.dispatchEvent(event);
                select_i.dispatchEvent(event);
                select_c.dispatchEvent(event);
                select_t.dispatchEvent(event);

                function switchColor(e){

                    var field = $("span[aria-labelledby='select2-"+e.target.id+"-container']");
                    field.children()[0].style = "color: black; font-weight: bold";
                    switch (field.text()) {
                        case '1': field.css("background-color", "#00FF00"); break;
                        case '2': field.css("background-color", "#FFFF00"); break;
                        case '3': field.css("background-color", "#FF9900"); break;
                        case '4': field.css("background-color", "#FF0000"); break;
                        default: field.css("background-color", "#999999"); break;
                    }
                }
            });
        }

        return this;
    };
}(jQuery));

$(document).webapplications_change_securityneedscolor();