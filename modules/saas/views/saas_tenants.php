<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
	.client-padding-10 {
		padding: 10px !important;
	}
</style>

<?php

$client_plan = getClientPlan(get_client()->userid);

get_instance()->load->config('saas/features_limitation_config');
$limitations = config_item('limitations');

?>

<?php if (!empty($client_plan)) { ?>

	<?php
        $planDetails    	= json_decode($client_plan->plan_details_json, true);
    $defined_limitation  = get_limitations($client_plan->tenants_name);
    $planExpiryDate      = getPlanExpiryDate($client_plan->trial_start_time, $client_plan->trial_days);
    $trialDiff           = getRemainingDays($planExpiryDate);
    $daysCount           = abs($trialDiff);
    $daysLabel           = 'remaining_days';
    if ($trialDiff > 0) {
        $daysLabel = 'passed_days';
    }
    ?>

	<div class="alert alert-success"><?php echo _l('your_selected_plan'); ?>:<b> <?php echo $planDetails['plan_name']; ?></b></div>

	<div class="row">
		<div class="col-md-12">
			<div class="panel_s">
				<div class="panel-body client-padding-10">
					<div class="tw-flex tw-justify-between tw-items-center tw-p-1.5">
			            <p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
			            	<span class="tw-text-neutral-700"><?php echo _l('plan_details'); ?></span>
			            </p>
			        </div>
			        <hr class="-tw-mx-3 tw-mt-2 tw-mb-6">
					<dl class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-3 sm:tw-gap-5">
						<?php
                            foreach ($limitations as $key => $value) {
                                echo '<div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md tw-bg-white">
									<div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2">
										<dt class="tw-font-medium text-success"> Total '.$value['label'].'</dt>
										<dd class="tw-mt-1 tw-flex tw-items-baseline tw-justify-between md:tw-block lg:tw-flex">
											<div class="tw-flex tw-items-baseline tw-text-base tw-font-semibold tw-text-primary-600">'.total_rows(db_prefix().$value['dbTable']).'/'.$defined_limitation[$key].'</div>
										</dd>
									</div>
								</div>';
                            }
    ?>
					</dl>
				</div>
			</div>
		</div>
	</div>


	<?php switchDatabase(); ?>

	<?php if (!getTenantDbNameByClientID(get_client()->userid)) { ?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-danger">
					<strong>Error!</strong> <?php echo _l('tenant_db_warning'); ?>
				</div>
			</div>
		</div>
	<?php exit;
	} ?>

	<div class="row">
		<div class="col-md-12">
			<div class="panel_s">
			    <div class="panel-body client-padding-10">
			        <div class="tw-flex tw-justify-between tw-items-center tw-p-1.5">
			            <p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
			            	<span class="tw-text-neutral-700"><?php echo _l('subscription_information'); ?></span>
			            </p>
			        </div>
			        <hr class="-tw-mx-3 tw-mt-2 tw-mb-6">
			        <div class="row">
			        	<div class="col-md-8">
							<table class="table no-margin project-overview-table" style="font-size: 14px;">
								<tbody>
									<tr class="project-overview-start-date">
										<td class="bold"><?php echo _l('company_name'); ?></td>
										<td><?php echo $client_plan->tenants_name; ?></td>
									</tr>
									<tr class="project-overview-date-created">
										<td class="bold"><?php echo _l('company_domain'); ?></td>
										<td> <a href="<?php echo parse_url(base_url())['scheme'].'://'.$client_plan->tenants_name.'.'.parse_url(base_url())['host'].'/admin'; ?>" target="_blank">
												<i class="fa fa-external-link"></i> <?php echo $client_plan->tenants_name.'.'.parse_url(base_url())['host']; ?>
											</a></td>
									</tr>
									<tr class="project-overview-deadline">
										<td class="bold"><?php echo _l('selected_plan'); ?></td>
										<td><?php echo $planDetails['plan_name']; ?></td>
									</tr>
									<tr class="project-overview-deadline">
										<td class="bold"><?php echo _l('created_at'); ?></td>
										<td><?php echo time_ago($client_plan->trial_start_time); ?></td>
									</tr>
									<tr class="project-overview-date-finished">
										<td class="bold"><?php echo _l('plan_expiry'); ?></td>
										<td class="text-danger"><?php echo $planExpiryDate; ?></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-md-4">
							<div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md tw-bg-white">
								<div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2">
									<dt class="tw-font-medium text-success"><?php echo _l($daysLabel); ?></dt>
									<dd class="tw-mt-1 tw-flex tw-items-baseline tw-justify-between md:tw-block lg:tw-flex">
										<div class="tw-flex tw-items-baseline tw-text-base tw-font-semibold tw-text-primary-600"><?php echo $daysCount; ?> days</div>
									</dd>
								</div>
							</div>
						</div>
			        </div>
			    </div>
			</div>
		</div>
	</div>
<?php } ?>