<?php

namespace IgniteOnline\AWS\SES;

use Aws\Credentials\Credentials;
use Aws\Ses\SesClient;

class Client
{
    private $attachments;
    protected $from;
    protected $aws_access_key;
    protected $aws_access_secret_key;
    protected $aws_region;

    public function __construct($aws_access_key_id, $aws_secret_access_key, $aws_region, $aws_address, $aws_address_name)
    {
        $this->from = $aws_address;
        $this->fromName = "{$aws_address_name}";
        $this->aws_access_key = $aws_access_key_id;
        $this->aws_access_secret_key = $aws_secret_access_key;
        $this->aws_region = $aws_region;

        $this->credentials = new Credentials($this->aws_access_key, $this->aws_access_secret_key);
        $this->awsClient = new SesClient([
            'credentials' => $this->credentials,
            'region' => $this->aws_region,
            'version' => 'latest'
        ]);
    }

    public function sendMail($to, $subject, $message, $headers = [], $attachments = [])
    {
        $replyTo = $this->from;
        if ($headers && !is_array($header)) {
            $headers = explode("\n", $headers);
            foreach ($headers as $header) {
                $header = explode(":", $header);
                if ($header[0] == 'Reply-To') {
                    $replyTo = $header[1];
                }
            }
        }
        if (is_array($headers)) {
            if (isset($headers['Reply-To'])) {
                $replyTo = $headers['Reply-To'];
            }
        }
        $mail = $this->awsClient->sendEmail([
            'Destination' => [
                'ToAddresses' => [
                    $to,
                ],
            ],
            'Message' => [
                'Body' => [
                    'Html' => [
                        'Charset' => 'UTF-8',
                        'Data' => $message,
                    ],
                ],
                'Subject' => [
                    'Charset' => 'UTF-8',
                    'Data' => $subject,
                ],
            ],
            'ReplyToAddresses' => [
                $replyTo,
            ],
            'Source' => "{$this->fromName} <{$this->from}>",
        ]);
        return $mail['messageId'];
    }

    public function addAttachments($name, $path, $mimeType = 'application/octet-stream', $contentId = null)
    {
        if (file_exists($path) && is_file($path) && is_readable($path)) {
            $this->attachments[$name] = array(
                'name' => $name,
                'mimeType' => $mimeType,
                'data' => file_get_contents($path),
                'contentId' => $contentId,
            );
            return true;
        }
    }

    public function encodeRecipients($recipient)
    {
        if (is_array($recipient)) {
            return join(', ', array_map(array($this, 'encodeRecipients'), $recipient));
        }

        if (preg_match("/(.*)<(.*)>/", $recipient, $regs)) {
            $recipient = '=?' . $this->recipientsCharset . '?B?' . base64_encode($regs[1]) . '?= <' . $regs[2] . '>';
        }

        return $recipient;
    }

    public function sendRawEmail($to, $subject, $message, $headers = [], $attachments = [])
    {
        foreach ($attachments as $attachment) {
            $this->addAttachments(basename($attachment), $attachment);
        }
        $boundary = uniqid(rand(), true);
        $raw_message = '';
        $raw_message .= 'To: ' . $this->encodeRecipients($to) . "\n";
        $raw_message .= 'From: ' . $this->encodeRecipients('dev@igniteonline.com.au') . "\n";
        $raw_message .= 'Reply-To: ' . $this->encodeRecipients('dev@igniteonline.com.au') . "\n";
        $raw_message .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\n";
        $raw_message .= 'MIME-Version: 1.0' . "\n";
        $raw_message .= 'Content-type: Multipart/Mixed; boundary="' . $boundary . '"' . "\n";
        $raw_message .= "\n--{$boundary}\n";
        $raw_message .= 'Content-type: Multipart/Alternative; boundary="alt-' . $boundary . '"' . "\n";
        $charset = empty($message) ? '' : "; charset=\"UTF-8\"";
        $raw_message .= "\n--alt-{$boundary}\n";
        $raw_message .= 'Content-Type: text/html' . $charset . "\n\n";
        $raw_message .= $message . "\n";
        $raw_message .= "\n--alt-{$boundary}--\n";

        foreach ($this->attachments as $attachment) {
            $raw_message .= "\n--{$boundary}\n";
            $raw_message .= 'Content-Type: ' . $attachment['mimeType'] . '; name="' . $attachment['name'] . '"' . "\n";
            $raw_message .= 'Content-Disposition: attachment' . "\n";
            if (!empty($attachment['contentId'])) {
                $raw_message .= 'Content-ID: ' . $attachment['contentId'] . '' . "\n";
            }
            $raw_message .= 'Content-Transfer-Encoding: base64' . "\n";
            $raw_message .= "\n" . chunk_split(base64_encode($attachment['data']), 76, "\n") . "\n";
        }

        $raw_message .= "\n--{$boundary}--\n";

        $mail = $this->awsClient->sendRawEmail([
            'Destinations' => [
                $to,
            ],
            'RawMessage' => [
                'Data' => ($raw_message),
            ],
            'Source' => "{$this->fromName} <{$this->from}>",
        ]);
        return $mail['messageId'];
    }

    public function verifyIsValidSender()
    {
        $response = $this->awsClient->listIdentities([]);

        if ($response->get('Identities')) {
            return in_array($this->from, $response->get('Identities'));
        }
        return false;
    }
}
