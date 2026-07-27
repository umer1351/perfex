<?php

if (!function_exists('assetcentral_asset_statuses')) {
    function assetcentral_asset_statuses()
    {
        return [
            [
                'id' => 1,
                'value' => 'available',
                'name' => _l('assetcentral_asset_status_available'),
                'badge' => '<span class="label project-status-available" style="color:#2563eb;border:1px solid #a8c1f7;background: #f6f9fe;">' . _l('assetcentral_asset_status_available') . '</span>',
                'icon' => 'fa fa-check-circle'
            ],
            [
                'id' => 2,
                'value' => 'allocate',
                'name' => _l('assetcentral_asset_status_allocate'),
                'badge' => '<span class="label project-status-allocate" style="color:#eab308;border:1px solid #fde047;background: #fffbeb;">' . _l('assetcentral_asset_status_allocate_badge') . '</span>',
                'icon' => 'fa fa-handshake'
            ],
            [
                'id' => 3,
                'value' => 'revocate',
                'name' => _l('assetcentral_asset_status_revocate'),
                'badge' => '<span class="label project-status-revocate" style="color:#ef4444;border:1px solid #fca5a5;background: #fee2e2;">' . _l('assetcentral_asset_status_revocate') . '</span>',
                'icon' => 'fa fa-undo'
            ],
            [
                'id' => 4,
                'value' => 'lost',
                'name' => _l('assetcentral_asset_status_lost'),
                'badge' => '<span class="label project-status-lost" style="color:#6b7280;border:1px solid #d1d5db;background: #f9fafb;">' . _l('assetcentral_asset_status_lost') . '</span>',
                'icon' => 'fa fa-search'
            ],
            [
                'id' => 5,
                'value' => 'found',
                'name' => _l('assetcentral_asset_status_found'),
                'badge' => '<span class="label project-status-found" style="color:#10b981;border:1px solid #6ee7b7;background: #ecfdf5;">' . _l('assetcentral_asset_status_found') . '</span>',
                'icon' => 'fa fa-map-marker-alt'
            ],
            [
                'id' => 6,
                'value' => 'broken',
                'name' => _l('assetcentral_asset_status_broken'),
                'badge' => '<span class="label project-status-broken" style="color:#dc2626;border:1px solid #f87171;background: #fef2f2;">' . _l('assetcentral_asset_status_broken') . '</span>',
                'icon' => 'fa fa-times-circle'
            ],
            [
                'id' => 7,
                'value' => 'dispose',
                'name' => _l('assetcentral_asset_status_dispose'),
                'badge' => '<span class="label project-status-dispose" style="color:#f97316;border:1px solid #fdba74;background: #fff7ed;">' . _l('assetcentral_asset_status_dispose_badge') . '</span>',
                'icon' => 'fa fa-trash'
            ],
            [
                'id' => 8,
                'value' => 'donate',
                'name' => _l('assetcentral_asset_status_donate'),
                'badge' => '<span class="label project-status-donate" style="color:#ec4899;border:1px solid #f9a8d4;background: #fdf2f8;">' . _l('assetcentral_asset_status_donate_badge') . '</span>',
                'icon' => 'fa fa-gift'
            ],
            [
                'id' => 9,
                'value' => 'sell',
                'name' => _l('assetcentral_asset_status_sell'),
                'badge' => '<span class="label project-status-sell" style="color:#f59e0b;border:1px solid #fcd34d;background: #fffbeb;">' . _l('assetcentral_asset_status_sell_badge') . '</span>',
                'icon' => 'fa fa-dollar-sign'
            ],
            [
                'id' => 10,
                'value' => 'repair',
                'name' => _l('assetcentral_asset_status_repair'),
                'badge' => '<span class="label project-status-repair" style="color:#3b82f6;border:1px solid #93c5fd;background: #eff6ff;">' . _l('assetcentral_asset_status_repair_badge') . '</span>',
                'icon' => 'fa fa-wrench'
            ],
            [
                'id' => 11,
                'value' => 'maintenance',
                'name' => _l('assetcentral_asset_status_maintenance'),
                'badge' => '<span class="label project-status-maintenance" style="color:#6366f1;border:1px solid #a5b4fc;background: #eef2ff;">' . _l('assetcentral_asset_status_maintenance') . '</span>',
                'icon' => 'fa fa-tools'
            ]
        ];
    }
}

if (!function_exists('assetcentral_get_status_data')) {
    function assetcentral_get_status_data($identifier)
    {
        $statuses = assetcentral_asset_statuses();

        foreach ($statuses as $status) {
            if ($status['id'] == $identifier || $status['value'] == $identifier) {
                return $status;
            }
        }

        return null;
    }
}

if (!function_exists('assetcentral_allocate_to_options')) {
    function assetcentral_allocate_to_options()
    {
        return [
            [
                'value' => 'staff',
                'name' => _l('assetcentral_allocate_to_staff')
            ],
            [
                'value' => 'customer',
                'name' => _l('assetcentral_allocate_to_customer')
            ],
            [
                'value' => 'project',
                'name' => _l('assetcentral_allocate_to_project')
            ]
        ];
    }
}

if (!function_exists('assetcentral_maintenance_statuses')) {
    function assetcentral_maintenance_statuses()
    {
        return [
            [
                'value' => '1',
                'name' => _l('assetcentral_asset_maintenance_status_scheduled')
            ],
            [
                'value' => '2',
                'name' => _l('assetcentral_asset_maintenance_status_in_progress')
            ],
            [
                'value' => '3',
                'name' => _l('assetcentral_asset_maintenance_status_on_hold')
            ],
            [
                'value' => '4',
                'name' => _l('assetcentral_asset_maintenance_status_cancelled')
            ],
            [
                'value' => '5',
                'name' => _l('assetcentral_asset_maintenance_status_completed')
            ]
        ];
    }
}

if (!function_exists('assetcentral_get_maintenance_status_data')) {
    function assetcentral_get_maintenance_status_data($identifier)
    {
        $statuses = assetcentral_maintenance_statuses();

        foreach ($statuses as $status) {
            if ($status['value'] == $identifier) {
                return $status;
            }
        }

        return null;
    }
}

if (!function_exists('assetcentral_handle_asset_main_image')) {
    function assetcentral_handle_asset_main_image($asset_id)
    {
        $path = FCPATH . 'modules/assetcentral/uploads/asset_images/main_image/' . $asset_id . '/';
        $CI = &get_instance();

        if (isset($_FILES['file']['name'])) {
            hooks()->do_action('before_upload_asset_main_image', $asset_id);
            $tmpFilePath = $_FILES['file']['tmp_name'];

            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $filename = $_FILES['file']['name'];
                $newFilePath = $path . $filename;
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $attachment = [];
                    $attachment[] = [
                        'file_name' => $filename,
                        'filetype' => $_FILES['file']['type'],
                    ];

                    $CI->misc_model->add_attachment_to_database($asset_id, 'asset_main_image', $attachment);
                }
            }
        }
    }
}

if (!function_exists('handle_asset_attachments_upload')) {
    function handle_asset_attachments_upload($id, $customer_upload = false)
    {
        $path = FCPATH . 'modules/assetcentral/uploads/asset_images/attachments/' . $id . '/';
        $CI = &get_instance();
        $totalUploaded = 0;

        if (isset($_FILES['file']['name'])
            && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
            if (!is_array($_FILES['file']['name'])) {
                $_FILES['file']['name'] = [$_FILES['file']['name']];
                $_FILES['file']['type'] = [$_FILES['file']['type']];
                $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
                $_FILES['file']['error'] = [$_FILES['file']['error']];
                $_FILES['file']['size'] = [$_FILES['file']['size']];
            }

            _file_attachments_index_fix('file');
            for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                hooks()->do_action('before_upload_asset_attachment', $id);
                // Get the temp file path
                $tmpFilePath = $_FILES['file']['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (_perfex_upload_error($_FILES['file']['error'][$i])
                        || !_upload_extension_allowed($_FILES['file']['name'][$i])) {
                        continue;
                    }

                    _maybe_create_upload_path($path);
                    $filename = unique_filename($path, $_FILES['file']['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $attachment = [];
                        $attachment[] = [
                            'file_name' => $filename,
                            'filetype' => $_FILES['file']['type'][$i],
                        ];

                        if (is_image($newFilePath)) {
                            create_img_thumb($newFilePath, $filename);
                        }

                        if ($customer_upload == true) {
                            $attachment[0]['staffid'] = 0;
                            $attachment[0]['contact_id'] = get_contact_user_id();
                            $attachment['visible_to_customer'] = 1;
                        }

                        $CI->misc_model->add_attachment_to_database($id, 'asset_attachment', $attachment);
                        $totalUploaded++;
                    }
                }
            }
        }

        return (bool)$totalUploaded;
    }
}

if (!function_exists('get_all_asset_attachments')) {
    function get_all_asset_attachments($id)
    {
        $CI = &get_instance();

        $attachments = [];

        $CI->db->where('rel_id', $id);
        $CI->db->where('rel_type', 'asset_attachment');
        $asset_attachments = $CI->db->get(db_prefix() . 'files')->result_array();

        $attachments[] = $asset_attachments;

        return hooks()->apply_filters('all_asset_attachments', $attachments, $id);
    }
}

if (!function_exists('assetcentral_depreciation_methods')) {
    function assetcentral_depreciation_methods()
    {
        return [
            [
                'value' => 'reducing_balance',
                'name' => 'Reducing Balance Depreciation',
                'badge' => '<span class="label ticket-status-1" style="border:1px solid #abc3ff; color:#2dceff;background: #f7feff;">Reducing Balance Depreciation</span>'
            ],
            [
                'value' => 'straight_line',
                'name' => 'Straight-Line Depreciation',
                'badge' => '<span class="label ticket-status-1" style="border:1px solid #c3abff; color:#622dff;background: #f7feff;">Straight-Line Depreciation</span>'
            ]
        ];
    }
}

if (!function_exists('assetcentral_get_depreciation_status_data')) {
    function assetcentral_get_depreciation_status_data($identifier)
    {
        $statuses = assetcentral_depreciation_methods();

        foreach ($statuses as $status) {
            if ($status['value'] == $identifier) {
                return $status;
            }
        }

        return null;
    }
}

if (!function_exists('assetcentral_get_asset_assigned_data')) {
    function assetcentral_get_asset_assigned_data($assigned_data)
    {
        if (empty($assigned_data)) {
            return '';
        }

        $assignedTo = '';
        $link = '';
        $name = '';

        if ($assigned_data->assigned_to === 'staff') {
            $assignedTo = _l('staff');
            $link = admin_url('staff/member/' . $assigned_data->assigned_id);
            $name = get_staff_full_name($assigned_data->assigned_id);
        }
        if ($assigned_data->assigned_to === 'customers') {
            $assignedTo = ucfirst(_l('customer'));
            $link = admin_url('clients/client/' . $assigned_data->assigned_id);
            $name = get_client($assigned_data->assigned_id)->company;
        }
        if ($assigned_data->assigned_to === 'project') {
            $assignedTo = _l('project');
            $link = admin_url('projects/view/' . $assigned_data->assigned_id);
            $name = get_project($assigned_data->assigned_id)->name;
        }

        return $assignedTo . ' - <a target="_blank" href="' . $link . '">' . $name . '</a>';
    }
}

if (!function_exists('assetcentral_calculate_straight_line_depreciation')) {
    function assetcentral_calculate_straight_line_depreciation($cost, $residual_value, $depreciation_months, $elapsed_months)
    {
        if (empty($cost) && empty($elapsed_months) && empty($depreciation_months)) {
            return '';
        }

        if ($depreciation_months == 0) {
            return 0;
        }

        $depreciable_amount = (float)$cost - (float)$residual_value;
        $monthly_depreciation = $depreciable_amount / $depreciation_months;
        $total_depreciation = $monthly_depreciation * $elapsed_months;
        $depreciated_value = $cost - $total_depreciation;

        return max($depreciated_value, $residual_value);
    }
}

if (!function_exists('assetcentral_calculate_straight_line_depreciation_chart')) {
    function assetcentral_calculate_straight_line_depreciation_chart($asset_data): array
    {
        $purchaseCost = (float)$asset_data->purchase_cost;
        $residualValue = (float)$asset_data->residual_value;
        $depreciationMonths = (int)$asset_data->depreciation_months;

        if (empty($purchaseCost) && empty($depreciationMonths) && empty($asset_data->purchase_date)) {
            return [];
        }

        $values = [];

        for ($month = 0; $month < $depreciationMonths; $month++) {
            $values[$month + 1] = assetcentral_calculate_straight_line_depreciation(
                $purchaseCost,
                $residualValue,
                $depreciationMonths,
                $month
            );
        }

        return $values;
    }
}

if (!function_exists('assetcentral_calculate_straight_line_depreciation_annual')) {
    function assetcentral_calculate_straight_line_depreciation_annual($asset_data): array
    {
        $purchaseCost = (float)$asset_data->purchase_cost;
        $residualValue = (float)$asset_data->residual_value;
        $depreciationMonths = (int)$asset_data->depreciation_months;

        $annual_depreciation = ($purchaseCost - $residualValue) / ($depreciationMonths / 12);

        $annual_data = [];
        $current_value = $purchaseCost;
        $accumulated_depreciation = 0;
        $purchase_date = new DateTime($asset_data->purchase_date);

        for ($year = 0; $year <= floor($depreciationMonths / 12); $year++) {
            $year_value = $purchase_date->format('Y');

            if ($year == 0) {

                $annual_data[] = [
                    'year' => $year_value,
                    'year_value' => $year_value,
                    'depreciation_expense' => '',
                    'accumulated_depreciation' => '',
                    'book_value' => $current_value,
                ];
            } else {
                $depreciation_expense = min($annual_depreciation, $current_value - $residualValue);
                $accumulated_depreciation += $depreciation_expense;
                $current_value -= $depreciation_expense;

                $annual_data[] = [
                    'year' => $year_value,
                    'year_value' => $year_value,
                    'depreciation_expense' => $depreciation_expense,
                    'accumulated_depreciation' => $accumulated_depreciation,
                    'book_value' => $current_value,
                ];
            }

            $purchase_date->modify('+1 year');
        }

        return $annual_data;
    }
}

if (!function_exists('assetcentral_calculate_reducing_balance_depreciation')) {
    function assetcentral_calculate_reducing_balance_depreciation($cost, $depreciation_percentage, $elapsed_months, $depreciation_months, $residual_value = 0)
    {
        if (empty($cost) && empty($depreciation_percentage) && empty($elapsed_months) && empty($depreciation_months)) {
            return '';
        }

        if ($depreciation_months == 0) {
            return 0;
        }

        $depreciation_rate = $depreciation_percentage / 100;
        $months_ratio = $elapsed_months / $depreciation_months;
        $depreciation_value = $cost * pow((1 - $depreciation_rate), $months_ratio);

        if ($residual_value > 0) {
            $depreciation_value = max($depreciation_value, $residual_value);
        }

        return $depreciation_value;
    }
}

if (!function_exists('assetcentral_calculate_reducing_balance_depreciation_chart')) {
    function assetcentral_calculate_reducing_balance_depreciation_chart($asset_data): array
    {
        $purchaseCost = (float)$asset_data->purchase_cost;
        $depreciationPercentage = (float)$asset_data->depreciation_percentage;
        $depreciationMonths = (int)$asset_data->depreciation_months;
        $residualValue = (float)$asset_data->residual_value;

        if (empty($purchaseCost) && empty($depreciationPercentage && empty($depreciationMonths) && empty($asset_data->purchase_date))) {
            return [];
        }

        $purchaseDate = new DateTime($asset_data->purchase_date);
        $currentDate = new DateTime();
        $elapsedMonths = $purchaseDate->diff($currentDate)->m + ($purchaseDate->diff($currentDate)->y * 12);

        $values = [];

        for ($month = 0; $month <= $elapsedMonths; $month++) {
            $depreciation_value = assetcentral_calculate_reducing_balance_depreciation(
                $purchaseCost,
                $depreciationPercentage,
                $month,
                $depreciationMonths,
                $residualValue
            );

            $values[$month] = $depreciation_value;
        }

        return $values;
    }
}

if (!function_exists('assetcentral_calculate_reducing_balance_depreciation_annual')) {
    function assetcentral_calculate_reducing_balance_depreciation_annual($asset_data): array
    {
        $purchaseCost = (float)$asset_data->purchase_cost;
        $depreciationPercentage = (float)$asset_data->depreciation_percentage;
        $depreciationMonths = (int)$asset_data->depreciation_months;
        $residualValue = $asset_data->residual_value !== '' ? (float)$asset_data->residual_value : 0;

        if (empty($purchaseCost) && empty($depreciationPercentage && empty($depreciationMonths) && empty($asset_data->purchase_date))) {
            return [];
        }

        $annual_data = [];
        $current_value = $purchaseCost;
        $accumulated_depreciation = 0;
        $purchase_date = new DateTime($asset_data->purchase_date);
        $current_date = new DateTime();

        // Calculate depreciation until the current date
        $interval = new DateInterval('P1M'); // 1 month interval
        $period = new DatePeriod($purchase_date, $interval, $current_date);

        foreach ($period as $date) {
            $year = $date->format('Y');
            $month = $date->format('m');

            $previous_value = $current_value;
            $current_value = assetcentral_calculate_reducing_balance_depreciation(
                $purchaseCost,
                $depreciationPercentage,
                (($year - $purchase_date->format('Y')) * 12) + $month - 1,
                $depreciationMonths,
                $residualValue
            );

            $depreciation_expense = $previous_value - $current_value;
            $accumulated_depreciation += $depreciation_expense;

            $annual_data[] = [
                'year' => $date->format('F Y'),
                'depreciation_expense' => $depreciation_expense,
                'accumulated_depreciation' => $accumulated_depreciation,
                'book_value' => $current_value,
            ];
        }

        return $annual_data;
    }
}

if (!function_exists('assetcentral_calculate_appreciation')) {
    function assetcentral_calculate_appreciation($cost, $appreciation_rate, $elapsed_months, $appreciation_periods)
    {
        if ($appreciation_periods <= 0) {
            return $cost;
        }
        $rate = $appreciation_rate / 100;
        $period_ratio = $elapsed_months / $appreciation_periods;

        return $cost * pow((1 + $rate), $period_ratio);
    }
}

if (!function_exists('assetcentral_calculate_gain_percentage')) {
    function assetcentral_calculate_gain_percentage($cost, $appreciated_value)
    {
        $gained_value = $appreciated_value - $cost;
        if ($cost == 0) {
            return 0;
        }
        $increase_percentage = ($gained_value / $cost) * 100;
        return round($increase_percentage, 2);
    }
}

if (!function_exists('assetcentral_calculate_appreciation_annual')) {
    function assetcentral_calculate_appreciation_annual($asset_data): array
    {
        $data = [];
        $current_date = new DateTime();
        $purchase_date = new DateTime($asset_data->purchase_date);
        $interval = new DateInterval('P1M');
        $elapsed_months = 0;

        if (empty($asset_data->purchase_cost) && empty($asset_data->appreciation_rate && empty($asset_data->appreciation_periods) && empty($asset_data->purchase_date))) {
            return [];
        }

        while ($purchase_date <= $current_date) {
            $appreciation_value = assetcentral_calculate_appreciation($asset_data->purchase_cost, $asset_data->appreciation_rate, $elapsed_months, $asset_data->appreciation_periods);
            $data[] = [
                'date' => $purchase_date->format('Y-m'),
                'value' => $appreciation_value
            ];
            $purchase_date->add($interval);
            $elapsed_months++;
        }

        return $data;
    }
}

if (!function_exists('assetcentral_calculate_appreciation_data')) {
    function assetcentral_calculate_appreciation_data($asset_data): array
    {
        if (empty($asset_data->purchase_cost) && empty($asset_data->appreciation_rate && empty($asset_data->appreciation_periods) && empty($asset_data->purchase_date))) {
            return [];
        }

        $data = [];
        $current_date = new DateTime();
        $purchase_date = new DateTime($asset_data->purchase_date);
        $interval = new DateInterval('P1M');
        $elapsed_months = 0;
        $previous_appreciation_value = $asset_data->purchase_cost;
        $total_appreciation = 0;

        while ($purchase_date <= $current_date) {
            $current_appreciation_value = assetcentral_calculate_appreciation($asset_data->purchase_cost, $asset_data->appreciation_rate, $elapsed_months, $asset_data->appreciation_periods);
            $monthly_appreciation = $current_appreciation_value - $previous_appreciation_value;
            $total_appreciation += $monthly_appreciation;
            $book_value = $current_appreciation_value;

            $data[] = [
                'date' => $purchase_date->format('Y-m'),
                'monthly_appreciation' => $monthly_appreciation,
                'total_appreciation' => $total_appreciation,
                'book_value' => $book_value
            ];

            $previous_appreciation_value = $current_appreciation_value;
            $purchase_date->add($interval);
            $elapsed_months++;
        }

        return $data;
    }
}

if (!function_exists('assetcentral_signature_pad_js')) {
    function assetcentral_signature_pad_js()
    {
        return '
        SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {
     var canvas = this._ctx.canvas;
       // First duplicate the canvas to not alter the original
       var croppedCanvas = document.createElement("canvas"),
       croppedCtx = croppedCanvas.getContext("2d");

       croppedCanvas.width = canvas.width;
       croppedCanvas.height = canvas.height;
       croppedCtx.drawImage(canvas, 0, 0);

       // Next do the actual cropping
       var w = croppedCanvas.width,
       h = croppedCanvas.height,
       pix = {
         x: [],
         y: []
       },
       imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height),
       x, y, index;

       for (y = 0; y < h; y++) {
         for (x = 0; x < w; x++) {
           index = (y * w + x) * 4;
           if (imageData.data[index + 3] > 0) {
             pix.x.push(x);
             pix.y.push(y);

           }
         }
       }
       pix.x.sort(function(a, b) {
         return a - b
       });
       pix.y.sort(function(a, b) {
         return a - b
       });
       var n = pix.x.length - 1;

       w = pix.x[n] - pix.x[0];
       h = pix.y[n] - pix.y[0];
       var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

       croppedCanvas.width = w;
       croppedCanvas.height = h;
       croppedCtx.putImageData(cut, 0, 0);

       return croppedCanvas.toDataURL();
     };


     function signaturePadChanged() {

       var input = document.getElementById("signatureInput");
       var $signatureLabel = $("#signatureLabel");
       $signatureLabel.removeClass("text-danger");

       if (signaturePad.isEmpty()) {
           $signatureLabel.addClass("text-danger");
           input.value = "";
           return false;
       }

       $("#signatureInput-error").remove();
       var partBase64 = signaturePad.toDataURLAndRemoveBlanks();
       partBase64 = partBase64.split(",")[1];
       input.value = partBase64;
     }

        var canvas = document.getElementById("signature");
        var clearButton = wrapper.querySelector("[data-action=clear]");
        var undoButton = wrapper.querySelector("[data-action=undo]");
        var identityFormSubmit = document.getElementById("identityConfirmationForm");

        var signaturePad = new SignaturePad(canvas, {
        maxWidth: 2,
      onEnd:function(){
            signaturePadChanged();
        }
    });

     clearButton.addEventListener("click", function(event) {
         signaturePad.clear();
         signaturePadChanged();
     });

     undoButton.addEventListener("click", function(event) {
         var data = signaturePad.toData();
         if (data) {
             data.pop(); // remove the last dot or line
             signaturePad.fromData(data);
             signaturePadChanged();
         }
     });

     $("#identityConfirmationForm").submit(function() {
         signaturePadChanged();
     });
        ';
    }
}

