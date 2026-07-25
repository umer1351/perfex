<?php

defined('BASEPATH') or exit('No direct script access allowed');

use chillerlan\QRCode\QRCode;

class Assetcentral extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('assetcentral_model');
        hooks()->do_action('assetcentral_init');
    }

    public function index()
    {
        show_404();
    }

    public function manage_assets()
    {
        if (!has_permission('assetcentral', '', 'view')) {
            access_denied('assetcentral');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'assets/table'), ['postData' => $_POST]);
        }

        $data['title'] = _l('assetcentral');

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $this->load->model('clients_model');
        $data['client_list'] = $this->clients_model->get('', [db_prefix() . 'clients.active' => 1]);

        $this->load->model('projects_model');
        $data['projects_list'] = $this->projects_model->get();

        $data['asset_categories'] = $this->assetcentral_model->get_all_asset_category();
        $data['asset_locations'] = $this->assetcentral_model->get_all_asset_location();

        $this->load->view('assets/manage', $data);
    }

    public function create_asset($id = '')
    {
        if (!has_permission('assetcentral', '', 'create')) {
            access_denied('assetcentral');
        }

        if ($this->input->post() && $id === '') {

            $response = $this->assetcentral_model->create_asset($this->input->post() + ['created_at' => date('Y-m-d H:i:s')]);

            if ($response == true) {
                assetcentral_handle_asset_main_image($response);
                set_alert('success', _l('assetcentral_created_successfully'));
            } else {
                set_alert('warning', _l('assetcentral_not_created_successfully'));
            }

            redirect(admin_url('assetcentral/manage_assets'));

        } elseif ($this->input->post() && $id !== '') {
            $response = $this->assetcentral_model->update_asset($id, $this->input->post());

            if ($response == true) {

                if (isset($_FILES['file']) && $_FILES['file']['size'] != 0) {
                    $this->assetcentral_model->delete_asset_main_image_attachment($id);

                    assetcentral_handle_asset_main_image($id);
                }

                set_alert('success', _l('assetcentral_updated_successfully'));
            } else {
                set_alert('warning', _l('assetcentral_not_updated_successfully'));
            }

            redirect(admin_url('assetcentral/create_asset/' . $id));
        }

        $data['title'] = _l('assetcentral');
        if ($id) {
            $data['asset_data'] = $this->assetcentral_model->get_asset($id);
            $data['asset_main_image'] = $this->assetcentral_model->get_asset_main_image_attachment($id);
            $data['title'] = _l('assetcentral') . ' - ' . $data['asset_data']->asset_name;
        }

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['asset_categories'] = $this->assetcentral_model->get_all_asset_category();
        $data['asset_locations'] = $this->assetcentral_model->get_all_asset_location();

        $this->load->view('assets/create', $data);
    }

    public function delete_asset($id = '')
    {
        if (!has_permission('assetcentral', '', 'delete')) {
            access_denied('assetcentral');
        }

        if (!$id) {
            redirect(admin_url('assetcentral/manage'));
        }

        $response = $this->assetcentral_model->delete_asset($id);

        if ($response == true) {
            set_alert('success', _l('deleted', _l('assetcentral')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('assetcentral')));
        }

        redirect(admin_url('assetcentral/manage_assets'));
    }

    public function view_asset($id)
    {
        if (!has_permission('assetcentral', '', 'view')) {
            access_denied('assetcentral');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'assets/history_table'), ['asset_id' => $id]);
        }

        $data['asset'] = $this->assetcentral_model->get_asset($id);
        $data['title'] = $data['asset']->asset_name;
        $data['asset_main_image'] = $this->assetcentral_model->get_asset_main_image_attachment($id);
        $data['asset_category'] = $this->assetcentral_model->get_asset_category($data['asset']->category_id);
        $data['asset_location'] = $this->assetcentral_model->get_asset_location($data['asset']->location_id);
        $data['asset_events'] = $this->assetcentral_model->get_all_asset_event($data['asset']->id);
        $data['assetAssigned'] = $this->assetcentral_model->get_asset_assign($id);

        $data['depreciation_values'] = [];

        if ($data['asset']->depreciation_method === 'straight_line') {
            $data['depreciation_values'] = assetcentral_calculate_straight_line_depreciation_chart($data['asset']);
            $data['annual_data'] = assetcentral_calculate_straight_line_depreciation_annual($data['asset']);
        } else {
            $data['depreciation_values'] = assetcentral_calculate_reducing_balance_depreciation_chart($data['asset']);
            $data['annual_data'] = assetcentral_calculate_reducing_balance_depreciation_annual($data['asset']);
        }

        $data['appreciation_data'] = assetcentral_calculate_appreciation_annual($data['asset']);
        $data['appreciation_data_table'] = assetcentral_calculate_appreciation_data($data['asset']);

        $purchaseDate = (new DateTime($data['asset']->purchase_date))->format('Y-m-d');
        if ($purchaseDate < 0) {
            $purchaseDate = '';
        }
        $data['generatedQr'] = (new QRCode)->render('
        Asset ID: ' . $data['asset']->id . '
        Asset Name: ' . $data['asset']->asset_name . '
        Serial Number: ' . $data['asset']->serial_number . '
        Model Number: ' . $data['asset']->model_number . '
        Purchase Date: ' . $purchaseDate . '
        Managed By: ' . get_staff_full_name($data['asset']->asset_manager) . '
        Warranty: ' . $data['asset']->warranty_period_month . ' months
        Supplier:  ' . $data['asset']->supplier_name . '
        Asset URL: ' . admin_url("assetcentral/view_asset/" . $data['asset']->id) . '
        ');

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['attachments'] = get_all_asset_attachments($data['asset']->id);

        $this->load->view('assets/view_asset', $data);
    }

    public function load_asset_single_audits($asset_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'audits/table'), ['assetSingleId' => $asset_id]);
        }
    }

    public function import_assets()
    {
        if (!is_admin()) {
            access_denied('assetcentral');
        }

        $assetImporter = new Import_assets();

        $assetImporter->setDatabaseFields($this->db->list_fields(db_prefix() . 'assetcentral_assets'))
            ->setCustomFields(get_custom_fields('assetcentral_as'));

        if ($this->input->post('download_sample') === 'true') {
            $assetImporter->downloadSample();
        }

        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $assetImporter->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $assetImporter->totalRows();

            if (!$assetImporter->isSimulation()) {
                set_alert('success', _l('import_total_imported', $assetImporter->totalImported()));
            }
        }

        $data['title'] = _l('assetcentral_import_assets_menu');
        $data['importInstance'] = $assetImporter;
        $this->load->view('assets/import_assets', $data);
    }

    public function upload_attachment($id)
    {
        handle_asset_attachments_upload($id);
    }

    public function asset_status()
    {
        if (!has_permission('assetcentral', '', 'edit')) {
            access_denied('assetcentral');
        }

        $postData = $this->input->post();
        $edit_status = $postData['id'] ?? '';

        $modalFormInputs = '';

        if ($postData['status'] == 'allocate') {

            $this->load->model('staff_model');
            $this->load->model('clients_model');
            $this->load->model('projects_model');

            $staffMembers = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
            $clients = $this->clients_model->get('', [db_prefix() . 'clients.active' => 1]);
            $projects = $this->projects_model->get();

            $allocateToValue = '';
            $staffValue = '';
            $projectsValue = '';
            $customersValue = '';
            $allocationNoteValue = '';
            $hideCheckbox = '';
            $hideSignaturePad = '';
            $signatureValue = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $allocateToValue = $eventDataForm->allocate_to;
                $staffValue = $eventDataForm->staff;
                $projectsValue = $eventDataForm->projects;
                $customersValue = $eventDataForm->customers;
                $allocationNoteValue = $eventDataForm->allocation_note ?? '';
                $signatureValue = $eventDataForm->signature;

                $hideCheckbox = 'hide';
                $hideSignaturePad = 'hide';

            }

            $modalFormInputs .= '
                                      ' . render_select("allocate_to", assetcentral_allocate_to_options(), ["value", "name"], "assetcentral_allocate_to", $allocateToValue) . '
                                      ' . render_select("staff", $staffMembers, ["staffid", ["firstname", "lastname"]], "assetcentral_allocate_to_staff_label", $staffValue) . '
                                      ' . render_select("projects", $projects, ["id", "name"], "assetcentral_allocate_to_project_label", $projectsValue) . '
                                      ' . render_select("customers", $clients, ["userid", "company"], "assetcentral_allocate_to_customer_label", $customersValue) . '
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("allocation_note", "", $allocationNoteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        <div class="row ' . $hideCheckbox . '">
                                            <div class="col-md-12">
                                                <div class="checkbox checkbox-primary">
                                                    <input type="checkbox" name="notify" id="notify">
                                                    <label for="notify">
                                                        ' . _l("assetcentral_notify_checkout") . '
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
         
                <script>
                init_selectpicker();
                init_datepicker();
                
                $(`[app-field-wrapper="staff"]`).hide();
                $(`[app-field-wrapper="projects"]`).hide();
                $(`[app-field-wrapper="customers"]`).hide();

                $(document).ready(function () {
                    let initialValue = $(`select[name="allocate_to"]`).val();
                    showRelevantField(initialValue);
                    ' . assetcentral_signature_pad_js() . '
                })

                $(`select[name="allocate_to"]`).on("change", function () {
                    let selectedValue = $(`select[name="allocate_to"]`).val();
                    showRelevantField(selectedValue);
                });

                function showRelevantField(value) {
                    if (value == "staff") {
                        $(`[app-field-wrapper="staff"]`).show();
                        $(`[app-field-wrapper="projects"]`).hide();
                        $(`[app-field-wrapper="customers"]`).hide();
                        $(`select[name="projects"]`).val("").change();
                        $(`select[name="customers"]`).val("").change();
                    } else if (value == "project") {
                        $(`[app-field-wrapper="projects"]`).show();
                        $(`[app-field-wrapper="staff"]`).hide();
                        $(`[app-field-wrapper="customers"]`).hide();
                        $(`select[name="staff"]`).val("").change();
                        $(`select[name="customers"]`).val("").change();
                    } else if (value == "customer") {
                        $(`[app-field-wrapper="customers"]`).show();
                        $(`[app-field-wrapper="staff"]`).hide();
                        $(`[app-field-wrapper="projects"]`).hide();
                        $(`select[name="staff"]`).val("").change();
                        $(`select[name="projects"]`).val("").change();
                    } else {
                        $(`[app-field-wrapper="staff"]`).hide();
                        $(`[app-field-wrapper="projects"]`).hide();
                        $(`[app-field-wrapper="customers"]`).hide();
                    }
                }
            </script>
                ';

        }

        if ($postData['status'] == 'lost') {

            $dateValue = '';
            $noteValue = '';
            $hideSignaturePad = '';
            $signatureValue = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);

                $eventDataForm = json_decode($eventData->event_form);

                $dateValue = $eventDataForm->date_lost;
                $noteValue = $eventDataForm->note;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';
            }

            $modalFormInputs .= '
                                      ' . render_date_input("date_lost", "assetcentral_asset_date_lost_label", $dateValue) . '
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("note", "", $noteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" name="signature" value="' . $signatureValue . '" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        if ($postData['status'] == 'found') {

            $dateValue = '';
            $noteValue = '';
            $hideSignaturePad = '';
            $signatureValue = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $dateValue = $eventDataForm->date_found;
                $noteValue = $eventDataForm->note;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';
            }

            $modalFormInputs .= '
                                      ' . render_date_input("date_found", "assetcentral_asset_date_found_label", $dateValue) . '
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("note", "", $noteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        if ($postData['status'] == 'broken') {

            $dateValue = '';
            $noteValue = '';
            $signatureValue = '';
            $hideSignaturePad = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $dateValue = $eventDataForm->date_broken;
                $noteValue = $eventDataForm->note;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';
            }

            $modalFormInputs .= '
                                      ' . render_date_input("date_broken", "assetcentral_asset_date_broken_label", $dateValue) . '
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("note", "", $noteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        if ($postData['status'] == 'dispose') {

            $dateValue = '';
            $disposeToValue = '';
            $noteValue = '';
            $signatureValue = '';
            $hideSignaturePad = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $dateValue = $eventDataForm->date_disposed;
                $disposeToValue = $eventDataForm->dispose_to;
                $noteValue = $eventDataForm->note;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';
            }

            $modalFormInputs .= '
                                      ' . render_date_input("date_disposed", "assetcentral_asset_date_disposed_label", $dateValue) . '
                                      ' . render_input("dispose_to", "assetcentral_asset_date_dispose_to_label", $disposeToValue) . '
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("note", "", $noteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        if ($postData['status'] == 'donate') {

            $dateValue = '';
            $donateToValue = '';
            $donateValue = '';
            $noteValue = '';
            $signatureValue = '';
            $hideSignaturePad = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $dateValue = $eventDataForm->date_donated;
                $donateToValue = $eventDataForm->donate_to;
                $donateValue = $eventDataForm->donate_value;
                $noteValue = $eventDataForm->note;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';
            }

            $modalFormInputs .= '
                                      ' . render_date_input("date_donated", "assetcentral_asset_date_donate_label", $dateValue) . '
                                      ' . render_input("donate_to", "assetcentral_asset_date_donate_to_label", $donateToValue) . '
                                      <div class="row">
                                      <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="donate_value">' . _l("assetcentral_asset_date_donate_value_label") . '</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="donate_value"
                                                           value="' . $donateValue . '">
                                                    <div class="input-group-addon">
                                                        ' . get_base_currency()->symbol . '
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("note", "", $noteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        if ($postData['status'] == 'sell') {

            $dateValue = '';
            $soldToValue = '';
            $saleAmountValue = '';
            $noteValue = '';
            $signatureValue = '';
            $hideSignaturePad = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $dateValue = $eventDataForm->date_sold;
                $soldToValue = $eventDataForm->sold_to;
                $saleAmountValue = $eventDataForm->sale_amount;
                $noteValue = $eventDataForm->note;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';
            }

            $modalFormInputs .= '
                                      ' . render_date_input("date_sold", "assetcentral_asset_date_sold_label", $dateValue) . '
                                      ' . render_input("sold_to", "assetcentral_asset_date_sold_to_label", $soldToValue) . '
                                      <div class="row">
                                      <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="donate_value">' . _l("assetcentral_asset_date_sale_value_label") . '</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="sale_amount"
                                                           value="' . $saleAmountValue . '">
                                                    <div class="input-group-addon">
                                                        ' . get_base_currency()->symbol . '
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("note", "", $noteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        if ($postData['status'] == 'repair') {

            $dateValue = '';
            $assignedToValue = '';
            $dateCompletedValue = '';
            $repairCostValue = '';
            $noteValue = '';
            $signatureValue = '';
            $hideSignaturePad = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $dateValue = $eventDataForm->date_scheduled;
                $assignedToValue = $eventDataForm->assigned_to;
                $dateCompletedValue = $eventDataForm->date_completed;
                $repairCostValue = $eventDataForm->repair_cost;
                $noteValue = $eventDataForm->note;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';
            }

            $modalFormInputs .= '
                                      ' . render_date_input("date_scheduled", "assetcentral_asset_date_schedule_label", $dateValue) . '
                                      ' . render_input("assigned_to", "assetcentral_asset_date_assigned_to_value_label", $assignedToValue) . '
                                      ' . render_date_input("date_completed", "assetcentral_asset_date_date_completed_value_label", $dateCompletedValue) . '
                                      <div class="row">
                                      <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="donate_value">' . _l("assetcentral_asset_date_repair_cost_value_label") . '</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="repair_cost"
                                                           value="' . $repairCostValue . '">
                                                    <div class="input-group-addon">
                                                        ' . get_base_currency()->symbol . '
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_allocate_to_note") . '</p>
                                                    ' . render_textarea("note", "", $noteValue) . '
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        if ($postData['status'] == 'maintenance') {

            $maintenanceTitleValue = '';
            $maintenanceDetailsValue = '';
            $maintenanceDueDateValue = '';
            $maintenanceByValue = '';
            $maintenanceStatusValue = '';
            $maintenanceDateCompletedValue = '';
            $maintenanceCostValue = '';
            $signatureValue = '';
            $hideSignaturePad = '';

            if ($edit_status) {
                $eventData = $this->assetcentral_model->get_asset_event($edit_status);
                $eventDataForm = json_decode($eventData->event_form);

                $maintenanceTitleValue = $eventDataForm->maintenance_title;
                $maintenanceDetailsValue = $eventDataForm->maintenance_details;
                $maintenanceDueDateValue = $eventDataForm->maintenance_due_date;
                $maintenanceByValue = $eventDataForm->maintenance_by;
                $maintenanceStatusValue = $eventDataForm->maintenance_status;
                $maintenanceDateCompletedValue = $eventDataForm->maintenance_date_completed;
                $maintenanceCostValue = $eventDataForm->maintenance_cost;
                $signatureValue = $eventDataForm->signature;
                $hideSignaturePad = 'hide';

            }

            $modalFormInputs .= '
                                      ' . render_input("maintenance_title", "assetcentral_asset_maintenance_title_label", $maintenanceTitleValue) . '
                                      <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_asset_maintenance_details_label") . '</p>
                                                    ' . render_textarea("maintenance_details", "", $maintenanceDetailsValue) . '
                                            </div>
                                        </div>
                                      ' . render_date_input("maintenance_due_date", "assetcentral_asset_maintenance_due_date_label", $maintenanceDueDateValue) . '
                                      <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold">' . _l("assetcentral_asset_maintenance_by_label") . '</p>
                                                    ' . render_textarea("maintenance_by", "", $maintenanceByValue) . '
                                            </div>
                                        </div>
                                        ' . render_select("maintenance_status", assetcentral_maintenance_statuses(), ["value", "name"], "assetcentral_asset_maintenance_status_label", $maintenanceStatusValue) . '
                                      ' . render_date_input("maintenance_date_completed", "assetcentral_asset_maintenance_date_completed_label", $maintenanceDateCompletedValue) . '
                                      <div class="row">
                                      <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="donate_value">' . _l("assetcentral_asset_maintenance_cost_label") . '</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="maintenance_cost"
                                                           value="' . $maintenanceCostValue . '">
                                                    <div class="input-group-addon">
                                                        ' . get_base_currency()->symbol . '
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="div ' . $hideSignaturePad . '">
                                            <p class="bold" id="signatureLabel">Signature</p>
                                                <div class="signature-pad--body" style="border-radius: 4px;position: relative;-webkit-box-flex: 1;-ms-flex: 1;flex: 1;border: 1px solid #c0cbda;">
                                                  <canvas id="signature" height="130" width="550"></canvas>
                                                </div>
                                            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" value="' . $signatureValue . '" name="signature" id="signatureInput">
                                            <div class="dispay-block">
                                              <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear">Clear</button>
                                              <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo">Undo</button>
                                            </div>
                                        </div>
                                        
                <script>
                init_selectpicker();
                init_datepicker();
                $(document).ready(function () {
                    ' . assetcentral_signature_pad_js() . '
                })
                </script>
                ';
        }

        echo json_encode(['form' => $modalFormInputs]);
        die;

    }

    public function save_asset_status()
    {
        if (!has_permission('assetcentral', '', 'edit')) {
            access_denied('assetcentral');
        }

        $postData = $this->input->post();
        $postId = $postData['id'];

        if ($postData['status'] == 'allocate') {

            $sendEmail = false;
            if (isset($postData['notify'])) {
                $sendEmail = true;
            }

            $rel_id = 0;
            $related_to = '';
            $requiredInput = 'customers';
            $whoReceivesNotifications = [];
            $notificationUrl = '';
            $emailUrl = '';

            if ($postData['allocate_to'] == 'customer') {
                $rel_id = $postData['customers'];
                $related_to = 'customers';
                $requiredInput = 'customers';
                $emailUrl = admin_url('clients/client/' . $rel_id);
                $notificationUrl = 'clients/client/' . $rel_id;

                $this->load->model('clients_model');
                $customerContacts = $this->clients_model->get_contacts($rel_id);
                foreach ($customerContacts as $contact) {
                    $whoReceivesNotifications[] = ['id' => $contact['id'], 'email' => $contact['email']];
                }
            }
            if ($postData['allocate_to'] == 'staff') {
                $rel_id = $postData['staff'];
                $related_to = 'staff';
                $requiredInput = 'staff';
                $emailUrl = admin_url('assetcentral/manage_my_assets');
                $notificationUrl = 'assetcentral/manage_my_assets';

                $whoReceivesNotifications[] = ['id' => get_staff($rel_id)->staffid, 'email' => get_staff($rel_id)->email];
            }
            if ($postData['allocate_to'] == 'project') {
                $rel_id = $postData['projects'];
                $related_to = 'project';
                $requiredInput = 'projects';
                $emailUrl = admin_url('projects/view/' . $rel_id);
                $notificationUrl = 'projects/view/' . $rel_id;

                $this->load->model('projects_model');
                $projectMembers = $this->projects_model->get_project_members($rel_id);
                foreach ($projectMembers as $staff) {
                    $whoReceivesNotifications[] = ['id' => $staff['staff_id'], $staff['email']];
                }
            }

            if (empty($postData['allocate_to'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }
            if (empty($postData[$requiredInput])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);
            $this->assetcentral_model->create_asset_assign([
                'asset_id' => $postData['asset_id'],
                'assigned_to' => $related_to,
                'assigned_id' => $rel_id,
                'assign_date' => date('Y-m-d H:i:s')
            ]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);

            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if ($sendEmail) {

                foreach ($whoReceivesNotifications as $receiver) {
                    $notified = add_notification([
                        'description' => 'assetcentral_asset_allocated_notification',
                        'touserid' => $receiver['id'],
                        'fromcompany' => 1,
                        'fromuserid' => 0,
                        'link' => $notificationUrl,
                        'additional_data' => serialize([
                            $assetData->asset_name,
                            get_staff_full_name(get_staff_user_id())
                        ]),
                    ]);

                    if ($notified) {
                        pusher_trigger_notification([$receiver['id']]);
                    }

                    $assetName = $assetData->asset_name ?? "";

                    $this->load->model('emails_model');
                    $this->emails_model->send_simple_email(
                        $receiver['email'],
                        'New Asset Allocated To You -' . get_option('companyname'),
                        '
            Hello,
<br>
Asset with title <strong>' . $assetName . '</strong> has been allocated to you by : <strong>' . get_staff_full_name(get_staff_user_id()) . '</strong>
<br>
<a href="' . $emailUrl . '">More Details</a>
<br>
Best regards,<br>
' . get_option('companyname') . '
            '
                    );

                }
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_allocate_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_allocate_successfully_update')]);
            die;

        }

        if ($postData['status'] == 'lost') {

            if (empty($postData['date_lost'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_lost_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_lost_successfully_update')]);
            die;

        }

        if ($postData['status'] == 'found') {

            if (empty($postData['date_found'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_found_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_found_successfully_update')]);
            die;

        }

        if ($postData['status'] == 'broken') {

            if (empty($postData['date_broken'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_broken_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_broken_successfully_update')]);
            die;
        }

        if ($postData['status'] == 'dispose') {

            if (empty($postData['date_disposed'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_disposed_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_disposed_successfully_update')]);
            die;
        }

        if ($postData['status'] == 'donate') {

            if (empty($postData['date_donated'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_donated_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_donated_successfully_update')]);
            die;
        }

        if ($postData['status'] == 'sell') {

            if (empty($postData['date_sold'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_sold_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_sold_successfully_update')]);
            die;
        }

        if ($postData['status'] == 'repair') {

            if (empty($postData['date_scheduled'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_repair_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_repair_successfully_update')]);
            die;
        }

        if ($postData['status'] == 'maintenance') {

            if (empty($postData['maintenance_title'])) {
                echo json_encode(['status' => 0, 'message' => _l('assetcentral_required_not_filled')]);
                die;
            }

            $assetData = $this->assetcentral_model->get_asset($postData['asset_id']);
            $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

            $this->assetcentral_model->delete_asset_assign($assetData->id);

            $this->assetcentral_model->update_asset($postData['asset_id'], ['asset_status' => assetcentral_get_status_data($postData['status'])['id']]);

            if (empty($postId)) {
                $eventId = $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $postData['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $uploadPath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $eventId . '/';
                process_digital_signature_image($this->input->post('signature', false), $uploadPath);
            } else {
                $this->assetcentral_model->update_asset_event($postId, [
                    'event_form' => json_encode($postData)
                ]);
            }

            if (empty($postId)) {
                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $postData['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data($postData['status'])['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            if (empty($postId)) {
                echo json_encode(['success' => 'true', 'message' => _l('assetcentral_maintenance_successfully')]);
                die;
            }

            echo json_encode(['success' => 'true', 'message' => _l('assetcentral_maintenance_successfully_update')]);
            die;
        }

    }

    public function delete_asset_event($event_id)
    {
        if (!has_permission('assetcentral', '', 'delete')) {
            access_denied('assetcentral');
        }

        $this->assetcentral_model->delete_asset_event($event_id);

        echo json_encode(['success' => 'true', 'message' => _l('assetcentral_asset_event_deleted')]);
        die;
    }

    public function revocate_asset($asset_id)
    {
        if (!has_permission('assetcentral', '', 'edit')) {
            access_denied('assetcentral');
        }

        $assetData = $this->assetcentral_model->get_asset($asset_id);
        $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

        $this->assetcentral_model->update_asset($asset_id, ['asset_status' => 1]);
        $this->assetcentral_model->delete_asset_assign('', $asset_id);

        $this->assetcentral_model->create_asset_event([
            'event_form' => '{"id":"","asset_id":"' . $asset_id . '","status":"revocate"}',
            'asset_id' => $asset_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->assetcentral_model->create_asset_history([
            'asset_id' => $asset_id,
            'event' => ucfirst(assetcentral_get_status_data(1)['value']),
            'field' => 'Status',
            'changed_from' => ucfirst($currentAssetStatus['value']),
            'changed_to' => ucfirst(assetcentral_get_status_data(1)['value']),
            'changed_by' => get_staff_user_id(),
            'changed_at' => date('Y-m-d H:i:s')
        ]);

        set_alert('success', _l('assetcentral_asset_revocate_success'));
        redirect(admin_url('assetcentral/view_asset/' . $asset_id));
    }

    public function manage_my_assets()
    {
        if (!has_permission('assetcentral', '', 'view_own')) {
            access_denied('assetcentral');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'assets/my_assets_table'));
        }

        $data['title'] = _l('assetcentral') . ' - ' . _l('assetcentral_my_assets_menu');

        $data['asset_categories'] = $this->assetcentral_model->get_all_asset_category();
        $data['asset_locations'] = $this->assetcentral_model->get_all_asset_location();

        $this->load->view('assets/my_assets', $data);
    }

    public function manage_project_assets($project_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'assets/project_assets_table'), ['postData' => $_POST, 'project_id' => $project_id]);
        }

        $data['title'] = _l('assetcentral');
        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['asset_categories'] = $this->assetcentral_model->get_all_asset_category();
        $data['asset_locations'] = $this->assetcentral_model->get_all_asset_location();

        $data['project_id'] = $project_id;

        $this->load->view('assets/project_assets', $data);
    }

    public function manage_audits()
    {
        if (!has_permission('assetcentral_audits', '', 'view') || !has_permission('assetcentral_audits', '', 'view_own')) {
            access_denied('assetcentral');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'audits/table'), ['postData' => $_POST]);
        }

        $data['title'] = _l('assetcentral_audits');
        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['assets'] = $this->assetcentral_model->get_all_asset();

        $this->load->view('audits/manage', $data);
    }

    public function create_audit($id = '')
    {
        if (!has_permission('assetcentral_audits', '', 'create')) {
            access_denied('assetcentral_audits');
        }

        if ($this->input->post() && $id === '') {

            $postData = $this->input->post();

            $postData['assets_list'] = json_encode($postData['assets_list']);
            $response = $this->assetcentral_model->create_asset_audit($postData + ['created_at' => date('Y-m-d H:i:s')]);

            if ($response == true) {

                $notified = add_notification([
                    'description' => 'assetcentral_asset_audit_allocated_notification',
                    'touserid' => $postData['audit_by'],
                    'fromcompany' => 1,
                    'fromuserid' => 0,
                    'link' => 'assetcentral/view_audit/' . $response,
                    'additional_data' => serialize([
                        $postData['audit_name'],
                        get_staff_full_name(get_staff_user_id())
                    ]),
                ]);

                if ($notified) {
                    pusher_trigger_notification([$postData['audit_by']]);
                }

                $auditName = $postData['audit_name'] ?? "";
                $completeData = $postData['audit_date'] ?? "";
                $auditUrl = admin_url('assetcentral/view_audit/' . $response);

                $this->load->model('emails_model');
                $this->emails_model->send_simple_email(
                    get_staff($postData['audit_by'])->email,
                    'New Audit Allocated To You -' . get_option('companyname'),
                    '
            Hello,
<br>
Asset audit with title <strong>' . $auditName . '</strong> has been allocated to you by : <strong>' . get_staff_full_name(get_staff_user_id()) . '</strong>. This asset audit should be completed on ' . $completeData . '.
<br>
<a href="' . $auditUrl . '">More Details</a>
<br>
Best regards,<br>
' . get_option('companyname') . '
            '
                );

                set_alert('success', _l('assetcentral_audit_created_successfully'));
            } else {
                set_alert('warning', _l('assetcentral_audit_not_created_successfully'));
            }

            redirect(admin_url('assetcentral/manage_audits'));

        }

        $data['title'] = _l('assetcentral');
        if ($id) {
            $data['asset_data'] = $this->assetcentral_model->get_asset($id);
            $data['asset_main_image'] = $this->assetcentral_model->get_asset_main_image_attachment($id);
            $data['title'] = _l('assetcentral') . ' - ' . $data['asset_data']->asset_name;
        }

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['asset_categories'] = $this->assetcentral_model->get_all_asset_category();
        $data['asset_locations'] = $this->assetcentral_model->get_all_asset_location();
        $data['asset_statuses'] = assetcentral_asset_statuses();
        $data['asset_list'] = $this->assetcentral_model->get_all_asset();

        $this->load->view('audits/create', $data);
    }

    public function filter_audit_assets()
    {
        $categories = $this->input->post('categories');
        $locations = $this->input->post('locations');
        $statuses = $this->input->post('statuses');

        if (empty($categories) && empty($locations) && empty($statuses)) {
            echo json_encode(['status' => 'success', 'assets' => json_encode([])]);
            die;
        }

        $assets = $this->assetcentral_model->get_audit_filtered_assets($categories, $locations, $statuses);
        $assetIds = array_map(static function ($asset) {
            return $asset->id;
        }, $assets);

        echo json_encode(['status' => 'success', 'assets' => json_encode($assetIds)]);
        die;
    }

    public function delete_audit($id = '')
    {
        if (!has_permission('assetcentral_audits', '', 'delete')) {
            access_denied('assetcentral_audits');
        }

        if (!$id) {
            redirect(admin_url('assetcentral/manage_audits'));
        }

        $response = $this->assetcentral_model->delete_asset_audit($id);

        if ($response == true) {
            set_alert('success', _l('deleted', _l('assetcentral_audit_menu')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('assetcentral_audit_menu')));
        }

        redirect(admin_url('assetcentral/manage_audits'));
    }

    public function view_audit($id)
    {
        if (!has_permission('assetcentral_audits', '', 'view')) {
            access_denied('assetcentral_audits');
        }

        $data['audit'] = $this->assetcentral_model->get_asset_audit($id);

        if (!is_admin() && $data['audit']->audit_by !== get_staff_user_id()) {
            access_denied('assetcentral_audits');
        }

        if ($this->input->post() && $id !== '') {

            $postData = $this->input->post();

            if (isset($postData['audit_date'])) {
                $postData['is_finished'] = 1;
            }

            $response = $this->assetcentral_model->update_asset_audit($id, $postData);

            if ($response == true) {
                set_alert('success', _l('assetcentral_audit_updated_successfully'));
            } else {
                set_alert('warning', _l('assetcentral_audit_updated_failed'));
            }

            redirect(admin_url('assetcentral/manage_audits/'));
        }

        $data['title'] = $data['audit']->audit_name;

        $this->load->view('audits/view', $data);
    }

    public function manage_requests()
    {
        if (!has_permission('assetcentral', '', 'view')) {
            access_denied('assetcentral');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'table'));
        }

        $data['title'] = _l('assetcentral') . ' - ' . _l('assetcentral_requests_menu');
        $this->load->view('requests/manage', $data);
    }

    public function create_requests($id = '')
    {
        if (!has_permission('assetcentral', '', 'create')) {
            access_denied('assetcentral');
        }

        if ($this->input->post() && $id === '') {

            $response = $this->assetcentral_model->create($this->input->post() + ['created_at' => date('Y-m-d H:i:s')]);

            if ($response == true) {
                set_alert('success', _l('assetcentral_created_successfully'));
            } else {
                set_alert('warning', _l('assetcentral_not_created_successfully'));
            }

            redirect(admin_url('assetcentral/manage'));

        } elseif ($this->input->post() && $id !== '') {
            $response = $this->assetcentral_model->update($id, $this->input->post());

            if ($response == true) {
                set_alert('success', _l('assetcentral_updated_successfully'));
            } else {
                set_alert('warning', _l('assetcentral_not_updated_successfully'));
            }

            redirect(admin_url('assetcentral/manage'));
        }

        $data['title'] = _l('assetcentral') . ' - ' . _l('assetcentral_requests_menu');
        if ($id) {
            $data['element_data'] = $this->assetcentral_model->get($id);
        }

        $this->load->view('create', $data);
    }

    public function delete_requests($id = '')
    {
        if (!has_permission('assetcentral', '', 'delete')) {
            access_denied('assetcentral');
        }

        if (!$id) {
            redirect(admin_url('assetcentral/manage'));
        }

        $response = $this->assetcentral_model->delete($id);

        if ($response == true) {
            set_alert('success', _l('deleted', _l('assetcentral')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('assetcentral')));
        }

        redirect(admin_url('assetcentral/manage'));
    }

    public function manage_appreciation()
    {
        if (!has_permission('assetcentral_appreciations', '', 'view')) {
            access_denied('assetcentral');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'appreciations/table'), ['postData' => $_POST]);
        }

        $data['title'] = _l('assetcentral') . ' - ' . _l('assetcentral_appreciations_menu');
        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['asset_categories'] = $this->assetcentral_model->get_all_asset_category();
        $data['asset_locations'] = $this->assetcentral_model->get_all_asset_location();

        $this->load->view('appreciations/manage', $data);
    }

    public function manage_depreciation()
    {
        if (!has_permission('assetcentral_depreciation', '', 'view')) {
            access_denied('assetcentral');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'depreciations/table'), ['postData' => $_POST]);
        }

        $data['title'] = _l('assetcentral') . ' - ' . _l('assetcentral_depreciation_menu');
        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['asset_categories'] = $this->assetcentral_model->get_all_asset_category();
        $data['asset_locations'] = $this->assetcentral_model->get_all_asset_location();

        $this->load->view('depreciations/manage', $data);
    }

    public function reports()
    {
        if (!has_permission('assetcentral_reports', '', 'view')) {
            access_denied('assetcentral_reports');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('assetcentral', 'table'));
        }

        $asset_status_data = $this->assetcentral_model->assetStatusReport();

        $status_data = [];
        foreach ($asset_status_data as $data) {
            $status_id = $data['asset_status'];
            $status_name = ucfirst(assetcentral_get_status_data($status_id)['value']);
            $status_data[] = [
                'status_name' => $status_name,
                'count' => $data['count']
            ];
        }

        $data['asset_location_data'] = $this->assetcentral_model->assetLocationReport();
        $data['asset_year_data'] = $this->assetcentral_model->assetsBoughtByYear();
        $data['assets_added_year_chart'] = $this->assetcentral_model->getAssetsAddedYearChart();
        $data['asset_assigned_chart_data'] = $this->assetcentral_model->assetsAssignedByChart();
        $data['asset_location_map_chart'] = $this->assetcentral_model->assetsLocationOnMapChart();
        $data['asset_summary_data'] = $this->assetcentral_model->getAssetSummaryData();

        $data['asset_status_data'] = $status_data;
        $data['title'] = _l('assetcentral') . ' - ' . _l('assetcentral_reports_menu');

        $this->load->view('reports/manage', $data);
    }

    public function add_note($rel_id)
    {
        $this->load->model('misc_model');
        $this->misc_model->add_note($this->input->post(), 'assets', $rel_id);
        echo $rel_id;
    }

    public function get_notes($id)
    {
        $this->load->model('misc_model');
        $data['notes'] = $this->misc_model->get_notes($id, 'assets');
        $this->load->view('admin/includes/sales_notes_template', $data);
    }

    public function qr_scanner()
    {
        $data['title'] = _l('assetcentral') . ' - ' . _l('assetcentral_qr_scanner');
        $this->load->view('qr_scanner/manage', $data);
    }

    public function bulk_checkin_assets()
    {
        if (!is_admin()) {
            access_denied('assetcentral_audits');
        }

        if ($this->input->post()) {

            $postData = $this->input->post();

            $sendEmail = false;
            if (isset($postData['notify'])) {
                $sendEmail = true;
            }
            $assetList = $postData['assets'];
            unset($postData['assets']);

            $rel_id = 0;
            $related_to = '';
            $requiredInput = 'customers';
            $whoReceivesNotifications = [];
            $notificationUrl = '';
            $emailUrl = '';

            if ($postData['allocate_to'] == 'customer') {
                $rel_id = $postData['customers'];
                $related_to = 'customers';
                $requiredInput = 'customers';
                $emailUrl = admin_url('clients/client/' . $rel_id);
                $notificationUrl = 'clients/client/' . $rel_id;

                $this->load->model('clients_model');
                $customerContacts = $this->clients_model->get_contacts($rel_id);
                foreach ($customerContacts as $contact) {
                    $whoReceivesNotifications[] = ['id' => $contact['id'], 'email' => $contact['email']];
                }
            }
            if ($postData['allocate_to'] == 'staff') {
                $rel_id = $postData['staff'];
                $related_to = 'staff';
                $requiredInput = 'staff';
                $emailUrl = admin_url('assetcentral/manage_my_assets');
                $notificationUrl = 'assetcentral/manage_my_assets';

                $whoReceivesNotifications[] = ['id' => get_staff($rel_id)->staffid, 'email' => get_staff($rel_id)->email];
            }
            if ($postData['allocate_to'] == 'project') {
                $rel_id = $postData['projects'];
                $related_to = 'project';
                $requiredInput = 'projects';
                $emailUrl = admin_url('projects/view/' . $rel_id);
                $notificationUrl = 'projects/view/' . $rel_id;

                $this->load->model('projects_model');
                $projectMembers = $this->projects_model->get_project_members($rel_id);
                foreach ($projectMembers as $staff) {
                    $whoReceivesNotifications[] = ['id' => $staff['staff_id'], $staff['email']];
                }
            }

            if (empty($postData['allocate_to'])) {
                set_alert('warning', _l('assetcentral_required_not_filled'));
                redirect(admin_url('assetcentral/bulk_checkin_assets'));
            }
            if (empty($postData[$requiredInput])) {
                set_alert('warning', _l('assetcentral_required_not_filled'));
                redirect(admin_url('assetcentral/bulk_checkin_assets'));
            }

            foreach ($assetList as $assetID) {

                $postData['status'] = assetcentral_get_status_data(2)['value'];

                $assetData = $this->assetcentral_model->get_asset($assetID);
                $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

                $this->assetcentral_model->update_asset($assetID, ['asset_status' => assetcentral_get_status_data(2)['id']]);
                $this->assetcentral_model->create_asset_assign([
                    'asset_id' => $assetID,
                    'assigned_to' => $related_to,
                    'assigned_id' => $rel_id,
                    'assign_date' => date('Y-m-d H:i:s')
                ]);

                $this->assetcentral_model->create_asset_event([
                    'event_form' => json_encode($postData),
                    'asset_id' => $assetID,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $assetID,
                    'event' => ucfirst(assetcentral_get_status_data(2)['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data(2)['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);

                if ($sendEmail) {

                    foreach ($whoReceivesNotifications as $receiver) {
                        $notified = add_notification([
                            'description' => 'assetcentral_asset_allocated_notification',
                            'touserid' => $receiver['id'],
                            'fromcompany' => 1,
                            'fromuserid' => 0,
                            'link' => $notificationUrl,
                            'additional_data' => serialize([
                                $assetData->asset_name,
                                get_staff_full_name(get_staff_user_id())
                            ]),
                        ]);

                        if ($notified) {
                            pusher_trigger_notification([$receiver['id']]);
                        }

                        $assetName = $assetData->asset_name ?? "";

                        $this->load->model('emails_model');
                        $this->emails_model->send_simple_email(
                            $receiver['email'],
                            'New Asset Allocated To You -' . get_option('companyname'),
                            '
            Hello,
<br>
Asset with title <strong>' . $assetName . '</strong> has been allocated to you by : <strong>' . get_staff_full_name(get_staff_user_id()) . '</strong>
<br>
<a href="' . $emailUrl . '">More Details</a>
<br>
Best regards,<br>
' . get_option('companyname') . '
            '
                        );

                    }
                }

            }

            set_alert('success', _l('assetcentral_bulk_event_success'));
            redirect(admin_url('assetcentral/manage_assets'));

        }

        $data['title'] = _l('assetcentral_bulk_checkin_assets');

        $this->load->model('staff_model');
        $this->load->model('clients_model');
        $this->load->model('projects_model');

        $data['staffMembers'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['clients'] = $this->clients_model->get('', [db_prefix() . 'clients.active' => 1]);
        $data['projects'] = $this->projects_model->get();
        $data['assets'] = $this->assetcentral_model->get_all_asset();

        $this->load->view('assets/bulk/checkin', $data);
    }

    public function bulk_checkout_assets()
    {
        if (!is_admin()) {
            access_denied('assetcentral_audits');
        }

        if ($this->input->post()) {

            $postData = $this->input->post();
            $rel_id = 0;
            $related_to = '';
            $requiredInput = 'customers';

            if ($postData['allocate_to'] == 'customer') {
                $rel_id = $postData['customers'];
                $related_to = 'customers';
                $requiredInput = 'customers';
            }
            if ($postData['allocate_to'] == 'staff') {
                $rel_id = $postData['staff'];
                $related_to = 'staff';
                $requiredInput = 'staff';
            }
            if ($postData['allocate_to'] == 'project') {
                $rel_id = $postData['projects'];
                $related_to = 'project';
                $requiredInput = 'projects';
            }

            if (empty($postData['allocate_to'])) {
                set_alert('warning', _l('assetcentral_required_not_filled'));
                redirect(admin_url('assetcentral/bulk_checkin_assets'));
            }
            if (empty($postData[$requiredInput])) {
                set_alert('warning', _l('assetcentral_required_not_filled'));
                redirect(admin_url('assetcentral/bulk_checkin_assets'));
            }

            $assetsList = $this->assetcentral_model->get_related_assets_list($rel_id, $related_to);


            foreach ($assetsList as $asset) {
                $assetData = $this->assetcentral_model->get_asset($asset['asset_id']);
                $currentAssetStatus = assetcentral_get_status_data($assetData->asset_status);

                $this->assetcentral_model->update_asset($asset['asset_id'], ['asset_status' => 1]);
                $this->assetcentral_model->delete_asset_assign('', $asset['asset_id']);

                $this->assetcentral_model->create_asset_event([
                    'event_form' => '{"id":"","asset_id":"' . $asset['asset_id'] . '","status":"revocate"}',
                    'asset_id' => $asset['asset_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->assetcentral_model->create_asset_history([
                    'asset_id' => $asset['asset_id'],
                    'event' => ucfirst(assetcentral_get_status_data(1)['value']),
                    'field' => 'Status',
                    'changed_from' => ucfirst($currentAssetStatus['value']),
                    'changed_to' => ucfirst(assetcentral_get_status_data(1)['value']),
                    'changed_by' => get_staff_user_id(),
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }

            set_alert('success', _l('assetcentral_bulk_event_success'));
            redirect(admin_url('assetcentral/manage_assets'));

        }

        $data['title'] = _l('assetcentral_bulk_checkout_assets');

        $this->load->model('staff_model');
        $this->load->model('clients_model');
        $this->load->model('projects_model');

        $data['staffMembers'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['clients'] = $this->clients_model->get('', [db_prefix() . 'clients.active' => 1]);
        $data['projects'] = $this->projects_model->get();

        $this->load->view('assets/bulk/checkout', $data);
    }

    public function bulk_manager_assets_transfer()
    {
        if (!is_admin()) {
            access_denied('assetcentral_audits');
        }

        if ($this->input->post()) {

            $postData = $this->input->post();

            $sendEmail = false;
            if (isset($postData['notify'])) {
                $sendEmail = true;
            }

            $transferFrom = $postData['transfer_from'];
            $transferTo = $postData['transfer_to'];

            $this->assetcentral_model->update_asset_manager($transferFrom, ['asset_manager' => $transferTo]);

            if ($sendEmail) {
                $notified = add_notification([
                    'description' => 'assetcentral_asset_transfered_notification',
                    'touserid' => $transferTo,
                    'fromcompany' => 1,
                    'fromuserid' => 0,
                    'link' => "assetcentral/manage_assets",
                ]);

                if ($notified) {
                    pusher_trigger_notification([$transferTo]);
                }

                $emailUrl = admin_url('assetcentral/manage_assets');
                $this->load->model('emails_model');
                $this->emails_model->send_simple_email(
                    get_staff($transferTo)->email,
                    'New Assets Under Your Management -' . get_option('companyname'),
                    '
            Hello,
<br>
New assets have been added under your management by : <strong>' . get_staff_full_name(get_staff_user_id()) . '</strong>
<br>
<a href="' . $emailUrl . '">More Details</a>
<br>
Best regards,<br>
' . get_option('companyname') . '
            '
                );

            }

            set_alert('success', _l('assetcentral_bulk_event_success'));
            redirect(admin_url('assetcentral/manage_assets'));

        }

        $data['title'] = _l('assetcentral_bulk_asset_manager_transfer');

        $this->load->model('staff_model');
        $data['staffMembers'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $this->load->view('assets/bulk/transfer_asset_manager', $data);
    }
}
