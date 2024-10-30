<?php

namespace IgniteOnline\Mailtrap;

class Client
{
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->trap();
    }

    public function trap()
    {
        add_action('phpmailer_init', function ($phpmailer) {
            $phpmailer->isSMTP();
            $phpmailer->Host = 'smtp.mailtrap.io';
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = 2525;
            $phpmailer->Username = $this->username;
            $phpmailer->Password = $this->password;
        });
    }
}
