<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CSRF Exclusion URIs for WhatsApp Chat Module
 *
 * This file is loaded early by Perfex's InitModules hook,
 * BEFORE CSRF verification runs. This allows external services
 * like n8n to POST to these endpoints without a CSRF token.
 *
 * Security is handled via Bearer token authentication in the API controller.
 *
 * @return array List of URI patterns to exclude from CSRF protection
 */
return [
    'whatsapp_chat/api/.*',
    'whatsapp_chat/api/message',
    'whatsapp_chat/api/status',
];
