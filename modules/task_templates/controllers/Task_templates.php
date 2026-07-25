<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Task_templates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // IF MODULE DISABLED THEN SHOW 404
        if(!defined('TASK_TEMPLATES_MODULE_NAME'))
            show_404();
        $this->load->model("task_templates_model");
        $this->load->helper("security");
        hooks()->add_action('before_compile_css_assets', 'add_task_templates_css');
        hooks()->add_action('before_compile_scripts_assets', 'add_task_templates_own_scripts');
    }

    public function index(){
        if (! (staff_can('view', 'task_templates') || staff_can('view_own', 'task_templates'))) {
            access_denied('task_templates');
        }


        $data['title'] = _l('tt_module_title');
        $this->load->view('manage_task_templates', $data);
    }


    /* Add new task or update existing */
    public function task($id = '')
    {
        if (!staff_can('edit', 'task_templates') && !staff_can('create', 'task_templates')) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data                = $this->input->post();
            $data['description'] = html_purify($this->input->post('description', false));
            if ($id == '') {
                if (!staff_can('create', 'task_templates')) {
                    header('HTTP/1.0 400 Bad error');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
                $id      = $this->task_templates_model->add($data);
                $_id     = false;
                $success = false;
                $message = '';
                if ($id) {
                    $success       = true;
                    $_id           = $id;
                    $message       = _l('added_successfully', _l('tt_task_template_lower'));
                    $uploadedFiles = handle_task_template_attachments_array($id);
                    if ($uploadedFiles && is_array($uploadedFiles)) {
                        foreach ($uploadedFiles as $file) {
                            $this->misc_model->add_attachment_to_database($id, 'task_templates', [$file]);
                        }
                    }
                }
                echo json_encode([
                    'success' => $success,
                    'id'      => $_id,
                    'message' => $message,
                ]);
            } else {
                if (!staff_can('edit','task_templates')) {
                    header('HTTP/1.0 400 Bad error');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
                $uploadedFiles = handle_task_template_attachments_array($id);
                if ($uploadedFiles && is_array($uploadedFiles)) {
                    foreach ($uploadedFiles as $file) {
                        $this->misc_model->add_attachment_to_database($id, 'task_templates', [$file]);
                    }
                }
                $success = $this->task_templates_model->update($data, $id);
                $message = '';
                if ($success) {
                    $message = _l('updated_successfully', _l('tt_task_template_lower'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id'      => $id,
                ]);
            }
            die;
        }

        $rel_type = $this->input->get("rel_type");
        $rel_id = $this->input->get("rel_id");
        $milestone_id = $this->input->get("milestone");
        $this->load->model("staff_model");
        $data['assignees'] = $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->model("tasks_model");
        $data['checklistTemplates'] = $this->tasks_model->get_checklist_templates();
        $assignees_ids = [];
        $followers_ids = [];

        if ($id == '') {
            $title = _l('add_new', _l('tt_task_template_lower'));
        } else {
            $data['task'] = $this->task_templates_model->get($id);
            $rel_type = $data['task']->rel_type;
            $rel_id = $data['task']->rel_id;
            $milestone_id = $data['task']->milestone;

            $assignees_ids = $data['task']->assignees_ids;
            $followers_ids = $data['task']->followers_ids;

            $title = _l('edit', _l('tt_task_template_lower')) . ' ' . $data['task']->name;
        }

        $data['assignees_ids'] = $assignees_ids;
        $data['followers_ids'] = $followers_ids;
        $data['rel_type'] = $rel_type;
        $data['rel_id'] = $rel_id;
        if($rel_type == "project" && module_is_active('project_templates')){
            $this->load->model("project_templates/project_templates_model");
            $project = $this->project_templates_model->get($rel_id);
            $data['project_template'] = $project;
            $milestones = $this->project_templates_model->get_milestones($rel_id);
            $data['milestones'] = $milestones;
            $data['milestone_id'] = $milestone_id;
            $data['assignees'] = $this->project_templates_model->get_project_template_members($rel_id, true);

            $data['other_tasks'] = $this->task_templates_model->get_tasks_for_dependent_task($rel_id, $id);
        }

        $data['id']    = $id;
        $data['title'] = $title;
        $this->load->view('new_task_template', $data);
    }

    public function get_template_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $tasks_where = [];
            if (!staff_can('view', 'task_templates')) {
                $tasks_where = get_task_templates_where_string(false);
            }
            $task = $this->task_templates_model->get($id, $tasks_where);
            if (!$task) {
                header('HTTP/1.0 404 Not Found');
                echo 'Task not found';
                die();
            }
            echo json_encode($task);
        }
    }

    public function table()
    {
        $this->app->get_table_data(module_views_path('task_templates', 'tables/task_templates'));
    }

    public function delete_task_template($id)
    {
        if (!staff_can('delete', 'task_templates')) {
            access_denied('task_templates');
        }
        $success = $this->task_templates_model->delete_task_template($id);
        $message = _l('problem_deleting', _l('tt_task_template_lower'));
        if ($success) {
            $message = _l('deleted', _l('tt_task_template_lower'));
            set_alert('success', $message);
        } else {
            set_alert('warning', $message);
        }

        if (strpos($_SERVER['HTTP_REFERER'], 'task_templates/index') !== false || strpos($_SERVER['HTTP_REFERER'], 'task_templates/view') !== false) {
            redirect(admin_url('task_templates'));
        } elseif (preg_match("/projects\/view\/[1-9]+/", $_SERVER['HTTP_REFERER'])) {
            $project_url = explode('?', $_SERVER['HTTP_REFERER']);
            redirect($project_url[0] . '?group=project_tasks');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * Remove task attachment
     * @since  Version 1.0.1
     * @param  mixed $id attachment it
     * @return json
     */
    public function remove_task_attachment($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->task_templates_model->remove_task_template_attachment($id));
        }
    }

    public function download_files($task_id, $comment_id = null)
    {
        $taskWhere = 'external IS NULL';

        if ($comment_id) {
            $taskWhere .= ' AND task_comment_id=' . $this->db->escape_str($comment_id);
        }

        if (!staff_can('view', 'task_templates')) {
            $taskWhere .= ' AND ' . get_task_templates_where_string(false);
        }

        $files = $this->task_templates_model->get_template_attachments($task_id, $taskWhere);

        if (count($files) == 0) {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $path = TASK_TEMPLATES_ATTACHMENTS_FOLDER . $task_id;

        $this->load->library('zip');

        foreach ($files as $file) {
            $this->zip->read_file($path . '/' . $file['file_name']);
        }

        $this->zip->download('files.zip');
        $this->zip->clear_data();
    }

    public function copy($task_id)
    {
        if (staff_can('create', 'task_templates')) {
            $new_task_id = $this->task_templates_model->copy($task_id);
            $message = _l('failed_to_copy_task_template');
            if ($new_task_id) {
                $message = _l('tt_task_template_copied_successfully');
                set_alert('success', $message);
            }
            else{
                set_alert('warning', $message);
            }
            $task = $this->task_templates_model->get($new_task_id);
            if($task->rel_type == "project"){
                redirect(admin_url('project_templates/view/'.$task->rel_id."?group=project_tasks"));
            }
            else{
                redirect(admin_url('task_templates'));
            }
        }
    }

    public function create_task(){
        if (!staff_can('create', 'tasks')) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data                = $this->input->post();

            $id      = $this->task_templates_model->create_task($data);
            $_id     = false;
            $success = false;
            $message = '';
            if ($id) {
                $success       = true;
                $_id           = $id;
                $message       = _l('added_successfully', _l('task_lowercase'));
                /*$uploadedFiles = handle_task_template_attachments_array($id);
                if ($uploadedFiles && is_array($uploadedFiles)) {
                    foreach ($uploadedFiles as $file) {
                        $this->misc_model->add_attachment_to_database($id, 'task_templates', [$file]);
                    }
                }*/
            }
            echo json_encode([
                'success' => $success,
                'id'      => $_id,
                'message' => $message,
            ]);

            die;
        }

        $data['task_templates'] = $this->task_templates_model->get_all("(rel_type='' OR rel_type IS NULL)");
        $data['id'] = $this->input->get('id');
        $title = _l('add_new', _l('tt_task_from_template'));
        $data['title'] = $title;
        $this->load->view('new_task', $data);
    }
}
