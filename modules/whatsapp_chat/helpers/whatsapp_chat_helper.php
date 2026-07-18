<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * whatsapp_chat_helper.php
 *
 * Utility functions for phone normalization and CRM matching.
 * Loaded globally in the main module init file (whatsapp_chat.php).
 */

if (!function_exists('wac_normalize_phone')) {
    /**
     * Normalize a phone number to E.164 digits only.
     *
     * Strips everything except digits, removes a leading '+',
     * and optionally handles local format conversion (Bangladesh: 880).
     *
     * Examples:
     *   +8801719059881  -> 8801719059881
     *   01719059881     -> 8801719059881  (BD prefix assumed if 11 digits starting with 0)
     *   8801719059881   -> 8801719059881
     *   +1-555-123-4567 -> 15551234567
     *
     * @param  string $phone Raw phone string
     * @return string        E.164-style digits (no +)
     */
    function wac_normalize_phone($phone)
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-digit characters (including +, spaces, dashes, parens)
        $digits = preg_replace('/\D/', '', (string) $phone);

        // Bangladesh local format: if 11 digits starting with 0, replace leading 0 with 880
        if (strlen($digits) === 11 && substr($digits, 0, 1) === '0') {
            $digits = '880' . substr($digits, 1);
        }

        return $digits;
    }
}

if (!function_exists('wac_match_crm_contact')) {
    /**
     * Try to find a matching Perfex customer or lead by normalized phone.
     *
     * Checks tblclients (phonenumber field) first, then tblleads (phonenumber field).
     * Uses LIKE-based matching compatible with MySQL 5.7+ and MariaDB.
     *
     * @param  string $normalized_phone  Already-normalized phone digits
     * @return array  ['customer_id' => int|null, 'lead_id' => int|null]
     */
    function wac_match_crm_contact($normalized_phone)
    {
        if (empty($normalized_phone)) {
            return ['customer_id' => null, 'lead_id' => null];
        }

        $CI = &get_instance();

        $customer_id = null;
        $lead_id     = null;

        // Get last 10 digits for partial matching (handles missing country code)
        $last10 = strlen($normalized_phone) > 10 ? substr($normalized_phone, -10) : $normalized_phone;
        // Get last 7 digits for even more flexible matching
        $last7 = strlen($normalized_phone) > 7 ? substr($normalized_phone, -7) : $normalized_phone;

        // ----- Match customer -----
        // Use LIKE with wildcards - works on all MySQL versions
        // Perfex uses 'phonenumber' column in tblclients
        $CI->db->select('userid');
        $CI->db->from(db_prefix() . 'clients');
        $CI->db->group_start();
            $CI->db->like('phonenumber', $normalized_phone, 'both');
            $CI->db->or_like('phonenumber', $last10, 'both');
            $CI->db->or_like('phonenumber', $last7, 'both');
        $CI->db->group_end();
        $CI->db->where('active', 1);
        $CI->db->limit(1);
        $customer = $CI->db->get()->row_array();

        if ($customer) {
            $customer_id = (int) $customer['userid'];
        }

        // ----- Match lead (only if no customer found) -----
        if (!$customer_id) {
            $CI->db->select('id');
            $CI->db->from(db_prefix() . 'leads');
            $CI->db->group_start();
                $CI->db->like('phonenumber', $normalized_phone, 'both');
                $CI->db->or_like('phonenumber', $last10, 'both');
                $CI->db->or_like('phonenumber', $last7, 'both');
            $CI->db->group_end();
            $CI->db->where('lost', 0);
            $CI->db->limit(1);
            $lead = $CI->db->get()->row_array();

            if ($lead) {
                $lead_id = (int) $lead['id'];
            }
        }

        return [
            'customer_id' => $customer_id,
            'lead_id'     => $lead_id,
        ];
    }
}

if (!function_exists('wac_api_token_valid')) {
    /**
     * Validate the Bearer token from the Authorization header.
     *
     * Token is stored in Perfex options as 'whatsapp_chat_api_token'.
     *
     * @return bool
     */
    function wac_api_token_valid()
    {
        $stored_token = get_option('whatsapp_chat_api_token');

        if (empty($stored_token)) {
            log_message('error', '[whatsapp_chat] API token not configured');
            return false;
        }

        $auth_header = '';

        // Try $_SERVER directly
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $auth_header = $value;
                    break;
                }
            }
        }

        if (empty($auth_header)) {
            log_message('debug', '[whatsapp_chat] No Authorization header found');
            return false;
        }

        // Expect: "Bearer <token>"
        if (preg_match('/Bearer\s+(.+)$/i', $auth_header, $matches)) {
            $provided_token = trim($matches[1]);
            return hash_equals($stored_token, $provided_token);
        }

        log_message('debug', '[whatsapp_chat] Invalid Authorization header format');
        return false;
    }
}

if (!function_exists('wac_get_timezone')) {
    /**
     * Get the configured timezone from Perfex settings.
     * Falls back to Asia/Dhaka if not set.
     *
     * @return string
     */
    function wac_get_timezone()
    {
        $tz = function_exists('get_option') ? get_option('timezone') : '';
        return !empty($tz) ? $tz : 'Asia/Dhaka';
    }
}

if (!function_exists('wac_time_ago')) {
    /**
     * Human-readable time difference for chat timestamps.
     * Uses the Perfect ERP timezone (Asia/Dhaka by default) so that
     * times match what is shown in the CRM Localization settings.
     *
     * @param  string|null $datetime MySQL datetime string (stored in UTC or server time)
     * @return string
     */
    function wac_time_ago($datetime)
    {
        if (empty($datetime)) {
            return '';
        }

        try {
            $tz       = new DateTimeZone(wac_get_timezone());
            $utc      = new DateTimeZone('UTC');

            // Parse the stored datetime (treat as UTC if no zone info)
            $then_dt  = new DateTime($datetime, $utc);
            $now_dt   = new DateTime('now', $utc);

            $diff = $now_dt->getTimestamp() - $then_dt->getTimestamp();
        } catch (Exception $e) {
            return '';
        }

        if ($diff < 0) {
            // Future date – show formatted local date
            $then_dt->setTimezone($tz);
            return $then_dt->format('d M');
        } elseif ($diff < 60) {
            return _l('just_now') ?: 'Just now';
        } elseif ($diff < 3600) {
            $m = floor($diff / 60);
            return $m . 'm';
        } elseif ($diff < 86400) {
            $h = floor($diff / 3600);
            return $h . 'h';
        } elseif ($diff < 604800) {
            $d = floor($diff / 86400);
            return $d . 'd';
        } else {
            $then_dt->setTimezone($tz);
            return $then_dt->format('d M');
        }
    }
}

if (!function_exists('wac_safe_string')) {
    /**
     * Safely output a string, handling null/empty values.
     *
     * @param  mixed  $value   The value to output
     * @param  string $default Default if empty
     * @return string
     */
    function wac_safe_string($value, $default = '')
    {
        return !empty($value) ? (string) $value : $default;
    }
}
