<?php declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';

    public string $userAgent  = 'CodeIgniter';
    public string $protocol   = 'smtp';
    public string $mailPath   = '/usr/sbin/sendmail';
    public string $SMTPHost   = '';
    public string $SMTPUser   = '';
    public string $SMTPPass   = '';
    public int    $SMTPPort   = 465;
    public int    $SMTPTimeout = 5;
    public bool   $SMTPKeepAlive = false;
    public string $SMTPCrypto = 'ssl';
    public bool   $wordWrap   = true;
    public int    $wrapChars  = 76;
    public string $mailType   = 'html';
    public string $charset    = 'utf-8';
    public bool   $validate   = false;
    public int    $priority   = 3;
    public string $CRLF       = "\r\n";
    public string $newline    = "\r\n";
    public bool   $BCCBatchMode = false;
    public int    $BCCBatchSize = 200;
    public bool   $DSN        = false;
}