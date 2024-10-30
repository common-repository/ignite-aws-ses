<?php

/**
 * Plugin name: Ignite AWS SES
 * Description: Send your email reliably through AWS SES
 * Version: 1.0.0
 */

use IgniteOnline\BetterMail\Settings;

require_once 'vendor/autoload.php';

if (version_compare(PHP_VERSION, '5.6', '<')) {
    exit(sprintf('Better Email requires PHP 5.6 or higher. You’re still on %s.', PHP_VERSION));
}

$settings = new Settings();

add_action('after_setup_theme', function () use ($settings) {
    $settings->saveOptions();
});

if ($settings->env == 'production' && $settings->mailProvider->verifyIsValidSender()) {
    if (!function_exists('wp_mail')) {
        function wp_mail($to, $subject, $message, $headers = '', $attachments = array())
        {
            global $settings;
            $client = $settings->mailProvider;
            if (!count($attachments)) {
                $id = $client->sendMail($to, $subject, $message, $headers, $attachments);
            } else {
                $id = $client->sendRawEmail($to, $subject, $message, $headers, $attachments);
            }
            if ($id) {
                return true;
            }
            return false;
        }
    }
}
