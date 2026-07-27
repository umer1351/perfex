<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('app_customers_portal_head'); ?>


<div class="panel_s section-heading section-invoices">
	<div class="panel-body">
		<div class="col-md-6">

			<h4 class="no-margin section-text"><?php echo _l('wm_warranty_claims'); ?></h4>
			
		</div>

		<div class="col-md-6">
			<ul class="nav navbar-nav navbar-right">
				
				<li class="customers-nav-item-products_services">
					<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_informations') ?>" class=""><?php echo _l('wm_warranty_informations'); ?></a>
				</li>
				<li class="customers-nav-item-services">
					<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims') ?>" class="a_active"><strong><?php echo _l('wm_warranty_claims'); ?></strong></a>
				</li>

			</ul>
		</div>
	</div>
</div>

<div class="panel_s">
	<div class="panel-body">
		<div class="row mbot25">
			<div class="col-md-2 list-status statement-bg  projects-status">
				<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims/cancelled') ?>" class=" sm-portal-a-cancelled" >
					<h4 class="bold text-uppercase sm-portal-h-cancelled" ><?php echo _l('wm_cancelled') ?></h4>
					<span class="bold sm-portal-span-cancelled" ><?php echo app_format_number(isset($warranty_claim_status['cancelled']) ? $warranty_claim_status['cancelled'] : 0 , true) ?></span>
				</a>
			</div>

			<div class="col-md-2 list-status projects-status">
				<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims/approved') ?>" class=" sm-portal-a-renewal" >
					<h4 class="bold text-uppercase sm-portal-h-cancelled" ><?php echo _l('wm_approved'); ?></h4>
					<span class="bold sm-portal-span-cancelled" ><?php echo app_format_number(isset($warranty_claim_status['approved']) ? $warranty_claim_status['approved'] : 0 , true) ?></span>
				</a>
			</div>

			<div class="col-md-2 list-status projects-status">
				<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims/processing') ?>" class=" sm-portal-a-expired">
					<h4 class="bold text-uppercase sm-portal-h-cancelled" ><?php echo _l('wm_processing'); ?></h4>
					<span class="bold sm-portal-span-cancelled" ><?php echo app_format_number(isset($warranty_claim_status['processing']) ? $warranty_claim_status['processing'] : 0 , true) ?></span>
				</a>
			</div>

			<div class="col-md-2 list-status projects-status">
				<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims/complete') ?>" class=" sm-portal-a-complete">
					<h4 class="bold text-uppercase text-success sm-portal-h-cancelled" ><?php echo _l('wm_complete'); ?></h4>
					<span class="bold sm-portal-span-cancelled" ><?php echo app_format_number(isset($warranty_claim_status['complete']) ? $warranty_claim_status['complete'] : 0 , true) ?></span>
				</a>
			</div>

			<div class="col-md-2 list-status projects-status">
				<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims/submitted') ?>" class=" sm-portal-a-pause" >
					<h4 class="bold text-uppercase text-success sm-portal-h-cancelled" ><?php echo _l('wm_submitted'); ?></h4>
					<span class="bold sm-portal-span-cancelled" ><?php echo app_format_number(isset($warranty_claim_status['submitted']) ? $warranty_claim_status['submitted'] : 0 , true) ?></span>
				</a>
			</div>
			<div class="col-md-2 list-status projects-status">
				<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims/closed') ?>" class=" sm-portal-a-activate" >
					<h4 class="bold text-uppercase text-success sm-portal-h-cancelled" ><?php echo _l('wm_closed') ; ?></h4>
					<span class="bold sm-portal-span-cancelled" ><?php echo app_format_number(isset($warranty_claim_status['closed']) ? $warranty_claim_status['closed'] : 0 , true) ?></span>
				</a>
			</div>

		</div>

		<hr />
		<table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
			<thead>
				<tr>
					<th class="th-invoice-number hide"><?php echo _l('id'); ?></th>
					<th class="th-invoice-number "><?php echo _l('wm_claim_code'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_created_by'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_created_type'); ?></th>
					<th class="th-invoice-number hide"><?php echo _l('client'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_description'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_date_created'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_status'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_options'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($warranty_claims as $warranty_claim){ ?>
					<tr>

						<td class="hide" data-order="<?php echo new_html_entity_decode($warranty_claim['id']); ?>"><?php echo new_html_entity_decode($warranty_claim['id']); ?></td>

						<td  class="" data-order="<?php echo new_html_entity_decode($warranty_claim['claim_code']); ?>"><?php echo new_html_entity_decode($warranty_claim['claim_code']); ?></td>


						<?php 
						$created_name = '';
						if($warranty_claim['created_type'] == 'staff'){
							$created_name = get_staff_full_name($warranty_claim['created_id']);
						}else{
							$created_name = get_contact_full_name($warranty_claim['created_id']);
						}
						?>
						<td data-order="<?php echo new_html_entity_decode($created_name); ?>"><?php echo new_html_entity_decode($created_name); ?></td>
						<td data-order="<?php echo new_html_entity_decode($warranty_claim['created_type']); ?>"><?php echo new_html_entity_decode($warranty_claim['created_type']); ?></td>

						<td class="hide"> data-order="<?php echo new_html_entity_decode(get_company_name($warranty_claim['client_id'])); ?>"><?php echo new_html_entity_decode(get_company_name($warranty_claim['client_id'])); ?></td>

						<td data-order="<?php echo new_html_entity_decode($warranty_claim['description']); ?>"><?php echo strip_tags($warranty_claim['description']); ?></td>
						<td data-order="<?php echo new_html_entity_decode($warranty_claim['datecreated']); ?>"><?php echo _dt($warranty_claim['datecreated']); ?></td>

						<td data-order=""><?php echo render_warranty_status_html($warranty_claim['id'], 'warranty_claim', $warranty_claim['status']) ?></td>

						<?php 
						$option = '';
						
						$option .= '<a href="'.site_url('warranty_management/warranty_management_client/warranty_claim_detail/'.$warranty_claim['id']).'" class="btn btn-primary btn-icon" data-original-title="View" data-toggle="tooltip" data-placement="top">
						<i class="fa fa-eye"></i>
						</a>';

						$_data = $option;
						?>

						<td data-order="<?php echo new_html_entity_decode($warranty_claim['status']); ?>"><?php echo new_html_entity_decode($option); ?></td>

					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php hooks()->do_action('app_customers_portal_footer'); ?>
