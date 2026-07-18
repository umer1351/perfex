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
                                        <h4 class="mt0"><?php echo _l('property_list'); ?></h4>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="pull-right header-btn-new">
                                            <a href="<?php echo admin_url('property/add'); ?>" class="btn btn-info pull-left display-block"> <?php echo _l('add_property'); ?> <i class="fa fa-plus" aria-hidden="true"></i>
                                            </a>
                                        </span>
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


                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('property_name'); ?></th>
                                    <th><?php echo _l('property_address'); ?></th>
                                    <th><?php echo _l('property_price'); ?></th>
                                    <th><?php echo _l('property_status'); ?></th>
                                    <th><?php echo _l('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($properties)) { ?>
                                    <?php foreach ($properties as $p) { ?>
                                        <tr>
                                            <td><?= $p['id'] ?></td>
                                            <td><?= $p['name'] ?></td>
                                            <td><?= $p['address'] ?></td>
                                            <td><?= $p['price'] ?></td>
                                            <td><?= $p['status'] ?></td>
                                            <td>
                                                <a href="<?= admin_url('property/edit/' . $p['id']) ?>" class="btn btn-sm btn-warning"><?php echo _l('edit'); ?></a>
                                                <a href="<?= admin_url('property/delete/' . $p['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo _l('confirm_delete'); ?>')"><?php echo _l('delete'); ?></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center"><?php echo _l('no_records_found'); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

</body>

</html>