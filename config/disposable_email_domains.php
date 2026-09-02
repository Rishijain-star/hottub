<?php

/**
 * Known disposable / temporary email domains. Extend via DISPOSABLE_EMAIL_DOMAINS env (comma-separated).
 */
$builtin = [
    'mailinator.com', 'mailinator.net', 'mailinator.org',
    'yopmail.com', 'yopmail.fr', 'yopmail.net',
    'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org', 'guerrillamail.biz', 'guerrillamail.de', 'grr.la', 'sharklasers.com',
    'temp-mail.org', 'temp-mail.io', 'tempmail.com', 'tempmail.net', 'tempmailo.com',
    '10minutemail.com', '10minutemail.net', '10minmail.com',
    'throwawaymail.com', 'throwaway.email',
    'trashmail.com', 'trashmail.net', 'trashmail.me',
    'getnada.com', 'nada.email',
    'maildrop.cc', 'mailnesia.com',
    'dispostable.com', 'mailcatch.com',
    'fakeinbox.com', 'fakemailgenerator.com',
    'mintemail.com', 'mytemp.email',
    'spamgourmet.com', 'spambox.us',
    'mail.tm', 'emailondeck.com',
    'getairmail.com', 'moakt.com',
    'tmpmail.org', 'tmpmail.net',
    'burnermail.io', 'inboxkitten.com',
];

$extra = array_filter(array_map('strtolower', array_map('trim', explode(',', (string) env('DISPOSABLE_EMAIL_DOMAINS', '')))));

return array_values(array_unique(array_merge($builtin, $extra)));
