<script>

(function($) {
"use strict";

var recipientServerParams = {
	"clients": "[name='clients[]']",
};

var table_recipients = $('.table-recipients');    
initDataTable('.table-recipients', window.location.href, [], [],
        recipientServerParams, [1, 'desc']);

$.each(recipientServerParams, function(i, obj) {
    $('select' + obj).on('change', function() {  
        table_recipients.DataTable().ajax.reload()
            .columns.adjust();
    });
});


})(jQuery);

</script>