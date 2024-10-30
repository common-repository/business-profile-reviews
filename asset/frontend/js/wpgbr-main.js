let wpgbr = (function($, window, undefined) {
    let wpgbr = {};
    let buildTemplate = function () {
        $.ajax({
            type : "POST",
            dataType : "html",
            cache: false,
            // contentType: false,
            // processData: false,
            url : my_ajax_object.ajax_url,
            data : {
                action: "load_widget_template",
            },//data,
            success: function(response) {
                $('body').append(response);
                // resultElement.html(response.html);
            },
            error: function(response) {
                console.log(response);
                // msgElement.oktHelper().showErrorMsg(response.responseJSON);
            }
        });
    }

    window.onload = function() {
        buildTemplate();
        $('body').on('click', '.wpgbr-action-button',function(e) {
            e.preventDefault();
            let href = $('.wpgbr-action-button').attr('href');
            wpgbr.popup(href, 600, 500);
        });
    }
    wpgbr.openDiv = function() {
        document.getElementById("wp-gbr-viewer").style.display = "block";
    }

    wpgbr.closeDiv = function() {
        document.getElementById("wp-gbr-viewer").style.display = "none";
    }

    wpgbr.popup = function(url, width, height, prms, top, left) {
        top = top || (screen.height/2)-(height/2);
        left = left || (screen.width/2)-(width/2);
        return window.open(url, '', 'location=1,status=1,resizable=yes,width='+width+',height='+height+',top='+top+',left='+left);
    }


    return wpgbr;
})(jQuery, window);
