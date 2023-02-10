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
                var dictfields = document.querySelectorAll('[name="webapplicationavailabilities"], [name="webapplicationintegrities"], ' +
                                                     '[name="webapplicationconfidentialities"], [name="webapplicationtraceabilities"]');

                $.each(dictfields, function(){
                    this.onchange = switchColor;
                    var event = new Event('change');
                    this.dispatchEvent(event);

                });


                function switchColor(e){

                    if(e.target.type == 'select-one') {
                        var field = $("span[aria-labelledby='select2-" + e.target.id + "-container']");
                        field.children()[0].style = "color: black; font-weight: bold";
                        switch (field.text()) {
                            case '1':
                                field.css("background-color", "#00FF00");
                                break;
                            case '2':
                                field.css("background-color", "#FFFF00");
                                break;
                            case '3':
                                field.css("background-color", "#FF9900");
                                break;
                            case '4':
                                field.css("background-color", "#FF0000");
                                break;
                            default:
                                field.css("background-color", "#999999");
                                break;
                        }
                    }
                    else{
                        switch (e.target.innerText) {
                            case '1':
                                e.target.style = "background-color: #00FF00; color: black; font-weight: bold; width: 29px";
                                break;
                            case '2':
                                e.target.style = "background-color: #FFFF00; color: black; font-weight: bold; width: 29px";
                                break;
                            case '3':
                                e.target.style = "background-color: #FF9900; color: black; font-weight: bold; width: 29px";
                                break;
                            case '4':
                                e.target.style = "background-color: #FF0000; color: black; font-weight: bold; width: 29px";
                                break;
                            default:
                                e.target.style = "background-color: #999999; color: black; font-weight: bold; width: 29px";
                                break;
                        }
                    }

                }
            });
        }

        return this;
    };
}(jQuery));

$(document).webapplications_change_securityneedscolor();
