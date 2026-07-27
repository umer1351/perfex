$(function () {
    $('.ai-lead-manager').on('click', function (e) {
        e.preventDefault();
    });

    initDataTable('.table-alm_call_logs', admin_url + '/ai_lead_manager/call_logs/table', [], [], [], [0, 'desc']);

    $('#vapi_ai_is_custom_voice_id').on('change', function () {
        console.log($(this).is(':checked'));
        if ($(this).is(':checked')) {
            $('#vapi_custom_id_input').show();
            $('#vapi_agent_voice_select').hide();
        } else {
            $('#vapi_custom_id_input').hide();
            $('#vapi_agent_voice_select').show();
        }
    });

    $('select[name="settings[vapi_ai_voice_provider]"]').on('change', function () {
        let provider_id = $('select[name="settings[vapi_ai_voice_provider]"]').val();
        requestGet('ai_lead_manager/get_vapi_voices/' + provider_id).done(function (response) {
            response = JSON.parse(response);
            const $select = $('select[name="settings[vapi_ai_agent_voice]"]');
            $select.empty();

            console.log(response);
            response.forEach(option => {
                if (option.previewUrl) {
                    $select.append(`<option value="${option.providerId}" data-subtext="${option.gender}" data-preview-url="${option.previewUrl}">${option.name.charAt(0).toUpperCase() + option.name.slice(1)}</option>`);
                } else {
                    $select.append(`<option value="${option.providerId}" data-subtext="${option.provider}">${option.name.charAt(0).toUpperCase() + option.name.slice(1)}</option>`);
                }
            });

            $select.selectpicker('refresh');

        }).fail(function (data) {
            $('#general_modal').modal('hide');
            alert_float('danger', data.responseText);
        });
    });

    $("#vapi_ai_temperature").on("input", function () {
        $("input[name='settings[vapi_ai_temperature]']").val(parseFloat($(this).val()).toFixed(1));
    });

    $("#bland_ai_temperature").on("input", function () {
        $("input[name='settings[bland_ai_temperature]']").val(parseFloat($(this).val()).toFixed(1));
    });
});

/**
 * Function to initialize the call log modal.
 * @param {Number} call_log_id ID of the call log to load
 */
function init_alm_call_log_modal(call_log_id) {
    var $callModal = $('#alm_call_log_modal_view');
    if ($callModal.is(':visible')) {
        $callModal.modal('hide');
    }
    requestGet('ai_lead_manager/call_logs/get_call_data/' + call_log_id).done(function (response) {
        var t = $("#alm_call_log_div").html(response);
        setTimeout(function () {
            t.find('#alm_call_log_modal_view').modal({ show: true, backdrop: 'static' });
        }, 150);

    }).fail(function (data) {
        $('#general_modal').modal('hide');
        alert_float('danger', data.responseText);
    });
}

/**
 * Play the preview audio for the selected voice for Bland AI.
 *
 * The selected voice ID is obtained from the select field.
 * The preview text is "Hey there, this is Blandie. How are you doing today?"
 * The response type is "stream".
 *
 * @return {boolean} false
 */
function play_selected_voice_bland() {
    const voiceId = $('select[name="settings[bland_ai_agent_voice]"]').val();
    const options = {
        method: 'POST',
        headers: {
            authorization: $('input[name="settings[bland_ai_api_key]"]').val(),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            text: "Hey there, this is Blandie. How are you doing today?",
            response_type: "stream"
        })
    };

    fetch(`https://api.bland.ai/v1/voices/${voiceId}/sample`, options)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
            }
            return response.blob(); // Handle binary response as a Blob
        })
        .then(blob => {
            const audioUrl = URL.createObjectURL(blob); // Create a URL for the audio
            const audio = new Audio(audioUrl); // Create a new audio element
            audio.play() // Play the audio
                .then(() => console.log('Audio is playing...'))
                .catch(err => console.error('Error playing audio:', err));
        })
        .catch(err => console.error('Fetch error:', err));
    return false;
}

/**
 * Play the preview audio for the selected voice for Vapi AI.
 *
 * The preview URL is obtained from the selected option in the select field.
 * The audio is played using the Audio() constructor and the play() method.
 *
 * @return {boolean} false
 */
function play_selected_voice_vapi() {
    const selectedOption = $('select[name="settings[vapi_ai_agent_voice]"]').find('option:selected');
    const previewUrl = selectedOption.data('preview-url');
    console.log(previewUrl);
    // const audioUrl = URL.createObjectURL(blob); // Create a URL for the audio
    const audio = new Audio(previewUrl); // Create a new audio element
    audio.play() // Play the audio
        .then(() => console.log('Audio is playing...'))
        .catch(err => console.error('Error playing audio:', err));

    return false;
}

/**
 * Copies the given text to the user's clipboard.
 *
 * Uses the Clipboard API (navigator.clipboard) if supported, otherwise logs an error.
 *
 * @param {string} text - The text to copy to the clipboard.
 */
alm_copy_to_clipboard = function (text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function () {
            alert_float("success", "Copied to clipboard");
            console.log('Text successfully copied to clipboard!');
        }).catch(function (err) {
            console.error('Failed to copy text: ', err);
        });
    } else {
        console.error('Clipboard API not supported!');
    }
};