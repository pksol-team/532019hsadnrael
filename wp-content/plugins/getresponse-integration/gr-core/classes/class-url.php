<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class Url
 * @package Getresponse\WordPress
 */
class Url {

    const BITCOIN = 'bitcoin';
    const DNS = 'dns';
    const FTP = 'ftp';
    const FTPS = 'ftps';
    const GIT = 'git';
    const HTTP = 'http';
    const HTTPS = 'https';
    const IMAP = 'imap';
    const IRC = 'irc';
    const JABBER = 'jabber';
    const POP = 'pop';
    const SFTP = 'sftp';
    const SKYPE = 'skype';
    const SMTP = 'smtp';
    const SSH = 'ssh';
    const SVN = 'svn';

    protected static $allowedProtocols = array(
        self::BITCOIN,
        self::DNS,
        self::FTP,
        self::FTPS,
        self::GIT,
        self::HTTP,
        self::HTTPS,
        self::IMAP,
        self::IRC,
        self::JABBER,
        self::POP,
        self::SKYPE,
        self::SMTP,
        self::SVN,
    );

    /** @var string */
    protected $url;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $url = str_replace('_', '-', $this->url);
        if(false === filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $urlParts = parse_url($this->url);
        $protocol = $urlParts['scheme'] ?: strtok($this->url, ':');
        return $this->isAllowedProtocol($protocol);
    }

    /**
     * @param string $protocol
     * @return bool
     */
    protected function isAllowedProtocol($protocol)
    {
        if(!in_array($protocol, static::$allowedProtocols)) {
            return false;
        }
        return true;
    }
}
