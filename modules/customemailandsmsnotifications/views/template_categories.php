<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <a href="#" onclick="add_category(); return false;" class="btn btn-info pull-right">
                        <i class="fa fa-plus"></i> <?php echo _l('add_category'); ?>
                     </a>
                  </div>
                  <div class="clearfix"></div>
                  <hr class="hr-panel-heading">
                  
                  <h3><?php echo _l('template_categories'); ?></h3>
                  
                  <!-- Categories Table -->
                  <div class="row">
                     <div class="col-md-12">
                        <?php render_datatable([
                           _l('id'),
                           _l('category_name'),
                           _l('description'),
                           _l('color'),
                           _l('icon'),
                           _l('sort_order'),
                           _l('actions')
                        ], 'template_categories'); ?>
                     </div>
                  </div>
                  
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="category_modal" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('add_category'); ?></h4>
         </div>
         <div class="modal-body">
            <form id="category_form">
               <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
               <input type="hidden" name="id" value="">
               
               <div class="form-group">
                  <label for="name"><?php echo _l('category_name'); ?></label>
                  <input type="text" class="form-control" name="name" required>
               </div>
               
               <div class="form-group">
                  <label for="description"><?php echo _l('category_description'); ?></label>
                  <textarea class="form-control" name="description" rows="3"></textarea>
               </div>
               
               <div class="form-group">
                  <label for="color"><?php echo _l('category_color'); ?></label>
                  <input type="color" class="form-control" name="color" value="#3498db">
               </div>
               
               <div class="form-group">
                  <label for="icon"><?php echo _l('category_icon'); ?></label>
                  <input type="text" class="form-control" name="icon" placeholder="fa-folder">
               </div>
               
               <div class="form-group">
                  <label for="sort_order"><?php echo _l('sort_order'); ?></label>
                  <input type="number" class="form-control" name="sort_order" value="0">
               </div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="button" class="btn btn-info" onclick="save_category();"><?php echo _l('save'); ?></button>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>

<script>
function add_category() {
   $('#category_form')[0].reset();
   $('#category_form input[name="id"]').val('');
   $('#category_modal .modal-title').text('<?php echo _l('add_category'); ?>');
   $('#category_modal').modal('show');
}

function edit_category(id) {
   $.get('<?php echo admin_url('customemailandsmsnotifications/template/get_category/'); ?>' + id, function(response) {
      var category = JSON.parse(response);
      $('#category_form input[name="id"]').val(category.id);
      $('#category_form input[name="name"]').val(category.name);
      $('#category_form textarea[name="description"]').val(category.description);
      $('#category_form input[name="color"]').val(category.color);
      $('#category_form input[name="icon"]').val(category.icon);
      $('#category_form input[name="sort_order"]').val(category.sort_order);
      $('#category_modal .modal-title').text('<?php echo _l('edit_category'); ?>');
      $('#category_modal').modal('show');
   });
}

function save_category() {
   var formData = new FormData($('#category_form')[0]);
   
   $.ajax({
      url: '<?php echo admin_url('customemailandsmsnotifications/template/save_category'); ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
         var res = JSON.parse(response);
         if (res.success) {
            alert_float('success', res.message);
            $('#category_modal').modal('hide');
            $('.table-template_categories').DataTable().ajax.reload();
         } else {
            alert_float('danger', res.message);
         }
      }
   });
}

$(function(){
   initDataTable('.table-template_categories', window.location.href, [6], [6]);
});
</script>
