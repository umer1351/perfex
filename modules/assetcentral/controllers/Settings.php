<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('assetcentral_model');
        hooks()->do_action('assetcentral_init');
    }

    public function index()
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        $data = [];
        $data['group'] = $this->input->get('group');

        $data['tab'][] = 'asset_general';
        $data['tab'][] = 'asset_categories';
        $data['tab'][] = 'asset_locations';
//        $data['tab'][] = 'asset_request_types';

        $data['tab_2'] = $this->input->get('tab');
        if ($data['group'] == '') {
            $data['group'] = 'asset_general';
        }

        $data['table_to_load'] = str_replace('_', '-', $data['group']);
        if ($this->input->is_ajax_request()) {

            if ($data['group'] == '') {
                $table_path = 'settings/tables/asset_categories';
            } else {
                $table_path = 'settings/tables/' . $data['group'];
            }

            $this->app->get_table_data(module_views_path('assetcentral', $table_path));
        }

        $data['title'] = _l($data['group']);

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['tabs']['view'] = 'settings/' . $data['group'];

        $this->load->view('settings/main', $data);
    }

    public function asset_categories()
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        if ($this->input->post()) {

            $data = $this->input->post();

            $message = '';
            if ($data['id'] == '') {

                $success = $this->assetcentral_model->create_asset_category($data);

                if ($success) {
                    $message = _l('added_successfully', _l('assetcentral_category_id'));
                } else {
                    $message = _l('add_failure');
                }

            } else {

                $id = $data['id'];
                unset($data['id']);

                $success = $this->assetcentral_model->update_asset_category($id, $data);

                if ($success) {
                    $message = _l('updated_successfully', _l('assetcentral_category_id'));
                } else {
                    $message = _l('updated_fail');
                }

            }

            echo json_encode(['success' => $success, 'message' => $message]);
            die();
        }
    }

    public function delete_asset_category($id)
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        $response = $this->assetcentral_model->delete_asset_category($id);

        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('assetcentral_category_id')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('assetcentral_category_id')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('assetcentral_category_id')));
        }

        redirect(admin_url('assetcentral/settings?group=asset_categories'));
    }

    public function get_data_asset_category($id)
    {
        echo json_encode($this->assetcentral_model->get_asset_category($id));
    }

    public function asset_location()
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        if ($this->input->post()) {

            $data = $this->input->post();

            $message = '';
            if ($data['id'] == '') {

                $success = $this->assetcentral_model->create_asset_location($data);

                if ($success) {
                    $message = _l('added_successfully', _l('assetcentral_location_id'));
                } else {
                    $message = _l('add_failure');
                }

            } else {

                $id = $data['id'];
                unset($data['id']);

                $success = $this->assetcentral_model->update_asset_location($id, $data);

                if ($success) {
                    $message = _l('updated_successfully', _l('assetcentral_location_id'));
                } else {
                    $message = _l('updated_fail');
                }

            }

            echo json_encode(['success' => $success, 'message' => $message]);
            die();
        }
    }

    public function delete_asset_location($id)
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        $response = $this->assetcentral_model->delete_asset_location($id);

        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('assetcentral_location_id')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('assetcentral_location_id')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('assetcentral_location_id')));
        }

        redirect(admin_url('assetcentral/settings?group=asset_locations'));
    }

    public function get_data_asset_location($id)
    {
        echo json_encode($this->assetcentral_model->get_asset_location($id));
    }

    public function asset_request_type()
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        if ($this->input->post()) {

            $data = $this->input->post();

            $message = '';
            if ($data['id'] == '') {

                $success = $this->assetcentral_model->create_asset_request_type($data);

                if ($success) {
                    $message = _l('added_successfully', _l('assetcentral_location_id'));
                } else {
                    $message = _l('add_failure');
                }

            } else {

                $id = $data['id'];
                unset($data['id']);

                $success = $this->assetcentral_model->update_asset_request_type($id, $data);

                if ($success) {
                    $message = _l('updated_successfully', _l('assetcentral_location_id'));
                } else {
                    $message = _l('updated_fail');
                }

            }

            echo json_encode(['success' => $success, 'message' => $message]);
            die();
        }
    }

    public function delete_asset_request_type($id)
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        $response = $this->assetcentral_model->delete_asset_request_type($id);

        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('assetcentral_location_id')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('assetcentral_location_id')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('assetcentral_location_id')));
        }

        redirect(admin_url('assetcentral/settings?group=asset_request_types'));
    }

    public function get_data_asset_request_type($id)
    {
        echo json_encode($this->assetcentral_model->get_asset_request_type($id));
    }

    public function update_asset_request_type_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->assetcentral_model->update_asset_request_status($id, $status);
        }
    }

    public function save_settings()
    {
        if (!is_admin()) {
            access_denied('projectspot');
        }

        if ($this->input->post()) {
            if (!is_admin()) {
                access_denied('settings');
            }
            $this->load->model('payment_modes_model');
            $this->load->model('settings_model');

            $logo_uploaded = (handle_company_logo_upload() ? true : false);
            $favicon_uploaded = (handle_favicon_upload() ? true : false);
            $signatureUploaded = (handle_company_signature_upload() ? true : false);

            $post_data = $this->input->post();
            $tmpData = $this->input->post(null, false);

            if (isset($post_data['settings']['email_header'])) {
                $post_data['settings']['email_header'] = $tmpData['settings']['email_header'];
            }

            if (isset($post_data['settings']['email_footer'])) {
                $post_data['settings']['email_footer'] = $tmpData['settings']['email_footer'];
            }

            if (isset($post_data['settings']['email_signature'])) {
                $post_data['settings']['email_signature'] = $tmpData['settings']['email_signature'];
            }

            if (isset($post_data['settings']['smtp_password'])) {
                $post_data['settings']['smtp_password'] = $tmpData['settings']['smtp_password'];
            }

            $success = $this->settings_model->update($post_data);

            if ($success > 0) {
                set_alert('success', _l('settings_updated'));
            }

            if ($logo_uploaded || $favicon_uploaded) {
                set_debug_alert(_l('logo_favicon_changed_notice'));
            }

            redirect(admin_url('assetcentral/settings?group=asset_general'), 'refresh');
        }
    }

}