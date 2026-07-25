<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Analytics_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get overview analytics data (optionally filtered by date range and tag).
     */
    public function get_overview_data($date_range = null, $tag_id = null)
    {
        $dc = $this->build_date_condition($date_range);
        $tc = $this->build_tag_condition($tag_id);
        $tbl = db_prefix() . 'chatbot_conversations';

        $total = $this->db->query(
            "SELECT COUNT(*) as cnt FROM {$tbl} c WHERE 1=1 {$dc} {$tc}"
        )->row()->cnt;

        $rated = $this->db->query(
            "SELECT COUNT(*) as cnt FROM {$tbl} c WHERE c.csat_score IS NOT NULL {$dc} {$tc}"
        )->row()->cnt;

        $avg = $this->db->query(
            "SELECT AVG(c.csat_score) as avg_rating FROM {$tbl} c WHERE c.csat_score IS NOT NULL {$dc} {$tc}"
        )->row()->avg_rating;

        return [
            'total_conversations' => (int) $total,
            'rated_conversations' => (int) $rated,
            'average_rating' => $avg ? (float) $avg : 0,
            'rating_percentage' => $total > 0 ? ($rated / $total) * 100 : 0,
        ];
    }

    /**
     * Get rating breakdown 1-5 stars (optionally filtered by date range and tag).
     */
    public function get_rating_breakdown($date_range = null, $tag_id = null)
    {
        $dc = $this->build_date_condition($date_range);
        $tc = $this->build_tag_condition($tag_id);
        $tbl = db_prefix() . 'chatbot_conversations';

        $rows = $this->db->query(
            "SELECT c.csat_score, COUNT(*) as cnt
             FROM {$tbl} c
             WHERE c.csat_score IS NOT NULL {$dc} {$tc}
             GROUP BY c.csat_score"
        )->result();

        $breakdown = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($rows as $r) {
            $breakdown[(int) $r->csat_score] = (int) $r->cnt;
        }

        return $breakdown;
    }

    /**
     * Get staff performance data (optionally filtered by date range and tag).
     */
    public function get_staff_performance($date_range = null, $tag_id = null)
    {
        $dc = $this->build_date_condition($date_range);
        $tc = $this->build_tag_condition($tag_id);

        return $this->db->query("
            SELECT
                s.staffid,
                s.firstname,
                s.lastname,
                CONCAT(s.firstname, ' ', s.lastname) as staff_name,
                COUNT(c.id) as total_conversations,
                COUNT(c.csat_score) as rated_conversations,
                AVG(c.csat_score) as avg_rating
            FROM " . db_prefix() . "staff s
            INNER JOIN " . db_prefix() . "chatbot_conversations c
                ON c.assigned_staff_id = s.staffid AND c.is_escalated = 1
            WHERE 1=1 {$dc} {$tc}
            GROUP BY s.staffid, s.firstname, s.lastname
            HAVING total_conversations > 0
            ORDER BY avg_rating DESC, total_conversations DESC
        ")->result();
    }

    /**
     * Get top N staff by average rating (minimum 3 rated conversations).
     * Optionally filtered by date range and tag.
     */
    public function get_top_staff($limit = 5, $date_range = null, $tag_id = null)
    {
        $dc = $this->build_date_condition($date_range);
        $tc = $this->build_tag_condition($tag_id);
        return $this->db->query("
            SELECT
                s.staffid, s.firstname, s.lastname,
                COUNT(c.id) as total_conversations,
                COUNT(c.csat_score) as rated_conversations,
                AVG(c.csat_score) as avg_rating
            FROM " . db_prefix() . "staff s
            INNER JOIN " . db_prefix() . "chatbot_conversations c
                ON s.staffid = c.assigned_staff_id AND c.is_escalated = 1
            WHERE c.csat_score IS NOT NULL {$dc} {$tc}
            GROUP BY s.staffid
            HAVING COUNT(c.id) >= 3
            ORDER BY avg_rating DESC, rated_conversations DESC
            LIMIT " . (int) $limit . "
        ")->result();
    }

    /**
     * Get quick stats (today, week, month).
     */
    public function get_quick_stats()
    {
        $tbl = db_prefix() . 'chatbot_conversations';

        $today = $this->db->query(
            "SELECT COUNT(*) as cnt FROM {$tbl} WHERE DATE(created_at) = CURDATE() AND csat_score IS NOT NULL"
        )->row()->cnt;

        $yesterday = $this->db->query(
            "SELECT COUNT(*) as cnt FROM {$tbl} WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND csat_score IS NOT NULL"
        )->row()->cnt;

        $week_avg = $this->db->query(
            "SELECT AVG(csat_score) as v FROM {$tbl} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND csat_score IS NOT NULL"
        )->row()->v;

        $last_week_avg = $this->db->query(
            "SELECT AVG(csat_score) as v FROM {$tbl} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) AND csat_score IS NOT NULL"
        )->row()->v;

        $month_esc = $this->db->query(
            "SELECT COUNT(*) as cnt FROM {$tbl} WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') AND is_escalated = 1"
        )->row()->cnt;

        return [
            'today_ratings' => (int) $today,
            'yesterday_ratings' => (int) $yesterday,
            'this_week_avg' => $week_avg,
            'last_week_avg' => $last_week_avg,
            'month_escalations' => (int) $month_esc,
        ];
    }

    /**
     * Get best performing day in last 30 days.
     */
    public function get_best_day()
    {
        return $this->db->query("
            SELECT DATE(created_at) as date, AVG(csat_score) as avg_rating, COUNT(*) as count
            FROM " . db_prefix() . "chatbot_conversations
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              AND csat_score IS NOT NULL
            GROUP BY DATE(created_at)
            HAVING COUNT(*) >= 3
            ORDER BY avg_rating DESC
            LIMIT 1
        ")->row();
    }

    /**
     * Get chart data for performance trends (optionally filtered by tag).
     */
    public function get_chart_data($days = 30, $tag_id = null)
    {
        $tc = $this->build_tag_condition($tag_id, 'c');
        $tbl = db_prefix() . 'chatbot_conversations';
        $rows = $this->db->query("
            SELECT DATE(c.created_at) as dt, AVG(c.csat_score) as avg_rating, COUNT(*) as rating_count
            FROM {$tbl} c
            WHERE c.created_at >= DATE_SUB(CURDATE(), INTERVAL " . (int) $days . " DAY)
              AND c.csat_score IS NOT NULL {$tc}
            GROUP BY DATE(c.created_at)
        ")->result();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->dt] = $r;
        }

        $data = ['dates' => [], 'ratings' => [], 'counts' => []];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $data['dates'][] = _d($date);
            if (isset($map[$date])) {
                $data['ratings'][] = round((float) $map[$date]->avg_rating, 1);
                $data['counts'][] = (int) $map[$date]->rating_count;
            } else {
                $data['ratings'][] = 0;
                $data['counts'][] = 0;
            }
        }

        return $data;
    }

    /**
     * Get conversations for analytics table (with date/rating/tag filters).
     */
    public function get_conversations_filtered($date_range = null, $rating_filter = null, $tag_id = null, $limit = 100)
    {
        $dc = $this->build_date_condition($date_range);
        $tc = $this->build_tag_condition($tag_id);

        $rc = '';
        if ($rating_filter && $rating_filter !== 'all') {
            if ($rating_filter === 'unrated') {
                $rc = ' AND c.csat_score IS NULL';
            } else {
                $rc = ' AND c.csat_score = ' . (int) $rating_filter;
            }
        }

        $rows = $this->db->query("
            SELECT
                c.id, c.visitor_info, c.tags, c.csat_score, c.csat_comment, c.csat_at,
                c.created_at, c.status,
                CONCAT(s.firstname, ' ', s.lastname) as staff_name,
                CASE
                    WHEN c.status = 'closed' THEN 'Closed'
                    WHEN c.status = 'human_handling' THEN 'Active'
                    ELSE 'AI Handling'
                END as status_label
            FROM " . db_prefix() . "chatbot_conversations c
            LEFT JOIN " . db_prefix() . "staff s ON c.assigned_staff_id = s.staffid
            WHERE 1=1 {$dc} {$tc} {$rc}
            ORDER BY c.created_at DESC
            LIMIT " . (int) $limit . "
        ")->result();

        $tags_map = $this->get_tags_map();
        return array_map(function ($conv) use ($tags_map) {
            $vi = is_string($conv->visitor_info) ? json_decode($conv->visitor_info, true) : $conv->visitor_info;
            $decoded = !empty($conv->tags) ? json_decode($conv->tags, true) : [];
            $tag_ids = is_array($decoded) ? $decoded : ($decoded ? [$decoded] : []);
            $tags_display = [];
            foreach ($tag_ids as $tid) {
                if (isset($tags_map[$tid])) {
                    $tags_display[] = $tags_map[$tid]['name'];
                }
            }
            return [
                'id' => $conv->id,
                'visitor_name' => $vi['name'] ?? $vi['email'] ?? null,
                'staff_name' => $conv->staff_name,
                'csat_score' => $conv->csat_score ? (int) $conv->csat_score : null,
                'csat_comment' => $conv->csat_comment,
                'csat_at' => $conv->csat_at,
                'created_at' => _dt($conv->created_at),
                'status' => $conv->status,
                'status_label' => $conv->status_label,
                'tags_display' => $tags_display,
            ];
        }, $rows);
    }

    /**
     * Get all analytics data at once (for initial page load).
     */
    public function get_all_analytics_data($date_range = null, $tag_id = null)
    {
        $data = $this->get_overview_data($date_range, $tag_id);
        $data['top_staff'] = $this->get_top_staff(5, $date_range, $tag_id);
        $data = array_merge($data, $this->get_quick_stats());
        $data['best_day'] = $this->get_best_day();

        $chart_days = ($date_range && in_array((int) $date_range, [7, 30, 90, 180], true)) ? (int) $date_range : 30;
        $data['chart_days'] = $chart_days;
        $chart = $this->get_chart_data($chart_days, $tag_id);
        $data['chart_dates'] = $chart['dates'];
        $data['chart_ratings'] = $chart['ratings'];
        $data['chart_counts'] = $chart['counts'];

        $data['staff_performance'] = $this->get_staff_performance($date_range, $tag_id);
        $data['conversations'] = $this->get_conversations_filtered($date_range, null, $tag_id);
        $data['tag_breakdown'] = $this->get_tag_breakdown($date_range);
        $data['chatbot_tags'] = $this->get_available_tags();

        return $data;
    }

    /**
     * Get analytics API data (with date and tag filtering).
     */
    public function get_analytics_api_data($date_range = null, $tag_id = null)
    {
        return [
            'overview' => $this->get_overview_data($date_range, $tag_id),
            'rating_breakdown' => $this->get_rating_breakdown($date_range, $tag_id),
            'staff_performance' => $this->get_staff_performance($date_range, $tag_id),
            'tag_breakdown' => $this->get_tag_breakdown($date_range),
        ];
    }

    /**
     * Get conversation counts per tag (optionally filtered by date range).
     */
    public function get_tag_breakdown($date_range = null)
    {
        $dc = $this->build_date_condition($date_range);
        $tbl_conv = db_prefix() . 'chatbot_conversations';
        $tags = $this->get_available_tags();
        if (empty($tags)) {
            return [];
        }
        $out = [];
        foreach ($tags as $t) {
            $tid = (int) $t['id'];
            $tc = $this->build_tag_condition($tid);
            $cnt = $this->db->query(
                "SELECT COUNT(*) as cnt FROM {$tbl_conv} c WHERE 1=1 {$dc} {$tc}"
            )->row()->cnt;
            $out[] = [
                'tag_id' => $tid,
                'name' => $t['name'],
                'color' => $t['color'] ?? '#6c757d',
                'count' => (int) $cnt,
            ];
        }
        usort($out, function ($a, $b) {
            return $b['count'] - $a['count'];
        });
        return $out;
    }

    /**
     * Get available tags — delegates to Chatbot_model to avoid duplication.
     */
    public function get_available_tags()
    {
        $CI = &get_instance();
        if (!isset($CI->chatbot_model)) {
            $CI->load->model('Chatbot_model', 'chatbot_model');
        }
        return $CI->chatbot_model->get_available_tags();
    }

    private function get_tags_map()
    {
        $tags = $this->get_available_tags();
        $map = [];
        foreach ($tags as $t) {
            $map[$t['id']] = $t;
        }
        return $map;
    }

    private function build_date_condition($date_range)
    {
        if (!$date_range || $date_range === 'all') {
            return '';
        }
        return ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL ' . (int) $date_range . ' DAY)';
    }

    /**
     * SQL fragment to filter by tag (conversation alias must be 'c').
     * Uses LIKE patterns so it works on both MySQL and MariaDB (no JSON_CONTAINS).
     * tags column stores JSON array e.g. [1,2,3]; we match tag id as whole number only
     * (e.g. tag 2 must not match "12" in [12,3]).
     */
    private function build_tag_condition($tag_id, $alias = 'c')
    {
        if ($tag_id === null || $tag_id === '' || $tag_id === 'all') {
            return '';
        }
        $tid = (int) $tag_id;
        if ($tid <= 0) {
            return '';
        }
        $pre = $alias ? $alias . '.' : '';
        $tidStr = (string) $tid;
        return " AND {$pre}tags IS NOT NULL AND {$pre}tags != '' AND {$pre}tags != '[]'"
            . " AND ({$pre}tags LIKE '[{$tidStr}]' OR {$pre}tags LIKE '[{$tidStr},%' OR {$pre}tags LIKE '%,{$tidStr}]' OR {$pre}tags LIKE '%,{$tidStr},%')";
    }
}
