<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade task-modal-single" id="alm_call_log_modal_view" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog  modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header task-single-header" data-task-single-id="<?php echo $call_log->id; ?>">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _l('alm_call_log_details') ?></h4>
                <div class="clearfix"></div>
                <h5 class="no-mtop task-info-created">
                    <small class="text-dark"><?php echo '<b>' . _l('alm_call_started_at') . '</b> <span class="text-dark">' . _dt(strtotime($call_log->started_at)) . '</span>'; ?></small>
                </h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mtop20">
                        <div class="clearfix"></div>

                        <div class="panel_s">
                            <div class="panel-body">
                                <p style="font-weight: 550;font-size: 14px;padding: 15px 15px;background: rgb(250, 250, 250);border-top-left-radius: 4px;border-top-right-radius: 4px;margin-top: -24px;margin-left: -24px;margin-right: -24px;">Details</p>
                                <div class="tw-flex tw-justify-between tw-items-center tw-w-full tw-py-2" style="border-bottom: 1px solid rgb(243, 243, 243);">
                                    <p style="font-weight: 450;"><?= _l('alm_call_log_call_id') ?></p>
                                    <div class="tw-flex tw-items-center" style="column-gap: 5px;">
                                        <p class="tw-font-normal" style="text-align: right;"><?= substr($call_log->call_id, 0, 5) ?>... <i onClick="alm_copy_to_clipboard('<?= $call_log->call_id ?>')" class="fa fa-regular fa-copy"></i></p>
                                    </div>
                                </div>

                                <div class="tw-flex tw-justify-between tw-items-center tw-w-full tw-py-2" style="border-bottom: 1px solid rgb(243, 243, 243);">
                                    <p style="font-weight: 450;"><?= _l('alm_call_log_to_number') ?></p>
                                    <div class="tw-flex tw-items-center" style="column-gap: 5px;">
                                        <p class="tw-font-normal" style="text-align: right;"><?= $call_log->to_number ?> <i onClick="alm_copy_to_clipboard('<?= $call_log->to_number ?>')" class="fa fa-regular fa-copy"></i></p>
                                    </div>
                                </div>

                                <div class="tw-flex tw-justify-between tw-items-center tw-w-full tw-py-2" style="border-bottom: 1px solid rgb(243, 243, 243);">
                                    <p style="font-weight: 450;"><?= _l('alm_call_log_from_number') ?></p>
                                    <div class="tw-flex tw-items-center" style="column-gap: 5px;">
                                        <p class="tw-font-normal" style="text-align: right;"><?= $call_log->from_number ?> <i onClick="alm_copy_to_clipboard('<?= $call_log->from_number ?>')" class="fa fa-regular fa-copy"></i></p>
                                    </div>
                                </div>

                                <div class="tw-flex tw-justify-between tw-items-center tw-w-full tw-py-2" style="border-bottom: 1px solid rgb(243, 243, 243);">
                                    <p style="font-weight: 450;"><?= _l('alm_call_log_duration') ?></p>
                                    <div class="tw-flex tw-items-center" style="column-gap: 5px;">
                                        <p class="tw-font-normal" style="text-align: right;"><?= convert_duration($call_log->call_length) ?></p>
                                    </div>
                                </div>

                                <div class="tw-flex tw-justify-between tw-items-center tw-w-full tw-py-2" style="border-bottom: 1px solid rgb(243, 243, 243);">
                                    <p style="font-weight: 450;"><?= _l('alm_call_log_price') ?></p>
                                    <div class="tw-flex tw-items-center" style="column-gap: 5px;">
                                        <p class="tw-font-normal" style="text-align: right;"><?= $call_log->price ?></p>
                                    </div>
                                </div>

                                <div class="tw-flex tw-justify-between tw-items-center tw-w-full tw-py-2" style="border-bottom: 1px solid rgb(243, 243, 243);">
                                    <p style="font-weight: 450;"><?= _l('alm_call_log_recording') ?></p>
                                    <div class="tw-flex tw-items-center" style="column-gap: 5px;">
                                        <p class="tw-font-normal" style="text-align: right;">
                                            <audio controls style="width: 210px; height: 30px">
                                                <source src="<?= $call_log->recording_url ?>" type="audio/wav">Your browser does not support the audio element.
                                            </audio>
                                        </p>
                                    </div>
                                </div>



                                <div class="tw-flex tw-justify-between tw-items-center tw-w-full tw-py-2">
                                    <p style="font-weight: 450;"><?= _l('alm_call_log_created_at') ?></p>
                                    <div class="tw-flex tw-items-center" style="column-gap: 5px;">
                                        <p class="tw-font-normal" style="text-align: right;"><?= _dt($call_log->created_at) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mtop25">
                        <div class="clearfix"></div>

                        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                            <div class="scroller arrow-left" style="display: none;"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right" style="display: none;"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#alm_call_log_modal_summary_tab" aria-controls="alm_call_log_modal_summary_tab" role="tab" data-toggle="tab"><?= _l('alm_call_log_summary') ?></a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#alm_call_log_modal_transcripts_table" aria-controls="alm_call_log_modal_transcripts_table" role="tab" data-toggle="tab"><?= _l('alm_call_log_transcripts') ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content mtop15">
                            <div role="tabpanel" class="tab-pane active" id="alm_call_log_modal_summary_tab">
                                <div class="clearfix"></div>

                                <div class="panel_s">
                                    <div class="panel-body">
                                        <?= $call_log->summary ?>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="alm_call_log_modal_transcripts_table">
                                <div class="clearfix"></div>

                                <div class="panel_s">
                                    <div class="panel-body" style="margin-top: 15px;max-height: 360px;overflow-y: scroll;">
                                        <div class="clearfix"></div>
                                        <div class="tw-flex tw-flex-col tw-w-full" style="gap: 10px;">
                                            <?php
                                            if ($call_log->transcripts) {
                                                $transcripts = json_decode($call_log->transcripts);


                                                foreach ($transcripts as $transcript) {
                                                    if (get_option('alm_voice_assistant') == 'bland_ai') {
                                                        if ($transcript->user == 'user') {
                                                            echo '<div class="tw-p-2 tw-self-start tw-rounded" style="background: rgb(248, 248, 248); border: none; width: 90%;">
                                                            <p style="font-weight: 550; font-size: 13px; color: rgb(0, 0, 0);">' . strtoupper($transcript->user) . '</p>
                                                            <p class="tw-font-normal" style="font-size: 13px; margin-top: 5px; color: rgb(0, 0, 0);">' . $transcript->text . '</p>
                                                        </div>';
                                                        } else if ($transcript->user == 'assistant') {
                                                            echo '<div class="tw-p-2 tw-self-end tw-rounded tw-bg-primary-600/100 !tw-text-white" style="border: none; width: 90%;">
                                                            <p style="font-weight: 550; font-size: 13px;">' . strtoupper($transcript->user) . '</p>
                                                            <p class="tw-font-normal" style="font-size: 13px; margin-top: 5px;">' . $transcript->text . '</p>
                                                        </div>';
                                                        } else {
                                                            echo '<div class="tw-p-2 tw-w-full tw-bg-transparent tw-rounded" style="border: 1px solid rgb(255, 159, 28);">
                                                            <p style="font-weight: 550; font-size: 13px; color: rgb(0, 0, 0);">' . strtoupper($transcript->user) . '</p>
                                                            <p class="tw-font-normal" style="font-size: 13px; margin-top: 5px; color: rgb(0, 0, 0);">' . $transcript->text . '</p>
                                                        </div>';
                                                        }
                                                    } else {
                                                        if ($transcript->role == 'user') {
                                                            echo '<div class="tw-p-2 tw-self-start tw-rounded" style="background: rgb(248, 248, 248); border: none; width: 90%;">
                                                            <p style="font-weight: 550; font-size: 13px; color: rgb(0, 0, 0);">USER</p>
                                                            <p class="tw-font-normal" style="font-size: 13px; margin-top: 5px; color: rgb(0, 0, 0);">' . $transcript->message . '</p>
                                                        </div>';
                                                        } else if ($transcript->role == 'bot') {
                                                            echo '<div class="tw-p-2 tw-self-end tw-rounded tw-bg-primary-600/100 !tw-text-white" style="border: none; width: 90%;">
                                                            <p style="font-weight: 550; font-size: 13px;">ASSISTANT</p>
                                                            <p class="tw-font-normal" style="font-size: 13px; margin-top: 5px;">' . $transcript->message . '</p>
                                                        </div>';
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>