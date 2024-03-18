<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class SiteUtil
 * 
 * SiteUtil provides basic site-related methods.
 * 
 * @package App\Util
 */
class SiteUtil
{
    /**
     * @var SecurityUtil
     * Instance of the SecurityUtil for handling security-related utilities.
     */
    private SecurityUtil $securityUtil;

    /**
     * SiteUtil constructor.
     *
     * @param SecurityUtil $securityUtil The SecurityUtil instance.
     */
    public function __construct(SecurityUtil $securityUtil)
    {
        $this->securityUtil = $securityUtil;
    }

    /**
     * Get the HTTP host.
     *
     * @return string The HTTP host.
     */
    public function getHttpHost(): string
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Check if the application is running on localhost.
     *
     * @return bool Whether the application is running on localhost.
     */
    public function isRunningLocalhost(): bool 
    {
        // get host URL
        $host = $this->getHttpHost();
    
        // check if host is null
        if ($host == null) {
            return false;
        }
    
        // check if running on localhost
        switch ($host) {
            case 'localhost':
            case '127.0.0.1':
            case '10.0.0.93':
                return true;
            default:
                return false;
        }
    }

    /**
     * Check if the connection is secure (SSL).
     *
     * @return bool Whether the connection is secure.
     */
    public function isSsl(): bool 
    {
        // xheck if HTTPS header is set and its value is either 1 or 'on'
        return isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 1 || strtolower($_SERVER['HTTPS']) === 'on');
    }

    /**
     * Check if the application is in maintenance mode.
     *
     * @return bool Whether the application is in maintenance mode.
     */
    public function isMaintenance(): bool
    {
        return $_ENV['MAINTENANCE_MODE'] === 'true';
    }

    /**
     * Check if the ssl only mode.
     *
     * @return bool Whether the application is under ssl only mode.
     */
    public function isSSLOnly(): bool
    {
        return $_ENV['SSL_ONLY'] === 'true';
    }

    /**
     * Check if the application is in development mode.
     *
     * @return bool Whether the application is in development mode.
     */
    public function isDevMode(): bool
    {
        return $_ENV['APP_ENV'] === 'dev';
    }

    /**
     * Get the value of a query string parameter, with XSS protection.
     *
     * @param string $query The query string parameter name.
     * @param Request $request The Symfony request object.
     *
     * @return string|null The sanitized value of the query string parameter.
     */
    public function getQueryString(string $query, Request $request): ?string
    {
        // get query value
        $value = $request->query->get($query);

        if ($value == null) {
            return '1';
        } else {
            // escape query string value (XSS Protection)
            return $this->securityUtil->escapeString($value);
        }
    }
}
