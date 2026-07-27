<?php

/**
 * AI Configuration
 * 
 * Centralized OpenAI configuration for the chatbot module.
 * Checks for the openai module's key first, then falls back to the chatbot's own key.
 */

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Resolve the OpenAI API key.
 * 
 * Priority:
 * 1. chatbot-specific key (chatbot_openai_api_key option)
 * 2. openai module key (openai_api_key option) as fallback
 *
 * @return string The API key, or empty string if none found.
 */
function chatbot_resolve_openai_key(): string
{
    $chatbotKey = get_option('chatbot_openai_api_key');
    if (!empty($chatbotKey)) {
        return $chatbotKey;
    }

    return get_option('openai_api_key') ?: '';
}
