<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $CI = &get_instance();
        $CI->load->dbforge();
        $fields = array(
            'start_type' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'after' => 'checklist_items'
            ),
            'startdate' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => NULL,
                'after' => 'start_type'
            ),
            'startdate_type' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => NULL,
                'after' => 'startdate'
            ),
            'duration_type' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => NULL,
                'after' => 'duration_value'
            ),
            'after_task_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => NULL,
                'after' => 'duration_type'
            ),
            'rel_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => NULL,
                'after' => 'cycles'
            ),
            'rel_type' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => NULL,
                'after' => 'rel_id'
            ),
            'milestone' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => "0",
                'after' => 'rel_type'
            ),
            'kanban_order' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => "1",
                'after' => 'milestone'
            ),
            'milestone_order' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => "0",
                'after' => 'kanban_order'
            ),
        );
        $CI->dbforge->add_column('task_templates', $fields);

        $CI->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'task_template_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'real_task_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'next_task_template_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'dateadded' => array(
                'type' => 'datetime',
            ),
            'added_by' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
        ));
        $CI->dbforge->add_key('id', TRUE);
        $CI->dbforge->create_table('task_templates_que');
    }
}