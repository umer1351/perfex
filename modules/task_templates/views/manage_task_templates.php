<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">

        <div class="panel_s">
            <div class="panel-body">
                <div class="row _buttons">
                    <div class="col-md-8">
                        <?php if(staff_can('create', 'task_templates')){ ?>
                            <a href="#" onclick="new_task_template(); return false;" class="btn btn-info pull-left new"><?php echo _l('tt_new_task_template'); ?></a>
                        <?php } ?>
                        <?php if(staff_can('create', 'tasks')){ ?>
                            <a href="#" onclick="new_task_from_template(); return false;" class="btn btn-info pull-left new mleft10"><?php echo _l('tt_new_task_from_template'); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <hr class="hr-panel-heading hr-10" />
                <div class="clearfix"></div>
                <?php $this->load->view('_task_template_table')?>
                </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

</body>
</html>
