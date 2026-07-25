<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<style>
    #qr-reader {
        width: 500px;
        margin: auto;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="text-center">
                <div class="panel_s text-center">
                    <div class="panel-body panel-table-full">
                        <div id="qr-reader"></div>
                        <br>
                        <div id="qr-reader-results"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function () {
        "use strict";

        const qrResult = document.getElementById('qr-reader-results');

        function onScanSuccess(decodedText, decodedResult) {
            qrResult.innerHTML = `<div>Scanned QR Code: <br>${decodedText}</div>`;
        }

        function onScanError(errorMessage) {
            console.warn(`QR Code scan error: ${errorMessage}`);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader",
            {fps: 10, qrbox: {width: 250, height: 250}},
            /* verbose= */ false);
        html5QrcodeScanner.render(onScanSuccess, onScanError);
    });
</script>