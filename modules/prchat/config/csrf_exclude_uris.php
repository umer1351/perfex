<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Chatbot widget endpoints excluded from CSRF protection.
 * These are cross-origin POST requests from external websites
 * embedding the chatbot widget via <script> tag.
 */
return [
    'prchat/Chatbot_Controller/config/.+',
    'prchat/Chatbot_Controller/message/.+',
    'prchat/Chatbot_Controller/history/.+',
    'prchat/Chatbot_Controller/lead/.+',
    'prchat/Chatbot_Controller/escalate/.+',
    'prchat/Chatbot_Controller/suggested_questions/.+',
    'prchat/Chatbot_Controller/typing/.+',
    'prchat/Chatbot_Controller/submit_rating/.+',
    'prchat/Chatbot_Controller/confirm_escalation/.+',
    'prchat/Chatbot_Controller/upload',
];
