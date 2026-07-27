<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Chat Module Constants
 * Centralized magic numbers and strings for maintainability
 */

// Database Limits
const CHAT_STAFF_PAGINATION_LIMIT = 10;

// Pusher Channel Patterns
const CHAT_GROUP_PRESENCE_PREFIX = 'presence-';

// Default Status Values
const CHAT_DEFAULT_USER_STATUS = 'online';

// File Extension Patterns (for shared files feature)
const CHAT_ALL_FILE_EXTENSIONS = 'unknown|rar|zip|mp3|mp4|mov|flv|wmv|avi|doc|docx|pdf|xls|xlsx|zip|rar|txt|html|css|jpeg|jpg|png|swf|PNG|JPG|JPEG';
const CHAT_PHOTO_EXTENSIONS = 'unknown|jpeg|jpg|png|gif|swf|PNG|JPG|JPEG';
const CHAT_DOCUMENT_EXTENSIONS = 'unknown|rar|zip|mp3|mp4|mov|flv|wmv|avi|doc|docx|pdf|xls|xlsx|zip|rar|txt|html|css';

// Calls configuration
const CHAT_CALLS_STAFF_CHANNEL_PREFIX = 'calls-staff-';
const CHAT_CALLS_CLIENT_CHANNEL_PREFIX = 'calls-client-';
const CHAT_CALLS_DEFAULT_STUN = 'stun:stun.l.google.com:19302';
// Optional TURN – leave empty if not available; can be set in settings later
const CHAT_CALLS_TURN_URL = '';
const CHAT_CALLS_TURN_USERNAME = '';
const CHAT_CALLS_TURN_CREDENTIAL = '';
