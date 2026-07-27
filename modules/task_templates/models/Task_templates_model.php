<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Task_templates_model extends App_Model
{

    public function __construct(){
        parent::__construct();

    }

    public function add($data)
    {
        $data['dateadded']             = date('Y-m-d H:i:s');
        $data['added_by']             = get_staff_user_id();

        if (isset($data['checklist_items']) && count($data['checklist_items']) > 0) {
            $data['checklist_items'] = implode(",", $data['checklist_items']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['cycles'] = !isset($data['cycles']) ? 0 : $data['cycles'];

        if (isset($data['is_public'])) {
            $data['is_public'] = 1;
        } else {
            $data['is_public'] = 0;
        }

        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }

        $data = hooks()->apply_filters('before_add_task_template', $data);

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }
        $assignees = '';
        if (isset($data['staff'])) {
            $assignees = $data['staff'];
            unset($data['staff']);
        }
        $followers = '';
        if (isset($data['followers'])) {
            $followers = $data['followers'];
            unset($data['followers']);
        }
        if ((!isset($data['milestone']) || $data['milestone'] == '') || (isset($data['milestone']) && $data['milestone'] == '')) {
            $data['milestone'] = 0;
        } else {
            if ($data['rel_type'] != 'project') {
                $data['milestone'] = 0;
            }
        }
        if (empty($data['rel_type'])) {
            $data['rel_id']   = null;
            $data['rel_type'] = null;
        } else {
            if (empty($data['rel_id'])) {
                $data['rel_id']   = null;
                $data['rel_type'] = null;
            }
        }
        if (isset($data['after_task_id']) && $data['after_task_id'] > 0) {
            $data['startdate'] = null;
            $data['startdate_type'] = null;
        }

        $this->db->insert(TASK_TEMPLATES_TABLE_NAME, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            handle_tags_save($tags, $insert_id, 'task_templates');
            if (isset($custom_fields)) {
                handle_custom_fields_post_for_task_templates($insert_id, $custom_fields);
            }
            if(is_array($assignees)){
                foreach ($assignees as $assignee){
                    $this->db->insert(TASK_TEMPLATES_ASSIGNEES_TABLE_NAME, [
                        'taskid'        => $insert_id,
                        'staffid'       => $assignee,
                    ]);
                }
            }
            if(is_array($followers)) {
                foreach ($followers as $follower) {
                    $this->db->insert(TASK_TEMPLATES_FOLLOWERS_TABLE_NAME, [
                        'taskid'  => $insert_id,
                        'staffid' => $follower,
                    ]);
                }
            }

            log_activity('New Task Template Added [ID:' . $insert_id . ', Name: ' . $data['name'] . ']');
            hooks()->do_action('after_add_task_template', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Update task data
     * @param  array $data task data $_POST
     * @param  mixed $id   task id
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows      = 0;

        if (isset($data['checklist_items']) && count($data['checklist_items']) > 0) {
            $data['checklist_items'] = implode(",", $data['checklist_items']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post_for_task_templates($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        $data['cycles'] = !isset($data['cycles']) ? 0 : $data['cycles'];

        if (isset($data['is_public'])) {
            $data['is_public'] = 1;
        } else {
            $data['is_public'] = 0;
        }
        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }
        if ((!isset($data['milestone']) || $data['milestone'] == '') || (isset($data['milestone']) && $data['milestone'] == '')) {
            $data['milestone'] = 0;
        } else {
            if ($data['rel_type'] != 'project') {
                $data['milestone'] = 0;
            }
        }
        if (empty($data['rel_type'])) {
            $data['rel_id']   = null;
            $data['rel_type'] = null;
        } else {
            if (empty($data['rel_id'])) {
                $data['rel_id']   = null;
                $data['rel_type'] = null;
            }
        }

        $data = hooks()->apply_filters('before_update_task_template', $data, $id);

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'task_templates')) {
                $affectedRows++;
            }
            unset($data['tags']);
        }
        $assignees = '';
        if (isset($data['staff'])) {
            $assignees = $data['staff'];
            unset($data['staff']);
        }
        $followers = '';
        if (isset($data['followers'])) {
            $followers = $data['followers'];
            unset($data['followers']);
        }
        if ($data['duration_value'] == "") {
            $data['duration_value'] = null;
        }
        if (isset($data['after_task_id']) && $data['after_task_id'] > 0) {
            $data['startdate'] = null;
            $data['startdate_type'] = null;
        }

        if(is_array($assignees)){
            $this->db->where("taskid", $id)->delete(TASK_TEMPLATES_ASSIGNEES_TABLE_NAME);
            foreach ($assignees as $assignee){
                $affectedRows++;
                $this->db->insert(TASK_TEMPLATES_ASSIGNEES_TABLE_NAME, [
                    'taskid'        => $id,
                    'staffid'       => $assignee,
                ]);
            }
        }
        if(is_array($followers)) {
            $this->db->where("taskid", $id)->delete(TASK_TEMPLATES_FOLLOWERS_TABLE_NAME);
            foreach ($followers as $follower) {
                $affectedRows++;
                $this->db->insert(TASK_TEMPLATES_FOLLOWERS_TABLE_NAME, [
                    'taskid'  => $id,
                    'staffid' => $follower,
                ]);
            }
        }

        $this->db->where('id', $id);
        $this->db->update(TASK_TEMPLATES_TABLE_NAME, $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            log_activity('Task Template Updated [ID:' . $id . ', Name: ' . $data['name'] . ']');
            hooks()->do_action('after_update_task_template', $id);
        }

        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get task by id
     * @param  mixed $id task id
     * @return object
     */
    public function get($id, $where = [])
    {
        $this->db->where('id', $id);
        $this->db->where($where);
        $task = $this->db->get(TASK_TEMPLATES_TABLE_NAME)->row();
        if ($task) {
            $task->assignees     = $this->get_template_assignees($id);
            $task->assignees_ids = [];

            foreach ($task->assignees as $follower) {
                array_push($task->assignees_ids, $follower['assigneeid']);
            }

            $task->followers     = $this->get_template_followers($id);
            $task->followers_ids = [];
            foreach ($task->followers as $follower) {
                array_push($task->followers_ids, $follower['followerid']);
            }

            $task->attachments     = $this->get_template_attachments($id);
            if(!empty($task->checklist_items)){
                $task->checklist_items = explode(",", $task->checklist_items);
            }
            else{
                $task->checklist_items = [];
            }
        }

        return hooks()->apply_filters('get_task_template', $task);
    }

    public function get_all($where = "")
    {
        if(!staff_can('view', 'task_templates')){
            if(!empty($where))
                $where .= " AND ";
            $where .= " added_by=".get_staff_user_id();
        }
        $this->db->where($where);
        return $this->db->get(TASK_TEMPLATES_TABLE_NAME)->result_array();
    }

    public function get_tasks_for_dependent_task($project_id, $id = '', $where = [])
    {
        $exclude_ids = [$id];
        if(!empty($id)){
            $all_child_tasks = $this->db
                ->where("after_task_id >", "0")
                ->order_by("after_task_id")
                ->get(TASK_TEMPLATES_TABLE_NAME)
                ->result_array();
            foreach ($all_child_tasks as $child_task){
                if(in_array($child_task['after_task_id'], $exclude_ids)){
                    $exclude_ids[] = $child_task['id'];
                }
            }
        }
        if(!staff_can('view', 'task_templates')){
            $where['added_by'] = get_staff_user_id();
        }
        $this->db->where("rel_type", "project");
        $this->db->where("rel_id", $project_id);
        $this->db->where_not_in("id", $exclude_ids);
        $this->db->where($where);
        return $this->db->get(TASK_TEMPLATES_TABLE_NAME)->result_array();
    }

    /**
     * Get all task assigneed
     * @param  mixed $id task id
     * @return array
     */
    public function get_template_assignees($id)
    {
        $this->db->select('id,' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . '.staffid as assigneeid, CONCAT(firstname, " ", lastname) as full_name');
        $this->db->from(TASK_TEMPLATES_ASSIGNEES_TABLE_NAME);
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . '.staffid');
        $this->db->where('taskid', $id);
        $this->db->order_by('firstname', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Get all task followers
     * @param  mixed $id task id
     * @return array
     */
    public function get_template_followers($id)
    {
        $this->db->select('id,' . TASK_TEMPLATES_FOLLOWERS_TABLE_NAME . '.staffid as followerid, CONCAT(firstname, " ", lastname) as full_name');
        $this->db->from(TASK_TEMPLATES_FOLLOWERS_TABLE_NAME);
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . TASK_TEMPLATES_FOLLOWERS_TABLE_NAME . '.staffid');
        $this->db->where('taskid', $id);
        $this->db->order_by('firstname', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Get all task attachments
     * @param  mixed $taskid taskid
     * @return array
     */
    public function get_template_attachments($taskid, $where = [])
    {
        $this->db->select(implode(', ', prefixed_table_fields_array(db_prefix() . 'files')));
        $this->db->where(db_prefix() . 'files.rel_id', $taskid);
        $this->db->where(db_prefix() . 'files.rel_type', 'task_templates');

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }

        $this->db->join(TASK_TEMPLATES_TABLE_NAME, TASK_TEMPLATES_TABLE_NAME . '.id = ' . db_prefix() . 'files.rel_id');
        $this->db->order_by(db_prefix() . 'files.dateadded', 'desc');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }

    public function delete_tasks_of_project_template($id){
        $tasks = $this->db->where("rel_type", "project")->where("rel_id", $id)->get(TASK_TEMPLATES_TABLE_NAME)->result_array();
        foreach($tasks as $task){
            $this->delete_task_template($task['id']);
        }
    }

    /**
     * Delete task and all connections
     * @param  mixed $id taskid
     * @return boolean
     */
    public function delete_task_template($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(TASK_TEMPLATES_TABLE_NAME);
        if ($this->db->affected_rows() > 0) {

            $this->db->where('taskid', $id);
            $this->db->delete(TASK_TEMPLATES_FOLLOWERS_TABLE_NAME);

            $this->db->where('taskid', $id);
            $this->db->delete(TASK_TEMPLATES_ASSIGNEES_TABLE_NAME);

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'task_templates');
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'task_templates');
            $attachments = $this->db->get(db_prefix() . 'files')->result_array();
            foreach ($attachments as $at) {
                $this->remove_task_template_attachment($at['id']);
            }

            if (is_dir(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $id)) {
                delete_dir(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $id);
            }

            hooks()->do_action('task_template_deleted', $id);

            return true;
        }

        return false;
    }

    /**
     * Remove task template attachment from server and database
     * @param  mixed $id attachmentid
     * @return boolean
     */
    public function remove_task_template_attachment($id)
    {
        $comment_removed = false;
        $deleted         = false;
        // Get the attachment
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'files')->row();

        if ($attachment) {
            if (empty($attachment->external)) {
                $relPath  = TASK_TEMPLATES_ATTACHMENTS_FOLDER . $attachment->rel_id . '/';
                $fullPath = $relPath . $attachment->file_name;
                unlink($fullPath);
                $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $relPath . $fname . '_thumb.' . $fext;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Task Template Attachment Deleted [TaskTemplateID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $attachment->rel_id);
                }
            }
        }

        return ['success' => $deleted];
    }

    public function copy_tasks_of_project($project_id, $new_project_id, $milestone_id = 0, $new_milestone_id = null, $postfix = null){
        $_tasks = $this->db->where("rel_type", "project")->where("rel_id", $project_id);
        if($milestone_id > 0){
            $_tasks->where("milestone", $milestone_id);
        }
        else{
            $this->db->group_start();
            $_tasks->where("milestone", "0");
            $_tasks->or_where("milestone IS NULL");
            $this->db->group_end();
        }
        $tasks = $_tasks->get(TASK_TEMPLATES_TABLE_NAME)->result_array();
        foreach($tasks as $task){
            $this->copy($task['id'], $new_project_id, $new_milestone_id, $postfix);
        }
    }

    public function copy($task_id, $new_project_id = null, $new_milestone_id = null, $postfix = null)
    {
        $task           = $this->get($task_id);
        $fields_tasks   = $this->db->list_fields(TASK_TEMPLATES_TABLE_NAME);
        $_new_task_data = [];

        foreach ($fields_tasks as $field) {
            if (isset($task->$field)) {
                $_new_task_data[$field] = $task->$field;
            }
        }

        unset($_new_task_data['id']);

        if (isset($task->checklist_items)) {
            $_new_task_data['checklist_items'] = implode(",", $task->checklist_items);
        }

        if(is_null($postfix)){
            $postfix = ' '._l('copy');
        }
        $_new_task_data['name']             = $task->name.$postfix;
        $_new_task_data['dateadded']             = date('Y-m-d H:i:s');
        $_new_task_data['added_by']             = get_staff_user_id();
        if($task->rel_type == "project"){
            if(!empty($new_project_id))
                $_new_task_data['rel_id']             = $new_project_id;
            if(!empty($new_milestone_id))
                $_new_task_data['milestone']             = $new_milestone_id;
        }

        $_new_task_data = hooks()->apply_filters('before_add_task_template', $_new_task_data);

        $this->db->insert(TASK_TEMPLATES_TABLE_NAME, $_new_task_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $tags = get_tags_in($task_id, 'task_templates');
            $_new_tags = !empty($tags) ? implode(",", $tags) : '';
            handle_tags_save($_new_tags, $insert_id, 'task_templates');

            if(is_array($task->assignees)){
                foreach ($task->assignees as $assignee){
                    $this->db->insert(TASK_TEMPLATES_ASSIGNEES_TABLE_NAME, [
                        'taskid'        => $insert_id,
                        'staffid'       => $assignee['assigneeid'],
                    ]);
                }
            }
            if(is_array($task->followers)) {
                foreach ($task->followers as $follower) {
                    $this->db->insert(TASK_TEMPLATES_FOLLOWERS_TABLE_NAME, [
                        'taskid'  => $insert_id,
                        'staffid' => $follower['followerid'],
                    ]);
                }
            }



            $attachments = $this->get_template_attachments($task_id);

            if (is_dir(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $task_id)) {
                xcopy(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $task_id, TASK_TEMPLATES_ATTACHMENTS_FOLDER . $insert_id);
            }

            foreach ($attachments as $at) {
                $_at      = [];
                $_at[]    = $at;
                $external = false;
                if (!empty($at['external'])) {
                    $external       = $at['external'];
                    $_at[0]['name'] = $at['file_name'];
                    $_at[0]['link'] = $at['external_link'];
                    if (!empty($at['thumbnail_link'])) {
                        $_at[0]['thumbnailLink'] = $at['thumbnail_link'];
                    }
                }
                $file_id = $this->misc_model->add_attachment_to_database($insert_id, 'task_templates', $_at, $external);
            }

            $this->copy_task_template_custom_fields($task_id, $insert_id);

            hooks()->do_action('after_add_task_template', $insert_id);

            return $insert_id;
        }

        return false;
    }

    public function copy_task_template_custom_fields($from_task, $to_task)
    {
        $custom_fields = get_custom_fields('tasks');
        foreach ($custom_fields as $field) {
            $value = get_custom_field_value($from_task, $field['id'], 'tasks', false);
            if ($value != '') {
                $this->db->insert(TASK_TEMPLATES_CUSTOM_FIELD_VALUES, [
                    'relid'   => $to_task,
                    'fieldid' => $field['id'],
                    'fieldto' => 'tasks',
                    'value'   => $value,
                ]);
            }
        }
    }

    public function create_task($data){
        $task_template = $this->get($data['task_template']);

        // fields from post data
        $_new_task_data['startdate'] = $data['startdate'];
        $_new_task_data['rel_type'] = $data['rel_type'];
        $_new_task_data['rel_id'] = $data['rel_id'];
        $_new_task_data['milestone'] = $data['milestone'];
        if(isset($data['visible_to_client']))
            $_new_task_data['visible_to_client'] = 1;
        else
            $_new_task_data['visible_to_client'] = 0;


        // fields from template
        $_new_task_data['duedate'] = '';
        if(!empty($task_template->duration_value)){
            $duration_value = intval($task_template->duration_value);
            $_new_task_data['duedate'] = _d(date("Y-m-d", strtotime("+".$duration_value." days", strtotime(to_sql_date($_new_task_data['startdate'])))));
        }
        $_new_task_data['is_public'] = $task_template->is_public;
        $_new_task_data['billable'] = $task_template->billable;
        $_new_task_data['name'] = $task_template->name;
        $_new_task_data['hourly_rate'] = $task_template->hourly_rate;
        $_new_task_data['priority'] = $task_template->priority;
        $_new_task_data['description'] = $task_template->description;
        $_new_task_data['repeat_every'] = $task_template->repeat_every;
        $_new_task_data['repeat_every_custom'] = $task_template->repeat_every_custom;
        $_new_task_data['repeat_type_custom'] = $task_template->repeat_type_custom;
        $_new_task_data['cycles'] = $task_template->cycles;

        if (isset($task_template->checklist_items) && count($task_template->checklist_items) > 0) {
            $_new_task_data['checklist_items'] = implode(",", $task_template->checklist_items);
        }

        $tags = get_tags_in($task_template->id, 'task_templates');
        $_new_task_data['tags'] = !empty($tags) ? implode(",", $tags) : '';

        $this->load->model("tasks_model");
        $inserted_task_id = $this->tasks_model->add($_new_task_data);

        if($inserted_task_id > 0){
            $this->insert_task_custom_fields($task_template->id, $inserted_task_id);

            foreach($task_template->assignees_ids as $assignees_id){
                if(total_rows(db_prefix() . 'task_assigned', ['taskid' => $inserted_task_id, 'staffid' => $assignees_id]) == 0){
                    $new_task_assignee_data = [
                        'taskid'        => $inserted_task_id,
                        'assignee'       => $assignees_id,
                    ];
                    $insert_assignee_id = $this->tasks_model->add_task_assignees($new_task_assignee_data);
                }
            }
            foreach($task_template->followers_ids as $followers_id){
                if(total_rows(db_prefix() . 'task_followers', ['taskid' => $inserted_task_id, 'staffid' => $followers_id]) == 0){
                    $new_task_follower_data = [
                        'taskid'        => $inserted_task_id,
                        'follower'       => $followers_id,
                    ];
                    $insert_follower_id = $this->tasks_model->add_task_followers($new_task_follower_data);
                }
            }

            $attachments = $this->get_template_attachments($task_template->id);

            if (is_dir(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $task_template->id)) {
                xcopy(TASK_TEMPLATES_ATTACHMENTS_FOLDER . $task_template->id, get_upload_path_by_type('task') . $inserted_task_id);
            }

            foreach ($attachments as $at) {
                $_at      = [];
                $_at[]    = $at;
                $external = false;
                if (!empty($at['external'])) {
                    $external       = $at['external'];
                    $_at[0]['name'] = $at['file_name'];
                    $_at[0]['link'] = $at['external_link'];
                    if (!empty($at['thumbnail_link'])) {
                        $_at[0]['thumbnailLink'] = $at['thumbnail_link'];
                    }
                }
                $this->misc_model->add_attachment_to_database($inserted_task_id, 'task', $_at, $external);
            }

            if(module_is_active('project_templates')){
                $_dependent_tasks = $this->db
                    ->where("after_task_id", $task_template->id)
                    ->where("rel_type", "project")
                    ->where("rel_id", $task_template->rel_id);
                /*if(!empty($task_template->milestone)){
                    $_dependent_tasks->where("milestone", $task_template->milestone);
                }
                else{
                    $_dependent_tasks->where("(milestone IS NULL OR milestone=0)");
                }*/
                $dependent_tasks = $_dependent_tasks->get(TASK_TEMPLATES_TABLE_NAME)
                    ->result_array();
                foreach($dependent_tasks as $dependent_task){
                    $new_dependent_task_data['task_template_id'] = $task_template->id;
                    $new_dependent_task_data['real_task_id'] = $inserted_task_id;
                    $new_dependent_task_data['next_task_template_id'] = $dependent_task['id'];
                    $this->add_to_queue($new_dependent_task_data);
                }
            }
        }

        return $inserted_task_id;
    }

    public function add_to_queue($data)
    {
        $data['dateadded']             = date('Y-m-d H:i:s');
        $data['added_by']             = get_staff_user_id();

        $this->db->insert(TASK_TEMPLATES_QUE_TABLE_NAME, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function insert_task_custom_fields($from_task_template, $to_task)
    {
        $custom_fields = get_custom_fields('tasks');
        foreach ($custom_fields as $field) {
            $value = get_task_template_custom_field_value($from_task_template, $field['id'], false);
            if ($value != '') {
                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'relid'   => $to_task,
                    'fieldid' => $field['id'],
                    'fieldto' => 'tasks',
                    'value'   => $value,
                ]);
            }
        }
    }
}
