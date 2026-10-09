<?php

if (!function_exists('main_website_url')) {
    /**
     * Get the absolute URL to the main e-commerce website (stripped of subdomains).
     *
     * @param string $path
     * @return string
     */
    function main_website_url(string $path = ''): string
    {
        $request = request();
        $host = $request->getHost();

        $appDomain = config('app.domain') ?: 'localhost';
        $subdomains = [
            config('app.admin_subdomain') ?: 'admin',
            config('app.vendor_subdomain') ?: 'vendor',
            config('app.ddc_subdomain') ?: 'ddc',
            config('app.adc_subdomain') ?: 'adc',
            config('app.store_subdomain') ?: 'pickupstore',
        ];

        $cleanHost = $host;
        foreach ($subdomains as $sub) {
            if ($sub && str_starts_with(strtolower($host), strtolower($sub) . '.')) {
                $cleanHost = substr($host, strlen($sub) + 1);
                break;
            }
        }

        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portStr = ($port && !in_array((int)$port, [80, 443], true)) ? ':' . $port : '';

        $baseUrl = $scheme . '://' . $cleanHost . $portStr;

        // If configured with explicit APP_URL and host hasn't changed (e.g. non-subdomain env)
        if (config('app.url') && strtolower($cleanHost) === strtolower($host)) {
            $configured = config('app.url');
            if ($configured !== 'http://localhost' && filter_var($configured, FILTER_VALIDATE_URL)) {
                $baseUrl = rtrim($configured, '/');
            }
        }

        $path = ltrim($path, '/');
        return $path ? $baseUrl . '/' . $path : $baseUrl . '/';
    }
}
