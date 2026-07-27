<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Track extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('deals/deals_automation_model', 'deals_automation_model', true);
    }

    public function open($token = null)
    {
        if (empty($token)) {
            show_404();
        }

        $this->deals_automation_model->track_open($token);
        $this->output
            ->set_content_type('image/gif')
            ->set_output(base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='));
    }

    public function click($token = null)
    {
        if (empty($token)) {
            show_404();
        }

        $encodedUrl = $this->input->get('u', true);
        $destination = $this->deals_automation_model->track_click($token, $encodedUrl);
        redirect($destination ?: site_url());
    }

    public function unsubscribe($token = null)
    {
        if (empty($token)) {
            show_404();
        }

        $email = $this->deals_automation_model->unsubscribe_by_token($token);
        if (!$email) {
            show_404();
        }

        $this->output
            ->set_content_type('text/html')
            ->set_output('<!doctype html><html><head><meta charset="utf-8"><title>Unsubscribed</title></head><body style="font-family:Arial,sans-serif;padding:40px;color:#111;"><h2>Unsubscribed</h2><p>' . html_escape($email) . ' has been unsubscribed from deal campaign emails.</p></body></html>');
    }
}
