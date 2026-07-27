<script>

(function($) {
  "use strict"; 


SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {
     var canvas = this._ctx.canvas;
       // First duplicate the canvas to not alter the original
       var croppedCanvas = document.createElement('canvas'),
       croppedCtx = croppedCanvas.getContext('2d');

       croppedCanvas.width = canvas.width;
       croppedCanvas.height = canvas.height;
       croppedCtx.drawImage(canvas, 0, 0);

       // Next do the actual cropping
       var w = croppedCanvas.width,
       h = croppedCanvas.height,
       pix = {
         x: [],
         y: []
       },
       imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height),
       x, y, index;

       for (y = 0; y < h; y++) {
         for (x = 0; x < w; x++) {
           index = (y * w + x) * 4;
           if (imageData.data[index + 3] > 0) {
             pix.x.push(x);
             pix.y.push(y);

           }
         }
       }
       pix.x.sort(function(a, b) {
         return a - b
       });
       pix.y.sort(function(a, b) {
         return a - b
       });
       var n = pix.x.length - 1;

       w = pix.x[n] - pix.x[0];
       h = pix.y[n] - pix.y[0];
       var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

       croppedCanvas.width = w;
       croppedCanvas.height = h;
       croppedCtx.putImageData(cut, 0, 0);

       return croppedCanvas.toDataURL();
     };


 function signaturePadChanged() {

   var input = document.getElementById('signatureInput');
   var $signatureLabel = $('#signatureLabel');
   $signatureLabel.removeClass('text-danger');

   if (signaturePad.isEmpty()) {
     $signatureLabel.addClass('text-danger');
     input.value = '';
     return false;
   }

   $('#signatureInput-error').remove();
   var partBase64 = signaturePad.toDataURLAndRemoveBlanks();
   partBase64 = partBase64.split(',')[1];
   input.value = partBase64;
 }

 var canvas = document.getElementById("signature");
 var signaturePad = new SignaturePad(canvas, {
  maxWidth: 2,
  onEnd:function(){
    signaturePadChanged();
  }
});

$('#identityConfirmationForm').on('submit', function() {
   signaturePadChanged();
 });  

})(jQuery);

function signature_clear(){
"use strict";
var canvas = document.getElementById("signature");
var signaturePad = new SignaturePad(canvas, {
  maxWidth: 2,
  onEnd:function(){

  }
});
signaturePad.clear();

}

function sign_package() {
  "use strict";
  $('#add_action').modal('show');
}


function sign_request(id){
   "use strict";
    upload_sign(id, true);
}


function upload_sign(id, sign_code){
  "use strict";
    var data = {};
    data.id = id;

    if(sign_code == true){
      data.signature = $('input[name="signature"]').val();

    }
    $.post(admin_url + 'logistic/upload_sign/' + id, data).done(function(response){
        response = JSON.parse(response); 
        if (response.success === true || response.success == 'true') {
            $('#sign_div').html(response.html);

            $('#add_action').modal('hide');
        }
    });
}


function remove_sign(id){
  "use strict";
	if (confirm_delete()) {
		$.post(admin_url + 'logistic/remove_sign/' + id).done(function(response){
	        response = JSON.parse(response); 
	        if (response.success === true || response.success == 'true') {
	            $('#sign_div').html(response.html);
	        }
	    });
	}
}

function submit_shipment(){
  "use strict";
	var sign_btn = $('.sign-open-modal');

	var delivery_date = $('input[name="delivery_date"]').val();
	var delivered_by = $('select[name="delivered_by"]').val();
	var receive_by = $('input[name="receive_by"]').val();

	if(sign_btn.length > 0){
		alert_float('warning', '<?php echo _l('lg_please_add_signature'); ?>');
	}else{
		if(delivery_date != '' && delivered_by != '' && receive_by != '' &&  document.getElementById("file").files.length > 0 ){
			$('#shipment_tracking_package-form').submit();
		}else{
			alert_float('warning', '<?php echo _l('lg_please_complete_all_information'); ?>');
		}
	}
}

</script>