<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'alm_call_logs')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "alm_call_logs` (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        call_id CHAR(36) NOT NULL COMMENT 'UUID of the call',
        to_number VARCHAR(15) NOT NULL,
        from_number VARCHAR(15) NOT NULL,
        status VARCHAR(20) NOT NULL,
        call_length FLOAT,
        recording_url TEXT,
        transcripts JSON,
        summary TEXT,
        call_ended_by VARCHAR(50),
        direction TINYINT(1) NOT NULL COMMENT '0 = Inbound, 1 = Outbound',
        price DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Price of the call in currency',
        extra_information JSON,
        rel_type VARCHAR(50),
        rel_id BIGINT,
        staff_id BIGINT DEFAULT NULL COMMENT 'Staff ID',
        sid VARCHAR(255) DEFAULT NULL,
        twilio_account_sid VARCHAR(255) DEFAULT NULL,
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ai_provider VARCHAR(50) NOT NULL COMMENT 'Name of the AI provider (e.g., bland_ai, vapi_ai)'
    );");
}

if (!option_exists('alm_voice_assistant')) {
    add_option('alm_voice_assistant', 'vapi_ai');
}

if (!option_exists('bland_ai_max_duration')) {
    add_option('bland_ai_max_duration', 5);
}

if (!option_exists('bland_ai_agent_voice')) {
    add_option('bland_ai_agent_voice', 'e1289219-0ea2-4f22-a994-c542c2a48a0f');
}

if (!option_exists('bland_ai_temperature')) {
    add_option('bland_ai_temperature', 0.6);
}

if (!option_exists('vapi_ai_max_duration')) {
    add_option('vapi_ai_max_duration', 300);
}

if (!option_exists('vapi_ai_voice_provider')) {
    add_option('vapi_ai_voice_provider', 'openai');
}

if (!option_exists('vapi_ai_agent_voice')) {
    add_option('vapi_ai_agent_voice', 'alloy');
}

if (!option_exists('vapi_ai_temperature')) {
    add_option('vapi_ai_temperature', 1.0);
}

if (!option_exists('vapi_ai_max_tokens')) {
    add_option('vapi_ai_max_tokens', 250);
}

if (!option_exists('vapi_ai_detect_emotions')) {
    add_option('vapi_ai_detect_emotions', 0);
}

if (!option_exists('filler_injection_enabled')) {
    add_option('filler_injection_enabled', 0);
}

if (!option_exists('back_channeling_enabled')) {
    add_option('back_channeling_enabled', 0);
}

if (!option_exists('dial_keypad_function_enabled')) {
    add_option('dial_keypad_function_enabled', 0);
}

if (!option_exists('end_call_function_enabled')) {
    add_option('end_call_function_enabled', 0);
}

if (!option_exists('alm_first_sentence')) {
    add_option('alm_first_sentence', 'Hello, I am AI Lead Manager. How can I help you?');
}

if (!option_exists('vapi_ai_knowledgebase_inbound')) {
    add_option('vapi_ai_knowledgebase_inbound', '[]');
}

if (!option_exists('vapi_ai_knowledgebase_outbound')) {
    add_option('vapi_ai_knowledgebase_outbound', '[]');
}

if (!option_exists('bland_ai_knowledgebase_inbound')) {
    add_option('bland_ai_knowledgebase_inbound', '[]');
}

if (!option_exists('bland_ai_knowledgebase_outbound')) {
    add_option('bland_ai_knowledgebase_outbound', '[]');
}
