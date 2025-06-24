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
