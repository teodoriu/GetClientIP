<?php
/**
 * Get Client Ip Library
 * =====================
 *
 * GetClientIp is a lightweight PHP class for detecting client IP address.
 * It uses specific HTTP headers to detect the real/original
 * (not private/reserved range) client ip address not final proxy IP
 *
 * @author      Aleksey Pevnev <pevnev@mail.ru>
 *
 * @license     Code and contributions have 'MIT License'
 *
 * @link        GitHub Repo:  https://github.com/worm/GetClientIp
 *
 * @version     1.0.1
 */

class GetClientIp
{
    /**
     * Stores the version number of the current release.
     */
    const VERSION   = '1.0.1';

    /**
     * All possible HTTP headers that represent the
     * IP address string.
     *
     * @var array
     */
    protected static $ipServerHeaders = array(
        'HTTP_X_FORWARDED_FOR',
        'X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'X-REAL-IP',
        'VIA',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    );

    /**
     * HTTP headers in the PHP-flavor.
     *
     * @var array
     */
    protected $serverHeaders = array();

    /**
     * Construct an instance of this class.
     *
     * @param array  $headers   Specify the headers as injection.
     */
    public function __construct($headers = []) {
        $this->setServerHeaders($headers);
    }

    /**
     * Get the current script version.
     *
     * @return string The version number in semantic version format.
     */
    public static function getScriptVersion()
    {
        return self::VERSION;
    }

    /**
     * Set the SERVER Headers. This method set IP headers data with sent manually headers array.
     *
     * @param array $serverHeaders The headers to set. If null, then using PHP _SERVER to extract the headers.
     */
    public function setServerHeaders($serverHeaders = [])
    {
        // use global _SERVER if $httpHeaders aren't defined
        if (!is_array($serverHeaders) || !count($serverHeaders)) {
            $serverHeaders = $_SERVER;
        }

        // clear existing headers
        $this->serverHeaders = array();

        // Only headers with IP.
        foreach (self::getIpServerHeaders() as $key) {
            if (array_key_exists($key, $serverHeaders)) {
                $this->serverHeaders[$key] = $serverHeaders[$key];
            }
        }
    }

    /**
     * Retrieves the IP detect headers.
     *
     * @return array
     */
    public function getServerHeaders()
    {
        return $this->serverHeaders;
    }

    /**
     * Get all possible SERVER headers that
     * can contain the IP address.
     *
     * @return array List of SERVER headers.
     */
    public function getIpServerHeaders()
    {
        return self::$ipServerHeaders;
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private or reserved network range.
     *
     * @param string $ip IP address for test
     *
     * @return bool
     */
    public function validate_ip($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        return false;
    }

    /**
     * Get the real valid IP address from serverHeaders
     *
     * @return bool|string
     */
    public function getClientIp()
    {
        foreach ($this->getIpServerHeaders() as $ipHeader) {
            if (isset($this->serverHeaders[$ipHeader])) {
                foreach (explode(',', $this->serverHeaders[$ipHeader]) as $ip) {
                    $ip = trim($ip);
                    if (self::validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get the real valid long IP address
     *
     * @return bool|string
     */
    public function getLongClientIp()
    {
        $ip = self::getClientIp();

        return $ip ? ip2long($ip) : false;
    }
}