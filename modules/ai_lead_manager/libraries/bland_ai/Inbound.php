<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

trait Inbound
{
    /**
     * Retrieves the current configuration for a given inbound phone number.
     *
     * @param string $number The phone number to retrieve the configuration for.
     * @return array The API response containing the configuration, or an error message if the request failed.
     */
    public function get_inbound_number_details($number) {
        return $this->send_request('GET', "/v1/inbound/" . urlencode($number));
    }

    /**
     * Update inbound agent details for a specific phone number.
     *
     * @param string $phoneNumber The inbound phone number to update.
     * @param array $updateDetails An associative array containing the details to update.
     * @param string|null $encryptedKey The encrypted key for the Twilio account, if applicable.
     * @return array The API response indicating success or failure.
     */
    public function update_inbound_details($phoneNumber, array $updateDetails)
    {
        return $this->send_request('POST', "/v1/inbound/" . urlencode($phoneNumber), $updateDetails, ['encrypted_key: ' . get_option('bland_ai_encrypted_key')]);
    }
}
