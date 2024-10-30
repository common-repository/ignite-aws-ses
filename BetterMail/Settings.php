<?php

namespace IgniteOnline\BetterMail;

use IgniteOnline\Mailtrap\Client as MailtrapWPClient;
use IgniteOnline\AWS\SES\Client as SesWPClient;

class Settings
{

    public $env;
    public $mailProvider;
    protected $optionsName = 'ignite-better-mail';
    protected $from;
    protected $fromName;

    public function __construct()
    {
        $this->checkEnvironment();
        $savedSettings = $this->loadDefaults();
        $this->from = $savedSettings['aws_address'];
        $this->fromName = $savedSettings['aws_address_name'];
        if ($this->env !== 'production') {
            $this->mailProvider = new MailtrapWPClient($savedSettings['mailtrap_user'], $savedSettings['mailtrap_password']);
        } else {
            $this->mailProvider = new SesWPClient($savedSettings['aws_access_key_id'], $savedSettings['aws_secret_access_key'], $savedSettings['aws_region'], $savedSettings['aws_address'], $savedSettings['aws_address_name']);
        }
        $this->actions();
        $this->scripts();
        $this->ajaxEndpoints();
    }

    public function scripts()
    {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_script('ignite-admin-email', plugin_dir_url(__FILE__) . '../plugin-assets/scripts/main.js');
            wp_enqueue_style('ignite-admin-email', plugin_dir_url(__FILE__) . '../plugin-assets/styles/main.css');
        });
    }

    protected function checkEnvironment()
    {
        $this->env = 'production';
        if (WP_DEBUG == true || function_exists('env') && env('WP_ENV') !== 'production') {
            $this->env = 'debug';
        }
    }

    public function getEnvironment()
    {
        return $this->env == 'production' ? 'Production' : 'Development';
    }

    protected function actions()
    {
        $this->adminMenu();
        $this->adminStyles();
        $this->emailFrom();
    }

    public function emailFrom()
    {
        $from = $this->from;
        $fromName = $this->fromName;
        add_filter('wp_mail_from', function ($email) use ($from) {
            return $from;
        });

        add_filter('wp_mail_from_name', function () use ($fromName) {
            return $fromName;
        });
    }

    protected function adminMenu()
    {
        add_action('admin_menu', function () {
            add_menu_page('Ignite Online', 'Ignite Online', 'manage_options', 'ignite-settings', [$this, 'adminOptionsPage'], 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNC40OCA5MS44MiI+PHRpdGxlPkFzc2V0IDE8L3RpdGxlPjxnIGlkPSJMYXllcl8yIiBkYXRhLW5hbWU9IkxheWVyIDIiPjxnIGlkPSJMYXllcl8xLTIiIGRhdGEtbmFtZT0iTGF5ZXIgMSI+PHBvbHlnb24gcG9pbnRzPSIyNC40OCA0OC45IDI0LjI3IDQ4LjgyIDEuMTUgMCA3LjEyIDQyLjk5IDAgNDIuOTkgMjMuMTIgOTEuODIgMTcuMSA0OC45IDI0LjMxIDQ4LjkgMjQuNDggNDguOSIgZmlsbD0iIzBmOSIvPjwvZz48L2c+PC9zdmc+');
            add_submenu_page('ignite-settings', 'Better Mail', 'Better Mail', 'manage_options', 'ignite-better-mail', [$this, 'adminOptionsPage']);
        });
    }

    protected function adminStyles()
    {
        add_action('admin_init', function () {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                return;
            }
            ?>
            <style>
                #toplevel_page_ignite-settings .wp-menu-image {
                    background-size: contain !important;
                }
            </style>
            <?php
        });
    }

    public function adminOptionsPage()
    {
        include plugin_dir_path(__FILE__) . '../templates/options.php';
    }

    public function loadDefaults()
    {
        $defaults = [
            'mailtrap_user' => '',
            'mailtrap_password' => '',
            'mail_from' => '',
            'aws_access_key_id' => '',
            'aws_secret_access_key' => '',
            'aws_region' => '',
            'aws_address' => '',
            'aws_address_name' => '',
        ];
        $currentOptions = get_option($this->optionsName);
        $currentOptions = !empty($currentOptions) ? maybe_unserialize($currentOptions) : [];
        return array_merge($defaults, $currentOptions);
    }

    public function saveOptions()
    {
        if (isset($_POST['ignite-better-mail']) && wp_verify_nonce($_POST['ignite-better-mail'], 'ignite-better-mail-save')) {
            $currentOptions = $this->loadDefaults();
            if (!empty($_POST['mailtrap_user']))
                $currentOptions['mailtrap_user'] = sanitize_text_field($_POST['mailtrap_user']);
            if (!empty($_POST['mailtrap_password']))
                $currentOptions['mailtrap_password'] = sanitize_text_field($_POST['mailtrap_password']);
            if (!empty($_POST['mail_from']))
                $currentOptions['mail_from'] = sanitize_text_field($_POST['mail_from']);
            if (!empty($_POST['aws_access_key_id']))
                $currentOptions['aws_access_key_id'] = sanitize_text_field($_POST['aws_access_key_id']);
            if (!empty($_POST['aws_secret_access_key']))
                $currentOptions['aws_secret_access_key'] = sanitize_text_field($_POST['aws_secret_access_key']);
            if (!empty($_POST['aws_region']))
                $currentOptions['aws_region'] = sanitize_text_field($_POST['aws_region']);
            if (!empty($_POST['aws_address']))
                $currentOptions['aws_address'] = sanitize_text_field($_POST['aws_address']);
            if (!empty($_POST['aws_address_name']))
                $currentOptions['aws_address_name'] = sanitize_text_field($_POST['aws_address_name']);
            update_option($this->optionsName, maybe_serialize($currentOptions), true);
            $this->setNotice("Saved!");
            if ($this->env == 'production') {
                $this->mailProvider = new SesWPClient($currentOptions['aws_access_key_id'], $currentOptions['aws_secret_access_key'], $currentOptions['aws_region'], $currentOptions['aws_address'], $currentOptions['aws_address_name']);
                if (!$this->mailProvider->verifyIsValidSender()) {
                    $this->setNotice('The sender address is not a verified address in AWS. Please enter a different one or verify it first.', 'warning');
                }
            }
        }
    }

    public function setNotice($message, $type = 'success')
    {
        add_action('admin_notices', function () use ($message, $type) {
            echo "<div class='notice notice-{$type} is-dismissible'><p>{$message}</p></div>";
        });
    }

    public function ajaxEndpoints()
    {
        add_action('wp_ajax_ignite-test-email', function () {
            wp_mail($_REQUEST['test_email'], 'Test Email from Ignite Better Email', 'This is a test message!');
        });
    }
}
