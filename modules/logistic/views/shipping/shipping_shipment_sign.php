<?php if(isset($shipment_sign->id)){ ?>
 <div class="col-md-10">  
  <?php if(file_exists(LOGISTIC_MODULE_UPLOAD_FOLDER.'/shipping_delivery_shipment/sign/'.$shipping->id.'/signature_'.$shipping->id.'.png')){ ?>
  <img src="<?php echo base_url(LOGISTIC_PATH.'shipping_delivery_shipment/sign/'.$shipping->id.'/signature_'.$shipping->id.'.png'); ?>" class="">

     
  <?php }elseif(file_exists(LOGISTIC_MODULE_UPLOAD_FOLDER.'/shipping_delivery_shipment/sign/'.$shipping->id.'/signature_'.$shipping->id.'.jpg')){ ?>
     <img src="<?php echo base_url(LOGISTIC_PATH.'shipping_delivery_shipment/sign/'.$shipping->id.'/signature_'.$shipping->id.'.jpg'); ?>" class="">

     
  <?php } ?>

  <p class="bold text-center text-success mtop15"><?php echo _l('lg_signed').' '._dt($shipment_sign->dateadded); ?></p> 
</div>
 <div class="col-md-2 text-left hide">
   <a href="javascript:void(0);" data-toggle="tooltip" title="<?php echo _l('lg_remove_sign'); ?>" onclick="remove_sign(<?php echo lg_html_entity_decode($shipping->id); ?>); return false;" class=" text-danger"><i class="fa fa-remove"></i></a>
 </div>

<?php }else{  ?>
  <a href="javascript:void(0);" class="btn btn-primary sign-open-modal" onclick="sign_package(this); return false;"><i class="fa fa-pencil">  </i><?php echo ' '._l('lg_sign'); ?></a>
<?php } ?>