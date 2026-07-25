<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

trait Accounts
{
    protected $bland_ai_encrypted_key;
    public function __construct()
    {
        $this->bland_ai_encrypted_key = get_option('bland_ai_encrypted_key');
    }

    /**
     * Sets the encrypted key to be used for interacting with the Bland AI API.
     * 
     * @param string $key The encrypted key to use for API requests.
     */
    public function set_encrypted_key($key)
    {
        $this->bland_ai_encrypted_key = $key;
    }

    /**
     * Get the encrypted key to be used for interacting with the Bland AI API.
     * 
     * @return string The encrypted key.
     */
    public function get_encrypted_key()
    {
        return $this->bland_ai_encrypted_key;
    }

    /**
     * Create an encrypted key for integrating Twilio with Bland AI.
     *
     * @param string $account_sid Twilio account SID.
     * @param string $auth_token Twilio authentication token.
     * @return array Response from the Bland AI API containing the encrypted key or an error.
     */
    public function create_encrypted_key($account_sid, $auth_token)
    {
        $data = [
            'account_sid' => $account_sid,
            'auth_token'  => $auth_token,
        ];

        return $this->send_request('POST', '/v1/accounts', $data);
    }

    /**
     * Delete an encrypted key used for integrating Twilio with Bland AI.
     *
     * @param string $encrypted_key The encrypted key to delete.
     * @return array Response from the Bland AI API containing the result of deletion or an error.
     */
    public function delete_encrypted_key()
    {
        return $this->send_request('POST', '/v1/accounts/delete', [], ['encrypted_key: ' . $this->bland_ai_encrypted_key]);
    }

    /**
     * Upload a list of inbound numbers using a provided encrypted key.
     *
     * @param string $encrypted_key The encrypted key for authentication.
     * @param array $numbers The list of inbound numbers to be uploaded.
     * @return array The response from the Bland AI API after uploading the numbers.
     */
    public function upload_inbound_numbers($numbers)
    {
        $data = [
            'numbers' => $numbers
        ];

        return $this->send_request('POST', '/v1/inbound/insert', $data, ['encrypted_key: ' . $this->bland_ai_encrypted_key]);
    }


    /**
     * Delete an inbound number using a provided encrypted key.
     *
     * @param string $encrypted_key The encrypted key for authentication.
     * @param string $number The inbound number to be deleted.
     * @return array The response from the Bland AI API after deleting the number.
     */
    public function delete_inbound_number($number)
    {
        return $this->send_request('POST', "/v1/inbound/{$number}/delete", [], ['encrypted_key: ' . $this->bland_ai_encrypted_key]);
    }
}
