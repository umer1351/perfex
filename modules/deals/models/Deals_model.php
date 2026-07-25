<?php

use app\services\AbstractKanban;

defined('BASEPATH') or exit('No direct script access allowed');

class Deals_model extends App_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;
    protected $_primary_filter = 'intval';


    public function dealInfo($id)
    {
        $deal = join_data('tbl_deals', 'tbl_deals.*,CONCAT(firstname, " ", lastname) as full_name,tbl_deals_source.source_name,tbl_deals_pipelines.pipeline_name,tbl_deals_stages.stage_name', array('id' => $id), array(db_prefix() . 'staff' => db_prefix() . 'staff.staffid = tbl_deals.default_deal_owner', 'tbl_deals_stages' => 'tbl_deals_stages.stage_id = tbl_deals.stage_id', 'tbl_deals_pipelines' => 'tbl_deals.pipeline_id = tbl_deals_pipelines.pipeline_id', 'tbl_deals_source' => 'tbl_deals.source_id = tbl_deals_source.source_id'));
        $deal->comments = $this->get_deal_comments($id);
        $deal->assignees = $this->get_deal_assignees($deal);
        $deal->customers = $this->get_deal_customers($deal);
        $deal->attachments = $this->get_deal_attachments($id);
        return $deal;
    }

    public function get_deal_customers($deal)
    {

        // if $deal is numeric, then it's an id, otherwise it's an object
        if (is_numeric($deal)) {
            $deal = $this->get($deal);
        }

        if (!empty($deal->rel_id) && !empty($deal->rel_type)) {
            $task_rel_data = get_relation_data($deal->rel_type, $deal->rel_id);
            $task_rel_value = get_relation_values($task_rel_data, $deal->rel_type);
            return $task_rel_value;
        }
    }


    /**
     * Get all task attachments
     * @param mixed $deal_id deal_id
     * @return array
     */

    public function get_deal_attachments($deal_id, $where = [])
    {
        $this->db->select(implode(', ', prefixed_table_fields_array(db_prefix() . 'files')) . ', ' . db_prefix() . '_deals_comments.id as comment_file_id');
        $this->db->where(db_prefix() . 'files.rel_id', $deal_id);
        $this->db->where(db_prefix() . 'files.rel_type', 'deal');

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }

        $this->db->join(db_prefix() . '_deals_comments', db_prefix() . '_deals_comments.file_id = ' . db_prefix() . 'files.id', 'left');
        $this->db->join(db_prefix() . '_deals', db_prefix() . '_deals.id = ' . db_prefix() . 'files.rel_id');
        $this->db->order_by(db_prefix() . 'files.dateadded', 'desc');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }


    public function get_deal_assignees($deal)
    {
        if (empty($deal->user_id)) {
            return [];
        }

        $user_id = json_decode($deal->user_id, true);
        if (!is_array($user_id)) {
            return [];
        }

        $user_id = array_values(array_filter(array_map('intval', $user_id)));
        if (empty($user_id)) {
            return [];
        }

        $this->db->select('staffid,firstname,lastname,CONCAT(firstname, " ", lastname) as full_name');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where_in('staffid', $user_id);

        return $this->db->get()->result_array();
    }

    public function get_deal_comments($id)
    {
        $task_comments_order = hooks()->apply_filters('task_comments_order', 'DESC');
        $this->db->select('id,dateadded,content,' . db_prefix() . 'staff.firstname,' . db_prefix() . 'staff.lastname,' . db_prefix() . '_deals_comments.staffid,' . db_prefix() . '_deals_comments.contact_id as contact_id,file_id,CONCAT(firstname, " ", lastname) as staff_full_name');
        $this->db->from(db_prefix() . '_deals_comments');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . '_deals_comments.staffid', 'left');
        $this->db->where('deal_id', $id);
        $this->db->order_by('dateadded', $task_comments_order);

        $comments = $this->db->get()->result_array();

        $ids = [];
        foreach ($comments as $key => $comment) {
            array_push($ids, $comment['id']);
            $comments[$key]['attachments'] = [];
        }

        if (count($ids) > 0) {
            $allAttachments = $this->get_deal_attachments($id, 'deal_comment_id IN (' . implode(',', $ids) . ')');
            foreach ($comments as $key => $comment) {
                foreach ($allAttachments as $attachment) {
                    if ($comment['id'] == $attachment['deal_comment_id']) {
                        $comments[$key]['attachments'][] = $attachment;
                    }
                }
            }
        }

        return $comments;
    }


    public function add_attachment_to_database($rel_id, $rel_type, $attachment, $external = false)
    {
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['rel_id'] = $rel_id;
        if (!isset($attachment[0]['staffid'])) {
            $data['staffid'] = get_staff_user_id();
        } else {
            $data['staffid'] = $attachment[0]['staffid'];
        }

        if (isset($attachment[0]['deal_comment_id'])) {
            $data['deal_comment_id'] = $attachment[0]['deal_comment_id'];
        }

        $data['rel_type'] = $rel_type;

        if (isset($attachment[0]['contact_id'])) {
            $data['contact_id'] = $attachment[0]['contact_id'];
            $data['visible_to_customer'] = 1;
            if (isset($data['staffid'])) {
                unset($data['staffid']);
            }
        }

        $data['attachment_key'] = app_generate_hash();

        if ($external == false) {
            $data['file_name'] = $attachment[0]['file_name'];
            $data['filetype'] = $attachment[0]['filetype'];
        } else {
            $path_parts = pathinfo($attachment[0]['name']);
            $data['file_name'] = $attachment[0]['name'];
            $data['external_link'] = $attachment[0]['link'];
            $data['filetype'] = !isset($attachment[0]['mime']) ? get_mime_by_extension('.' . $path_parts['extension']) : $attachment[0]['mime'];
            $data['external'] = $external;
            if (isset($attachment[0]['thumbnailLink'])) {
                $data['thumbnail_link'] = $attachment[0]['thumbnailLink'];
            }
        }

        $this->db->insert(db_prefix() . 'files', $data);
        $insert_id = $this->db->insert_id();

        if ($data['rel_type'] == 'customer' && isset($data['contact_id'])) {
            if (get_option('only_own_files_contacts') == 1) {
                $this->db->insert(db_prefix() . 'shared_customer_files', [
                    'file_id' => $insert_id,
                    'contact_id' => $data['contact_id'],
                ]);
            } else {
                $this->db->select('id');
                $this->db->where('userid', $data['rel_id']);
                $contacts = $this->db->get(db_prefix() . 'contacts')->result_array();
                foreach ($contacts as $contact) {
                    $this->db->insert(db_prefix() . 'shared_customer_files', [
                        'file_id' => $insert_id,
                        'contact_id' => $contact['id'],
                    ]);
                }
            }
        }

        return $insert_id;
    }

    public function edit_comment($data)
    {
        // Check if user really creator
        $this->db->where('id', $data['id']);
        $comment = $this->db->get('tbl_deals_comments')->row();
        if ($comment->staffid == get_staff_user_id() || has_permission('deals', '', 'edit') || $comment->contact_id == get_contact_user_id()) {
            $comment_added = strtotime($comment->dateadded);
            $minus_1_hour = strtotime('-1 hours');
            if (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 1 && $comment_added >= $minus_1_hour) || is_admin()) {
                if (total_rows(db_prefix() . 'files', ['deal_comment_id' => $comment->id]) > 0) {
                    $data['content'] .= '[deal_attachment]';
                }

                $this->db->where('id', $data['id']);
                $this->db->update('tbl_deals_comments', [
                    'content' => $data['content'],
                ]);
                if ($this->db->affected_rows() > 0) {
                    return true;
                }
            } else {
                return false;
            }

            return false;
        }
    }

    /**
     * Remove task comment from database
     * @param mixed $id task id
     * @return boolean
     */
    public function remove_comment($id, $force = false)
    {
        // Check if user really creator
        $this->db->where('id', $id);
        $comment = $this->db->get('tbl_deals_comments')->row();

        if (!$comment) {
            return true;
        }

        if ($comment->staffid == get_staff_user_id() || has_permission('deals', '', 'delete') || $comment->contact_id == get_contact_user_id() || $force === true) {
            $comment_added = strtotime($comment->dateadded);
            $minus_1_hour = strtotime('-1 hours');
            if (
                get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 1 && $comment_added >= $minus_1_hour)
                || (is_admin() || $force === true)
            ) {
                $this->db->where('id', $id);
                $this->db->delete('tbl_deals_comments');

                if ($this->db->affected_rows() > 0) {
                    if ($comment->file_id != 0) {
                        $this->remove_deal_attachment($comment->file_id);
                    }

                    $commentAttachments = $this->get_deal_attachments($comment->deal_id, 'deal_comment_id=' . $id);
                    foreach ($commentAttachments as $attachment) {
                        $this->remove_deal_attachment($attachment['id']);
                    }

                    return true;
                }
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Remove task attachment from server and database
     * @param mixed $id attachmentid
     * @return boolean
     */
    public function remove_deal_attachment($id)
    {
        $comment_removed = false;
        $deleted = false;
        // Get the attachment
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'files')->row();

        if ($attachment) {
            if (empty($attachment->external)) {
                $relPath = get_upload_path_for_deal() . $attachment->rel_id . '/';
                $fullPath = $relPath . $attachment->file_name;
                unlink($fullPath);
                $fname = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $relPath . $fname . '_thumb.' . $fext;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Deal Attachment Deleted [DealID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_for_deal() . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_for_deal() . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_for_deal() . $attachment->rel_id);
                }
            }
        }

        if ($deleted) {
            if ($attachment->deal_comment_id != 0) {
                $total_comment_files = total_rows(db_prefix() . 'files', ['deal_comment_id' => $attachment->deal_comment_id]);
                if ($total_comment_files == 0) {
                    $this->db->where('id', $attachment->deal_comment_id);
                    $comment = $this->db->get('tbl_deals_comments')->row();

                    if ($comment) {
                        // Comment is empty and uploaded only with attachments
                        // Now all attachments are deleted, we need to delete the comment too
                        if (empty($comment->content) || $comment->content === '[deal_attachment]') {
                            $this->db->where('id', $attachment->deal_comment_id);
                            $this->db->delete('tbl_deals_comments');
                            $comment_removed = $comment->id;
                        } else {
                            $this->db->query("UPDATE tbl_deals_comments
                            SET content = REPLACE(content, '[deal_attachment]', '')
                            WHERE id = " . $attachment->deal_comment_id);
                        }
                    }
                }
            }

            $this->db->where('file_id', $id);
            $comment_attachment = $this->db->get('tbl_deals_comments')->row();

            if ($comment_attachment) {
                $this->remove_comment($comment_attachment->id);
            }
        }

        return ['success' => $deleted, 'comment_removed' => $comment_removed];
    }

    function get_deal_cost($deals_id)
    {
        $this->db->select_sum('total_cost');
        $this->db->where('deals_id', $deals_id);
        $this->db->from('tbl_deals_items');
        $query_result = $this->db->get();
        $cost = $query_result->row();
        if (!empty($cost->total_cost)) {
            $result = $cost->total_cost;
        } else {
            $result = '0';
        }
        return $result;
    }

    public function staff_can_access_deal($id, $staff_id = '')
    {
        $staff_id = $staff_id == '' ? get_staff_user_id() : $staff_id;

        if (has_permission('deals', '', 'view')) {
            return true;
        }

        $deal = $this->db->where('id', $id)->get('tbl_deals')->row();
        if (!$deal) {
            return false;
        }

        if ((int) $deal->default_deal_owner === (int) $staff_id) {
            return true;
        }

        $assignees = !empty($deal->user_id) ? json_decode($deal->user_id, true) : [];

        return is_array($assignees) && in_array((int) $staff_id, array_map('intval', $assignees), true);
    }

    public function get_comment_details($module_id, $module = null, $relpy_id = null)
    {
        // get all data from tbltask_comments and tbl_users and assign the data to array and order by comment_datetime
        $this->commentsJoinStart();
        if (empty($module)) {
            if ($relpy_id) {
                $this->db->where('tbltask_comments.id', $module_id);
            } else {
                $this->db->where('tbltask_comments.contact_id', $module_id);
            }
        } else {
            // $this->db->where('tbltask_comments.module', $module);
            // $this->db->where('tbltask_comments.module_field_id', $module_id);
            // $this->db->where('tbltask_comments.id', '0');
            // $this->db->where('tbltask_comments.attachments_id', '0');
            // $this->db->where('tbltask_comments.file_id', '0');
        }
        $result = $this->commentsJoinEnd();
        return $result;
    }

    private function commentsJoinEnd()
    {
        $this->db->order_by('tbltask_comments.id', 'desc');
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    private function commentsJoinStart()
    {
        $this->db->select('tbltask_comments.*', FALSE);
        $this->db->select('tblstaff.firstname,tblstaff.lastname,tblstaff.profile_image', FALSE);
        $this->db->from('tbltask_comments');
        $this->db->join('tblstaff', 'tblstaff.staffid = tbltask_comments.deal_id', 'left');
    }

    public
    function check_deals_update($table, $where, $id = Null)
    {
        $this->db->select('*', FALSE);
        $this->db->from($table);
        if ($id != null) {
            $this->db->where($id);
        }
        $this->db->where($where);
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    public
    function deals_array_from_post($fields)
    {
        $data = array();
        foreach ($fields as $field) {
            $data[$field] = $this->input->post($field, true);
        }
        return $data;
    }

    public
    function save_deals($data, $id = NULL)
    {
        // Insert
        if ($id === NULL) {
            !isset($data[$this->_primary_key]) || $data[$this->_primary_key] = NULL;
            $this->db->set($data);
            $this->db->insert($this->_table_name);
            $id = $this->db->insert_id();
        } // Update
        else {
            $filter = $this->_primary_filter;
            $id = $filter($id);
            $this->db->set($data);
            $this->db->where($this->_primary_key, $id);
            $this->db->update($this->_table_name);
        }
        return $id;
    }

    function check_by_deals($where, $tbl_name)
    {

        $this->db->select('*');
        $this->db->from($tbl_name);
        $this->db->where($where);
        $query_result = $this->db->get();
        $result = $query_result->row();
        return $result;
    }

    public function staff_query($table)
    {
        $role = $this->session->userdata('user_type');
        $userid = $this->input->post('user_id', true);
        if ($role == 3 || !empty($userid)) {
            if (empty($userid)) {
                $userid = my_id();
            }
            if (!empty($this->db->field_exists('permission', $table))) {
                $this->db->group_start();
                if ($this->db->version() >= 8) {
                    $sq = $this->db->escape('\\b' . ($userid) . '\\b');
                } else {
                    $sq = $this->db->escape('[[:<:]]' . ($userid) . '[[:>:]]');
                }
                $this->db->where($table . '.permission REGEXP', $sq, false);
                $this->db->or_where(array($table . '.permission' => 'all'));
                $this->db->or_where(array($table . '.permission' => NULL));
                $this->db->group_end(); //close bracket
            }
        }
    }

    public function check_deals_by($where, $tbl_name)
    {

        $this->db->select('*');
        $this->db->from($tbl_name);
        $this->db->where($where);
        $query_result = $this->db->get();
        $result = $query_result->row();
        return $result;
    }

    public function delete_deals($id)
    {
        $filter = $this->_primary_filter;
        $id = $filter($id);
        if (!$id) {
            return FALSE;
        }
        $this->db->where($this->_primary_key, $id);
        $this->db->limit(1);
        $this->db->delete($this->_table_name);
    }

    public function getItemsInfo($term = null, $warehouse_id = null, $limit = 10)
    {
        $for_purcahse = $this->input->get('for', true);

        $table = db_prefix() . 'items';
        $this->db->select('*');
        if (!empty($term)) {
            $this->db->where("(description LIKE '%" . $term . "%' OR long_description LIKE '%" . $term . "%' OR  concat(description, ' (', long_description, ')') LIKE '%" . $term . "%')");
        }
        $this->db->limit($limit);
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get($table);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function get_attach_file($id, $module = null, $files_id = null)
    {

        // get all data from tbl_attachments and tbl_attachments_files and assign the data to array
        // $this->db->select('tbl_attachments.*', FALSE);
        $this->db->select('tblfiles.*', FALSE);
        $this->db->select('tblstaff.firstname,tblstaff.lastname', FALSE);
        $this->db->from('tblfiles');
        // $this->db->join('tbl_attachments_files', 'tbl_attachments_files.attachments_id = tbl_attachments.attachments_id', 'left');
        $this->db->join('tblstaff', 'tblstaff.staffid = tblfiles.rel_id', 'left');
        if (!empty($module) && empty($files_id)) {

            if ($module == 'g') {
                $this->db->where('tblfiles.attachments_id', $id);
            } else {
                $this->db->where('tblfiles.rel_type', $module);
                $this->db->where('tblfiles.rel_id', $id);
            }
            $query_result = $this->db->get();
            $result = $query_result->result();
            // assign the data to array using attachments_id as key
            if ($module != 'g') {
                $data = array();
                foreach ($result as $row) {
                    $data[$row->attachments_id][] = $row;
                }
            } else {
                $data = $result;
            }
        } else {
            $this->db->where('tblfiles.rel_id', $id);
            // if (!empty($files_id)) {
            //     $this->db->where('tbl_attachments_files.uploaded_files_id', $files_id);
            // }
            $query_result = $this->db->get();
            if (!empty($module) && $module == 'r') {
                $data[] = $query_result->row();
            } else {
                $data = $query_result->result();
            }
        }

        return $data;
    }

    public function count_rows($table, $where = null)
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    public function staff_can_access_deals($id, $staff_id = '')
    {
        return $this->staff_can_access_deal($id, $staff_id);
    }

    public function delete_deals_attachment($id)
    {
        $attachment = $this->get_deals_attachments('', $id);
        $deleted = false;

        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_for_deal() . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Deals Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_for_deal() . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_for_deal() . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_for_deal() . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    public function get($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('tbl_deals.id', $id);
            $deals = $this->db->get('tbl_deals')->row();
            if ($deals) {
                $deals->attachments = $this->get_deals_attachments($id);
            }

            return $deals;
        }

        return $this->db->get('tbl_deals')->result_array();
    }

    public function get_deals_attachments($id = '', $attachment_id = '', $where = [])
    {
        $this->db->where($where);
        $idIsHash = !is_numeric($attachment_id) && strlen($attachment_id) == 32;
        if (is_numeric($attachment_id) || $idIsHash) {
            $this->db->where($idIsHash ? 'attachment_key' : 'id', $attachment_id);

            return $this->db->get(db_prefix() . 'files')->row();
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'deal');
        $this->db->order_by('dateadded', 'DESC');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }

    public function add_attachment_deals_database($deals_id, $attachment, $external = false, $form_activity = false)
    {

        $this->misc_model->add_attachment_to_database($deals_id, 'deals', $attachment, $external);

        if ($form_activity == false) {
            $this->log_deals_activity($deals_id, 'not_deals_activity_added_attachment');
        } else {
            $this->log_deals_activity($deals_id, 'not_deals_activity_log_attachment', true, serialize([
                $form_activity,
            ]));
        }

        // No notification when attachment is imported from web to deals form
        if ($form_activity == false) {
            $deals = $this->get($deals_id);
            $not_user_ids = [];
            $assignees = !empty($deals->user_id) ? json_decode($deals->user_id) : [];
            foreach ($assignees as $userId) {

                if ($userId != get_staff_user_id()) {
                    array_push($not_user_ids, $userId);
                }
                if ($userId != get_staff_user_id() && $userId != 0) {
                    array_push($not_user_ids, $userId);
                }
            }
            $notifiedUsers = [];
            foreach ($not_user_ids as $uid) {
                $notified = add_notification([
                    'description' => 'not_deals_added_attachment',
                    'touserid' => $uid,
                    'link' => '#dealsid=' . $deals_id,
                    'additional_data' => serialize([
                        $deals->title,
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $uid);
                }
            }
            pusher_trigger_notification($notifiedUsers);
        }
    }

    /**
     * Add new task comment
     * @param array $data comment $_POST data
     * @return boolean
     */
    public function add_deal_comment($data)
    {
        if (is_client_logged_in()) {
            $data['staffid'] = 0;
            $data['contact_id'] = get_contact_user_id();
        } else {
            $data['staffid'] = get_staff_user_id();
            $data['contact_id'] = 0;
        }

        $this->db->insert('tbl_deals_comments', [
            'deal_id' => $data['deal_id'],
            'content' => is_client_logged_in() ? _strip_tags($data['content']) : $data['content'],
            'staffid' => $data['staffid'],
            'contact_id' => $data['contact_id'],
            'dateadded' => date('Y-m-d H:i:s'),
        ]);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->sync_deal_metrics($data['deal_id'], ['last_activity_at' => date('Y-m-d H:i:s')]);

            return $insert_id;
        }

        return false;
    }

    private function _send_deal_mentioned_users_notification($description, $deal_id, $staff, $email_template, $notification_data, $comment_id)
    {
        $staff = array_unique($staff, SORT_NUMERIC);

        $this->load->model('staff_model');
        $notifiedUsers = [];

        foreach ($staff as $staffId) {
            if (!is_client_logged_in()) {
                if ($staffId == get_staff_user_id()) {
                    continue;
                }
            }

            $member = $this->staff_model->get($staffId);

            $link = '#deal_id=' . $deal_id;

            if ($comment_id) {
                $link .= '#comment_' . $comment_id;
            }

            $notified = add_notification([
                'description' => $description,
                'touserid' => $member->staffid,
                'link' => $link,
                'additional_data' => $notification_data,
            ]);

            if ($notified) {
                array_push($notifiedUsers, $member->staffid);
            }

            if ($email_template != '') {
                send_mail_template($email_template, $member->email, $member->staffid, $deal_id);
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    public function log_deals_activity($id, $description, $integration = false, $additional_data = '')
    {
        $log = [
            'date' => date('Y-m-d H:i:s'),
            'description' => $description,
            'deal_id' => $id,
            'staffid' => get_staff_user_id(),
            'additional_data' => $additional_data,
            'full_name' => get_staff_full_name(get_staff_user_id()),
        ];
        if ($integration == true) {
            $log['staffid'] = 0;
            $log['full_name'] = '[CRON]';
        }

        $this->db->insert('tbl_deal_activity_log', $log);
        $this->sync_deal_metrics($id, ['last_activity_at' => $log['date']]);

        return $this->db->insert_id();
    }

    public function get_lead_activity_log($id)
    {
        $sorting = hooks()->apply_filters('deal_activity_log_default_sort', 'DESC');

        $this->db->where('deal_id', $id);
        $this->db->order_by('date', $sorting);

        return $this->db->get('tbl_deal_activity_log')->result_array();
    }

    public function array_from_post($fields)
    {
        $data = array();
        foreach ($fields as $field) {
            $data[$field] = $this->input->post($field, true);
        }
        return $data;
    }

    public function send_email($params)
    {
        $template = mail_template('deal_send_email', 'deals', array_to_object($params));
        $template->send();
    }

    public function update_deal_satges($data)
    {
        $this->db->select('stage_id');
        $this->db->where('id', $data['leadid']);
        $_old = $this->db->get(db_prefix() . '_deals')->row();
        $old_status = '';

        if ($_old) {
            $old_status = get_deals_row('tbl_deals_stages', ['stage_id' => $_old->stage_id]);
            if ($old_status) {
                $old_status = $old_status->stage_name;
            }
        }

        $affectedRows = 0;
        $current_status = get_deals_row('tbl_deals_stages', ['stage_id' => $data['status']])->stage_name;
        $probability = $this->calculate_probability_from_stage($data['leadid'], $data['status']);

        $this->db->where('id', $data['leadid']);
        $this->db->update(db_prefix() . '_deals', [
            'stage_id' => $data['status'],
            'probability' => $probability,
            'expected_revenue' => $this->calculate_expected_revenue($data['leadid'], $data['status'], $probability),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        $_log_message = '';

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if ($current_status != $old_status && $old_status != '') {
                $_log_message = 'not_deal_activity_status_updated';
                $additional_data = serialize([
                    get_staff_full_name(),
                    $old_status,
                    $current_status,
                ]);
            }
        }

        if (isset($data['order'])) {
            AbstractKanban::updateOrder($data['order'], 'dealorder', '_deals', $data['status']);
        }

        if ($affectedRows > 0) {
            if ($_log_message == '') {
                return true;
            }

            $this->log_deals_activity($data['leadid'], $_log_message, false, $additional_data);

            return true;
        }

        return false;
    }

    public function update_stage_order($data)
    {
        foreach ($data['order'] as $status) {
            $this->db->where('stage_id', $status[0]);
            $this->db->update('tbl_deals_stages', [
                'stage_order' => $status[1],
            ]);
        }
    }

    public function calculate_probability_from_stage($dealId, $stageId = null)
    {
        $deal = $this->db->where('id', $dealId)->get('tbl_deals')->row();
        if (!$deal) {
            return 0;
        }

        if ($deal->status === 'won') {
            return 100;
        }

        if ($deal->status === 'lost') {
            return 0;
        }

        $currentStageId = $stageId ?: $deal->stage_id;
        $stages = get_deals_order_by('tbl_deals_stages', ['pipeline_id' => $deal->pipeline_id], 'stage_order', 'asc');
        if (empty($stages)) {
            return 0;
        }

        $probability = 0;
        $totalStages = count($stages);
        foreach ($stages as $stage) {
            $probability += round(100 / $totalStages);
            if ((int) $stage->stage_id === (int) $currentStageId) {
                break;
            }
        }

        return max(0, min(100, $probability));
    }

    public function calculate_expected_revenue($dealId, $stageId = null, $probability = null)
    {
        $deal = $this->db->where('id', $dealId)->get('tbl_deals')->row();
        if (!$deal) {
            return 0;
        }

        $effectiveProbability = $probability;
        if ($effectiveProbability === null) {
            $effectiveProbability = $this->calculate_probability_from_stage($dealId, $stageId);
        }

        return round(((float) $deal->deal_value) * ((float) $effectiveProbability / 100), 2);
    }

    public function sync_deal_metrics($dealId, $overrides = [])
    {
        $deal = $this->db->where('id', $dealId)->get('tbl_deals')->row();
        if (!$deal) {
            return false;
        }

        $probability = array_key_exists('probability', $overrides) ? $overrides['probability'] : $this->calculate_probability_from_stage($dealId);
        $data = [
            'probability' => $probability,
            'expected_revenue' => array_key_exists('expected_revenue', $overrides)
                ? $overrides['expected_revenue']
                : round(((float) $deal->deal_value) * ((float) $probability / 100), 2),
            'last_activity_at' => array_key_exists('last_activity_at', $overrides)
                ? $overrides['last_activity_at']
                : ($deal->last_activity_at ?: date('Y-m-d H:i:s')),
        ];

        if (array_key_exists('last_contacted_at', $overrides)) {
            $data['last_contacted_at'] = $overrides['last_contacted_at'];
        }

        if (array_key_exists('next_follow_up_at', $overrides)) {
            $data['next_follow_up_at'] = $overrides['next_follow_up_at'];
        }

        $this->db->where('id', $dealId);
        $this->db->update('tbl_deals', $data);

        return true;
    }

    public function get_deal_followups($dealId)
    {
        $this->db->select('tbl_deals_followups.*, CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as owner_name');
        $this->db->from('tbl_deals_followups');
        $this->db->join('tblstaff', 'tblstaff.staffid = tbl_deals_followups.owner_id', 'left');
        $this->db->where('deal_id', $dealId);
        $this->db->order_by('follow_up_at', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_follow_up_queue($filters = [])
    {
        $this->db->select('tbl_deals_followups.*, tbl_deals.title as deal_title, tbl_deals.status as deal_status, tbl_deals.next_follow_up_at, CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as owner_name');
        $this->db->from('tbl_deals_followups');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_followups.deal_id', 'left');
        $this->db->join('tblstaff', 'tblstaff.staffid = tbl_deals_followups.owner_id', 'left');

        if (!empty($filters['status'])) {
            $this->db->where('tbl_deals_followups.status', $filters['status']);
        }

        if (!empty($filters['owner_id'])) {
            $this->db->where('tbl_deals_followups.owner_id', $filters['owner_id']);
        }

        if (!empty($filters['deal_status'])) {
            $this->db->where('tbl_deals.status', $filters['deal_status']);
        }

        $this->db->order_by('tbl_deals_followups.follow_up_at', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_reports_summary($filters = [])
    {
        $summary = [];

        $this->db->select('COALESCE(SUM(deal_value), 0) as total_pipeline, COALESCE(SUM(expected_revenue), 0) as weighted_pipeline', false);
        $this->db->from('tbl_deals');
        $this->db->where('status', 'open');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $pipelineSummary = $this->db->get()->row_array();

        $this->db->select('COALESCE(SUM(deal_value), 0) as won_value, COUNT(*) as won_count', false);
        $this->db->from('tbl_deals');
        $this->db->where('status', 'won');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $wonSummary = $this->db->get()->row_array();

        $this->db->select('COUNT(*) as open_deals, COALESCE(AVG(deal_value), 0) as avg_deal_size, COALESCE(AVG(DATEDIFF(COALESCE(last_activity_at, created_at), created_at)), 0) as avg_cycle_days', false);
        $this->db->from('tbl_deals');
        $this->db->where('status !=', 'lost');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $volumeSummary = $this->db->get()->row_array();

        $this->db->from('tbl_deals');
        $this->db->where('status', 'lost');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $lostCount = $this->db->count_all_results();

        $this->db->from('tbl_deals_followups');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_followups.deal_id', 'left');
        $this->db->where('tbl_deals_followups.status', 'pending');
        $this->db->where('tbl_deals_followups.follow_up_at <', date('Y-m-d H:i:s'));
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $overdueFollowups = $this->db->count_all_results();

        $this->db->from('tbl_deals');
        $this->db->where('days_to_close >=', date('Y-m-d'));
        $this->db->where('days_to_close <=', date('Y-m-d', strtotime('+30 days')));
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $closingSoon = $this->db->count_all_results();

        $wonCount = (int) ($wonSummary['won_count'] ?? 0);
        $lossBase = $wonCount + (int) $lostCount;
        $summary['total_pipeline'] = (float) ($pipelineSummary['total_pipeline'] ?? 0);
        $summary['weighted_pipeline'] = (float) ($pipelineSummary['weighted_pipeline'] ?? 0);
        $summary['won_this_month'] = (float) ($wonSummary['won_value'] ?? 0);
        $summary['overdue_followups'] = (int) $overdueFollowups;
        $summary['closing_next_30_days'] = (int) $closingSoon;
        $summary['open_deals'] = (int) ($volumeSummary['open_deals'] ?? 0);
        $summary['avg_deal_size'] = (float) ($volumeSummary['avg_deal_size'] ?? 0);
        $summary['avg_cycle_days'] = round((float) ($volumeSummary['avg_cycle_days'] ?? 0), 1);
        $summary['win_rate'] = $lossBase > 0 ? round(($wonCount / $lossBase) * 100, 1) : 0;

        return $summary;
    }

    public function get_stage_report($filters = [])
    {
        $this->db->select('tbl_deals_stages.stage_name, COUNT(tbl_deals.id) as total_deals, COALESCE(SUM(tbl_deals.deal_value), 0) as pipeline_value, COALESCE(SUM(tbl_deals.expected_revenue), 0) as weighted_value', false);
        $this->db->from('tbl_deals_stages');
        $this->db->join('tbl_deals', 'tbl_deals.stage_id = tbl_deals_stages.stage_id', 'left');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('tbl_deals_stages.stage_id');
        $this->db->order_by('tbl_deals_stages.stage_order', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_source_report($filters = [])
    {
        $this->db->select('tbl_deals_source.source_name, COUNT(tbl_deals.id) as total_deals, COALESCE(SUM(tbl_deals.deal_value), 0) as pipeline_value, COALESCE(SUM(CASE WHEN tbl_deals.status = "won" THEN tbl_deals.deal_value ELSE 0 END), 0) as won_value, ROUND((SUM(CASE WHEN tbl_deals.status = "won" THEN 1 ELSE 0 END) / NULLIF(COUNT(tbl_deals.id), 0)) * 100, 1) as win_rate', false);
        $this->db->from('tbl_deals_source');
        $this->db->join('tbl_deals', 'tbl_deals.source_id = tbl_deals_source.source_id', 'left');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('tbl_deals_source.source_id');
        $this->db->order_by('pipeline_value', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_owner_report($filters = [])
    {
        $this->db->select('CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as owner_name, COUNT(tbl_deals.id) as total_deals, COALESCE(SUM(tbl_deals.deal_value), 0) as pipeline_value, COALESCE(SUM(tbl_deals.expected_revenue), 0) as weighted_value, COALESCE(SUM(CASE WHEN tbl_deals.status = "won" THEN tbl_deals.deal_value ELSE 0 END), 0) as won_value', false);
        $this->db->from('tblstaff');
        $this->db->join('tbl_deals', 'tbl_deals.default_deal_owner = tblstaff.staffid', 'left');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('tblstaff.staffid');
        $this->db->order_by('pipeline_value', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_status_report($filters = [])
    {
        $this->db->select('status, COUNT(*) as total_deals, COALESCE(SUM(deal_value), 0) as total_value, COALESCE(SUM(expected_revenue), 0) as weighted_value', false);
        $this->db->from('tbl_deals');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('status');
        $this->db->order_by('total_deals', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_forecast_report($filters = [])
    {
        $this->db->select('forecast_category, COUNT(*) as total_deals, COALESCE(SUM(deal_value), 0) as total_value, COALESCE(SUM(expected_revenue), 0) as weighted_value', false);
        $this->db->from('tbl_deals');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('forecast_category');
        $this->db->order_by('weighted_value', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_health_report($filters = [])
    {
        $this->db->select('health_status, COUNT(*) as total_deals, COALESCE(SUM(deal_value), 0) as total_value', false);
        $this->db->from('tbl_deals');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('health_status');
        $this->db->order_by('total_deals', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_follow_up_performance_report($filters = [])
    {
        $this->db->select('tbl_deals_followups.status, COUNT(*) as total_items', false);
        $this->db->from('tbl_deals_followups');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_followups.deal_id', 'left');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('tbl_deals_followups.status');
        $rows = $this->db->get()->result_array();

        $summary = [
            'pending' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'overdue' => 0,
        ];

        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['total_items'];
        }

        $this->db->from('tbl_deals_followups');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_followups.deal_id', 'left');
        $this->db->where('tbl_deals_followups.status', 'pending');
        $this->db->where('tbl_deals_followups.follow_up_at <', date('Y-m-d H:i:s'));
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $summary['overdue'] = $this->db->count_all_results();

        return $summary;
    }

    public function get_monthly_revenue_report($filters = [], $months = 12)
    {
        $months = max((int) $months, 1);
        $startMonth = date('Y-m-01', strtotime('-' . ($months - 1) . ' months'));

        $this->db->select("DATE_FORMAT(COALESCE(last_activity_at, created_at), '%Y-%m') as report_month, COALESCE(SUM(deal_value), 0) as won_value, COUNT(*) as won_deals", false);
        $this->db->from('tbl_deals');
        $this->db->where('status', 'won');
        $this->db->where('COALESCE(last_activity_at, created_at) >=', $startMonth);
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->group_by('report_month');
        $this->db->order_by('report_month', 'ASC');
        $rows = $this->db->get()->result_array();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['report_month']] = $row;
        }

        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime('-' . $i . ' months'));
            $series[] = [
                'month_key' => $monthKey,
                'month_label' => date('M Y', strtotime($monthKey . '-01')),
                'won_value' => (float) ($indexed[$monthKey]['won_value'] ?? 0),
                'won_deals' => (int) ($indexed[$monthKey]['won_deals'] ?? 0),
            ];
        }

        return $series;
    }

    public function get_stage_aging_report($filters = [], $limit = 10)
    {
        $this->db->select('tbl_deals.id, tbl_deals.title, tbl_deals.status, tbl_deals.last_activity_at, tbl_deals.created_at, tbl_deals.deal_value, tbl_deals_stages.stage_name, DATEDIFF(CURDATE(), DATE(COALESCE(tbl_deals.last_activity_at, tbl_deals.created_at))) as aging_days', false);
        $this->db->from('tbl_deals');
        $this->db->join('tbl_deals_stages', 'tbl_deals_stages.stage_id = tbl_deals.stage_id', 'left');
        $this->db->where('tbl_deals.status', 'open');
        $this->apply_deal_report_filters($filters, 'tbl_deals');
        $this->db->order_by('aging_days', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    protected function apply_deal_report_filters($filters = [], $tableAlias = 'tbl_deals')
    {
        if (!empty($filters['pipeline_id'])) {
            $this->db->where($tableAlias . '.pipeline_id', $filters['pipeline_id']);
        }

        if (!empty($filters['owner_id'])) {
            $this->db->where($tableAlias . '.default_deal_owner', $filters['owner_id']);
        }

        if (!empty($filters['source_id'])) {
            $this->db->where($tableAlias . '.source_id', $filters['source_id']);
        }

        if (!empty($filters['status'])) {
            $this->db->where($tableAlias . '.status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $fromDate = date('Y-m-d', strtotime($filters['from_date']));
            $this->db->where('DATE(' . $tableAlias . '.created_at) >= ' . $this->db->escape($fromDate), null, false);
        }

        if (!empty($filters['to_date'])) {
            $toDate = date('Y-m-d', strtotime($filters['to_date']));
            $this->db->where('DATE(' . $tableAlias . '.created_at) <= ' . $this->db->escape($toDate), null, false);
        }
    }

    public function get_campaigns()
    {
        $this->db->select('tbl_deals_campaigns.*, COUNT(tbl_deals.id) as attributed_deals, COALESCE(SUM(tbl_deals.deal_value), 0) as attributed_value', false);
        $this->db->from('tbl_deals_campaigns');
        $this->db->join('tbl_deals', 'tbl_deals.campaign_id = tbl_deals_campaigns.id', 'left');
        $this->db->group_by('tbl_deals_campaigns.id');
        $this->db->order_by('tbl_deals_campaigns.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

}
