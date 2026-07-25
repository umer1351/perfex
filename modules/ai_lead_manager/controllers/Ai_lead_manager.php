<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ai_lead_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Bland_ai');
        $this->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Vapi_ai');
    }

    /**
     * Retrieves and outputs a list of Bland AI voices in JSON format.
     *
     * This function loads the Bland AI library and fetches the available voices.
     * The voices are then output as a JSON-encoded array.
     */
    public function get_bland_voices()
    {
        $voices = $this->bland_ai->get_voices();
        echo json_encode($voices);
    }

    /**
     * Returns a list of Vapi voices for the given provider.
     *
     * @param string $provider_id The ID of the voice provider.
     */
    public function get_vapi_voices($provider_id)
    {

        $voices = $this->vapi_ai->get_voices($provider_id);
        echo json_encode($voices);
    }

    public function add_knowledgebase($provider = 'bland')
    {
        if (!isset($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
            set_alert('warning', 'No file selected');
            redirect(admin_url('settings?group=ai_lead_manager'));
            return;
        }

        $file_path = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];

        if ($provider == 'vapi') {
            $response = $this->vapi_ai->upload_file($file_path, $file_name);
        } else if ($provider == 'bland') {
            $response = $this->bland_ai->upload_knowledgebase($file_path, $file_name, $this->input->post('name'), $this->input->post('description'));
        }

        set_alert('success', 'Knowledgebase uploaded successfully');
        redirect(admin_url('settings?group=ai_lead_manager'));
    }

    /**
     * Deletes a knowledgebase with the given ID for the given provider.
     *
     * @param string $provider The provider of the knowledgebase. Either 'bland_ai' or 'vapi_ai'.
     * @param int $id The ID of the knowledgebase.
     * @return void
     */
    public function delete_knowledgebase(string $provider, int $id): void
    {
        $this->{$provider}->delete_knowledgebase($id);
        set_alert('success', 'Knowledgebase deleted successfully');
        redirect(admin_url('settings?group=ai_lead_manager'));
    }
}
