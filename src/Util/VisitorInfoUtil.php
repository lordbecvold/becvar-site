<?php

namespace App\Util;

/**
 * Class VisitorInfoUtil
 * 
 * VisitorInfoUtil provides methods to get information about visitors.
 * 
 * @package App\Util
 */
class VisitorInfoUtil
{
    /**
     * @var SiteUtil
     * Instance of the SiteUtil for handling site-related utilities.
     */
    private SiteUtil $siteUtil;

    /**
     * @var JsonUtil
     * Instance of the JsonUtil for handling JSON-related utilities.
     */
    private JsonUtil $jsonUtil;

    /**
     * VisitorInfoUtil constructor.
     *
     * @param SiteUtil $siteUtil The SiteUtil instance.
     * @param JsonUtil $jsonUtil The JsonUtil instance.
     */
    public function __construct(SiteUtil $siteUtil, JsonUtil $jsonUtil)
    {
        $this->siteUtil = $siteUtil;
        $this->jsonUtil = $jsonUtil;
    }

    /**
     * Get the client's IP address.
     *
     * @return string|null The client's IP address.
     */
    public function getIP(): ?string 
    {
        // check client IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } 
        
        // check forwarded IP (get IP from cloudflare visitors) 
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } 
        
        // default addr get
        else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Get the user agent (browser).
     *
     * @return string|null The user agent.
     */
    public function getBrowser(): ?string 
    {
        // get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        return $user_agent !== null ? $user_agent : 'Unknown';
    }

    /**
     * Get a short version of the browser name.
     *
     * @param string $user_agent The user agent string.
     *
     * @return string|null The short browser name.
     */
    public function getBrowserShortify(string $user_agent): ?string 
    {
        $output = null;
    
        // identify shortify array [ID: str_contains, Value: replacement]
        $browser_list = $this->jsonUtil->getJson(__DIR__.'/../../config/becwork/browser-list.json');
    
        // check if browser list found
        if ($browser_list != null) {
            // check all user agents
            foreach ($browser_list as $index => $value) {
                // check if index found in agent
                if (str_contains($user_agent, $index)) {
                    $output = $index;
                    break;
                }
            }
        }
    
        // check if output is not found in browser list
        if ($output == null) {
            // identify common browsers using switch statement
            switch (true) {
                case preg_match('/MSIE (\d+\.\d+);/', $user_agent):
                case str_contains($user_agent, 'MSIE'):
                    $output = 'Internet Explore';
                    break;
                case preg_match('/Chrome[\/\s](\d+\.\d+)/', $user_agent):
                    $output = 'Chrome';
                    break;
                case preg_match('/Edge\/\d+/', $user_agent):
                    $output = 'Edge';
                    break;
                case preg_match('/Firefox[\/\s](\d+\.\d+)/', $user_agent):
                case str_contains($user_agent, 'Firefox/96'):
                    $output = 'Firefox';
                    break;
                case preg_match('/Safari[\/\s](\d+\.\d+)/', $user_agent):
                    $output = 'Safari';
                    break;
                case str_contains($user_agent, 'UCWEB'):
                case str_contains($user_agent, 'UCBrowser'):
                    $output = 'UC Browser';
                    break;
                case str_contains($user_agent, 'Iceape'):
                    $output = 'IceApe Browser';
                    break;
                case str_contains($user_agent, 'maxthon'):
                    $output = 'Maxthon Browser';
                    break;
                case str_contains($user_agent, 'konqueror'):
                    $output = 'Konqueror Browser';
                    break;
                case str_contains($user_agent, 'NetFront'):
                    $output = 'NetFront Browser';
                    break;
                case str_contains($user_agent, 'Midori'):
                    $output = 'Midori Browser';
                    break;
                case preg_match('/OPR[\/\s](\d+\.\d+)/', $user_agent):
                case preg_match('/Opera[\/\s](\d+\.\d+)/', $user_agent):
                    $output = 'Opera';
                    break;
                default:
                    // if not found, check user agent length
                    if (str_contains($user_agent, ' ') || strlen($user_agent) >= 39) {
                        $output = 'Unknown';
                    } else {
                        $output = $user_agent;
                    }
            }
        }
    
        return $output;
    }

    /**
     * Get the operating system.
     *
     * @return string|null The operating system.
     */
    public function getOS(): ?string 
    { 
        $os = 'Unknown OS';

        // get browser agent
        $agent = $this->getBrowser();
        
        // OS list
        $os_array = array (
            '/windows nt 5.2/i'     =>  'Windows Server_2003',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/win16/i'              =>  'Windows 3.11',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/blackberry/i'         =>  'BlackBerry',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/SMART-TV/i'           =>  'Smart TV',
            '/windows/i'            =>  'Windows',
            '/iphone/i'             =>  'Mac IOS',
            '/android/i'            =>  'Android',
            '/webos/i'              =>  'Mobile',
            '/ubuntu/i'             =>  'Ubuntu',
            '/linux/i'              =>  'Linux',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad'
        );
        
        // find os
        foreach ($os_array as $regex => $value) {
            // check if os found
            if ($regex != null && $agent != null) {
                if (preg_match($regex, $agent)) {
                    $os = $value;
                }
            }
        }
        
        return $os;
    }

    /**
     * Get the location based on IP address.
     *
     * @param string $ip_address The IP address.
     *
     * @return array<string,string>|null The location information (city, country) or null on failure.
     */
    public function getLocation(string $ip_address): ?array
    {
        // check if site is running on localhost
        if ($this->siteUtil->isRunningLocalhost()) {
            return ['city' => 'locale', 'country' => 'host'];
        }

        try {
            // get geoplugin URL from environment variables
            $geoplugin_url = $_ENV['GEOPLUGIN_URL'];
    
            // get data from geoplugin
            $geoplugin_data = file_get_contents("$geoplugin_url/json.gp?ip=$ip_address");
    
            // decode data
            $details = json_decode($geoplugin_data);
    
            // get country
            $country = $details->geoplugin_countryCode;
    
            // extract city name from timezone
            $city = null;
            if (!empty($details->geoplugin_timezone)) {
                $timezone_parts = explode('/', $details->geoplugin_timezone);
                $city = $timezone_parts[1] ?? null;
            }
        } catch (\Exception) {
            // handle exception gracefully
            $country = null;
            $city = null;
        }
    
        // set default values if data not retrieved
        $country = $country ?: 'Unknown';
        $city = $city ?: 'Unknown';
    
        return ['city' => $city, 'country' => $country];
    }
}
