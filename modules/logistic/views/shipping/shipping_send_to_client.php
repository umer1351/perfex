<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade email-template modal_index " data-editor-id=".<?php echo 'tinymce-' . $shipping->id; ?>" id="shipping_send_to_client_modal" tabindex="-1" role="dialog" data-toggle="modal">
    <div class="modal-dialog" role="document">
        <?php echo form_open('admin/logistic/send_shipping_to_email/' . $shipping->id); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('shipping_send_to_client_modal_heading'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?php echo form_hidden('redirect_url', ''); ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php
                            if ($template_disabled) {
                                echo '<div class="alert alert-danger">';
                                echo 'The email template <b><a href="' . admin_url('emails/email_template/' . $template_id) . '" target="_blank">' . $template_system_name . '</a></b> is disabled. Click <a href="' . admin_url('emails/email_template/' . $template_id) . '" target="_blank">here</a> to enable the email template in order to be sent successfully.';
                                echo '</div>';
                            }
                            $selected = [];
                            $contacts = $this->logistic_model->get_recipients_of_shipping($shipping->recipient_id);
                            foreach ($contacts as $contact) {
                                array_push($selected, $contact['id']);
                            }
                            if (count($selected) == 0) {
                                echo '<p class="text-danger">' . _l('sending_email_contact_permissions_warning', _l('customer_permission_shipping')) . '</p><hr />';
                            }
                            echo render_select('sent_to[]', $contacts, ['id', 'email', 'first_name,last_name'], 'shipping_estimate_sent_to_email', $selected, ['multiple' => true], [], '', '', false);
                            ?>
                        </div>
                        <?php echo render_input('cc', 'CC'); ?>
                        <hr />
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="attach_pdf" id="attach_pdf" checked>
                            <label for="attach_pdf"><?php echo _l('shipping_send_to_client_attach_pdf'); ?></label>
                        </div>
           
               
                    <hr />
                    <h5 class="bold"><?php echo _l('shipping_send_to_client_preview_template'); ?></h5>
                    <hr />
                    <?php echo render_textarea('email_template_custom', '', $template->message, [], [], '', 'tinymce-' . $shipping->id); ?>
                    <?php echo form_hidden('template_name', $template_name); ?>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-primary"><?php echo _l('send'); ?></button>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
</div>

