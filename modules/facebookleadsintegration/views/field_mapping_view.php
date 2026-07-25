<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Header -->
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin">
                                    <i class="fa fa-exchange"></i>
                                    <?php echo _l('fb_leads_field_mapping'); ?>
                                </h4>
                                <p class="text-muted mtop5"><?php echo _l('fb_leads_field_mapping_description'); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('facebookleadsintegration'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('fb_leads_back_to_settings'); ?>
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Default Mappings Info -->
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong><?php echo _l('fb_leads_default_mappings_info'); ?></strong>
                            <br>
                            <?php echo _l('fb_leads_default_mappings_description'); ?>
                        </div>

                        <!-- Standard Fields Reference -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('fb_leads_facebook_standard_fields'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table table-condensed">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('fb_leads_facebook_field'); ?></th>
                                                    <th><?php echo _l('fb_leads_maps_to'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td><code>full_name</code> / <code>name</code></td><td>Name</td></tr>
                                                <tr><td><code>email</code></td><td>Email</td></tr>
                                                <tr><td><code>phone_number</code></td><td>Phone</td></tr>
                                                <tr><td><code>company_name</code></td><td>Company</td></tr>
                                                <tr><td><code>street_address</code></td><td>Address</td></tr>
                                                <tr><td><code>city</code></td><td>City</td></tr>
                                                <tr><td><code>state</code> / <code>province</code></td><td>State</td></tr>
                                                <tr><td><code>country</code></td><td>Country</td></tr>
                                                <tr><td><code>zip_code</code> / <code>post_code</code></td><td>Zip</td></tr>
                                                <tr><td><code>job_title</code></td><td>Position</td></tr>
                                                <tr><td><code>website</code></td><td>Website</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('fb_leads_perfex_lead_fields'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table table-condensed">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('fb_leads_field_name'); ?></th>
                                                    <th><?php echo _l('fb_leads_field_type'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($standard_fields as $field => $label): ?>
                                                <tr>
                                                    <td><code><?php echo $field; ?></code></td>
                                                    <td><?php echo $label; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Field Mappings -->
                        <div class="panel panel-default mtop20">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php echo _l('fb_leads_custom_field_mappings'); ?>
                                    <button type="button" class="btn btn-success btn-xs pull-right" id="btn-add-mapping">
                                        <i class="fa fa-plus"></i> <?php echo _l('fb_leads_add_mapping'); ?>
                                    </button>
                                </h4>
                            </div>
                            <div class="panel-body">
                                <p class="text-muted"><?php echo _l('fb_leads_custom_mapping_description'); ?></p>
                                
                                <div id="mappings-container">
                                    <?php if (empty($current_mappings)): ?>
                                    <div class="text-muted text-center" id="no-mappings-message">
                                        <?php echo _l('fb_leads_no_custom_mappings'); ?>
                                    </div>
                                    <?php else: ?>
                                    <?php foreach ($current_mappings as $index => $mapping): ?>
                                    <div class="fb-leads-mapping-row" data-index="<?php echo $index; ?>">
                                        <input type="text" class="form-control" placeholder="Facebook Field Name" 
                                               name="mappings[<?php echo $index; ?>][facebook_field]"
                                               value="<?php echo htmlspecialchars($mapping['facebook_field']); ?>">
                                        <span class="arrow"><i class="fa fa-arrow-right"></i></span>
                                        <select class="form-control" name="mappings[<?php echo $index; ?>][perfex_field]">
                                            <option value=""><?php echo _l('fb_leads_select_field'); ?></option>
                                            <optgroup label="<?php echo _l('fb_leads_standard_fields'); ?>">
                                                <?php foreach ($standard_fields as $field => $label): ?>
                                                <option value="<?php echo $field; ?>" <?php echo ($mapping['perfex_field'] ?? '') == $field ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <?php if (!empty($custom_fields)): ?>
                                            <optgroup label="<?php echo _l('fb_leads_custom_fields'); ?>">
                                                <?php foreach ($custom_fields as $cf): ?>
                                                <option value="cf_<?php echo $cf['id']; ?>" <?php echo ($mapping['perfex_field'] ?? '') == 'cf_' . $cf['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cf['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <?php endif; ?>
                                        </select>
                                        <button type="button" class="btn btn-danger btn-sm remove-mapping">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <hr />
                                <button type="button" class="btn btn-primary" id="btn-save-mappings">
                                    <i class="fa fa-save"></i> <?php echo _l('fb_leads_save_mappings'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Fields in Perfex -->
                        <?php if (!empty($custom_fields)): ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><?php echo _l('fb_leads_your_custom_fields'); ?></h4>
                            </div>
                            <div class="panel-body">
                                <p class="text-muted"><?php echo _l('fb_leads_custom_fields_info'); ?></p>
                                <table class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?php echo _l('fb_leads_field_name'); ?></th>
                                            <th><?php echo _l('fb_leads_slug'); ?></th>
                                            <th><?php echo _l('fb_leads_field_type'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($custom_fields as $cf): ?>
                                        <tr>
                                            <td><?php echo $cf['id']; ?></td>
                                            <td><?php echo htmlspecialchars($cf['name']); ?></td>
                                            <td><code><?php echo htmlspecialchars($cf['slug']); ?></code></td>
                                            <td><?php echo htmlspecialchars($cf['type']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="alert alert-info mtop10">
                                    <i class="fa fa-lightbulb-o"></i>
                                    <?php echo _l('fb_leads_custom_field_tip'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mapping Row Template -->
<template id="mapping-row-template">
    <div class="fb-leads-mapping-row" data-index="__INDEX__">
        <input type="text" class="form-control" placeholder="Facebook Field Name" 
               name="mappings[__INDEX__][facebook_field]" value="">
        <span class="arrow"><i class="fa fa-arrow-right"></i></span>
        <select class="form-control" name="mappings[__INDEX__][perfex_field]">
            <option value=""><?php echo _l('fb_leads_select_field'); ?></option>
            <optgroup label="<?php echo _l('fb_leads_standard_fields'); ?>">
                <?php foreach ($standard_fields as $field => $label): ?>
                <option value="<?php echo $field; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </optgroup>
            <?php if (!empty($custom_fields)): ?>
            <optgroup label="<?php echo _l('fb_leads_custom_fields'); ?>">
                <?php foreach ($custom_fields as $cf): ?>
                <option value="cf_<?php echo $cf['id']; ?>"><?php echo htmlspecialchars($cf['name']); ?></option>
                <?php endforeach; ?>
            </optgroup>
            <?php endif; ?>
        </select>
        <button type="button" class="btn btn-danger btn-sm remove-mapping">
            <i class="fa fa-times"></i>
        </button>
    </div>
</template>

<?php init_tail(); ?>

<script>
$(function() {
    var mappingIndex = <?php echo count($current_mappings); ?>;

    // Add new mapping
    $('#btn-add-mapping').on('click', function() {
        $('#no-mappings-message').remove();
        
        var template = $('#mapping-row-template').html();
        template = template.replace(/__INDEX__/g, mappingIndex);
        $('#mappings-container').append(template);
        mappingIndex++;
    });

    // Remove mapping
    $(document).on('click', '.remove-mapping', function() {
        $(this).closest('.fb-leads-mapping-row').remove();
        
        if ($('.fb-leads-mapping-row').length === 0) {
            $('#mappings-container').html('<div class="text-muted text-center" id="no-mappings-message"><?php echo _l('fb_leads_no_custom_mappings'); ?></div>');
        }
    });

    // Save mappings
    $('#btn-save-mappings').on('click', function() {
        var btn = $(this);
        var mappings = [];
        
        $('.fb-leads-mapping-row').each(function() {
            var fbField = $(this).find('input[name$="[facebook_field]"]').val();
            var perfexField = $(this).find('select[name$="[perfex_field]"]').val();
            
            if (fbField && perfexField) {
                var isCustomField = perfexField.indexOf('cf_') === 0;
                var customFieldId = isCustomField ? perfexField.replace('cf_', '') : null;
                
                mappings.push({
                    facebook_field: fbField,
                    perfex_field: isCustomField ? null : perfexField,
                    is_custom_field: isCustomField,
                    custom_field_id: customFieldId
                });
            }
        });
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_saving'); ?>');
        
        $.ajax({
            url: admin_url + 'facebookleadsintegration/save_field_mapping',
            type: 'POST',
            dataType: 'json',
            data: {
                mappings: mappings,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo _l('fb_leads_save_mappings'); ?>');
                
                if (response.success) {
                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message || 'Failed to save mappings');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo _l('fb_leads_save_mappings'); ?>');
                alert_float('danger', 'Failed to save mappings');
            }
        });
    });
});
</script>
