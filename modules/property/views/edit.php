<?php init_head(); ?>

<div id="wrapper" class="contact_details">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">


                        <div class="_buttons">

                            <div class="flex items-center justify-between">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 class="mt0"> <?php echo isset($property) ? _l('edit_property') : _l('add_property'); ?></h4>
                                    </div>

                                </div>

                            </div>
                        </div>


                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <form method="post">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
           value="<?= $this->security->get_csrf_hash(); ?>" />
                            <div class="form-group">
                                <label><?php echo _l('property_name'); ?></label>
                                <input type="text" name="name" class="form-control" value="<?= isset($property) ? $property->name : '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label><?php echo _l('property_address'); ?></label>
                                <textarea name="address" class="form-control"><?= isset($property) ? $property->address : '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label><?php echo _l('property_price'); ?></label>
                                <input type="number" step="0.01" name="price" class="form-control" value="<?= isset($property) ? $property->price : '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label><?php echo _l('property_description'); ?></label>
                                <textarea name="description" class="form-control"><?= isset($property) ? $property->description : '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label><?php echo _l('property_status'); ?></label>
                                <select name="status" class="form-control">
                                    <option value="available" <?= isset($property) && $property->status == 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="sold" <?= isset($property) && $property->status == 'sold' ? 'selected' : ''; ?>>Sold</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success"><?php echo isset($property) ? _l('update') : _l('add'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

</body>

</html>