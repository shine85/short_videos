<?php

if (!function_exists('svRequireApiToken')) {
    define('SV_API_AUTH_JSON_OPTIONS', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    function svRepoRootPath()
    {
        return dirname(__DIR__, 2);
    }

    function svGetApiTokenFileCandidates()
    {
        $tokenFileCandidates = [];
        $customTokenFile = trim((string) ($_SERVER['SHORT_VIDEOS_API_TOKEN_FILE'] ?? getenv('SHORT_VIDEOS_API_TOKEN_FILE') ?: ''));
        if ($customTokenFile !== '') {
            $tokenFileCandidates[] = $customTokenFile;
        }

        $tokenFileCandidates[] = svRepoRootPath() . '/.api_token';
        return $tokenFileCandidates;
    }

    function svParseApiTokenExpiry($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $normalizedValue = strtolower($value);
        $neverExpireValues = ['never', 'permanent', 'forever', 'none', 'no-expire', 'long-term'];
        if (in_array($normalizedValue, $neverExpireValues, true)) {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return false;
        }

        return $timestamp;
    }

    function svBuildApiTokenEntry($token, $owner = '', $expiresAt = '', $remark = '', $source = '')
    {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }

        $owner = trim((string) $owner);
        $remark = trim((string) $remark);
        $expiresRaw = trim((string) $expiresAt);
        $expiresTimestamp = svParseApiTokenExpiry($expiresRaw);
        if ($expiresTimestamp === false) {
            return null;
        }

        return [
            'token' => $token,
            'owner' => $owner,
            'expires_at' => $expiresRaw,
            'expires_ts' => $expiresTimestamp,
            'remark' => $remark,
            'source' => $source
        ];
    }

    function svParseApiTokenContent($content, $source = '')
    {
        $entries = [];
        $lines = preg_split('/\r\n|\r|\n/', (string) $content);
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (strpos($line, '#') === 0 || strpos($line, ';') === 0 || strpos($line, '//') === 0) {
                continue;
            }

            $parts = array_map('trim', explode('|', $line, 4));
            $entry = svBuildApiTokenEntry(
                $parts[0] ?? '',
                $parts[1] ?? '',
                $parts[2] ?? '',
                $parts[3] ?? '',
                $source
            );

            if ($entry !== null) {
                $entries[$entry['token']] = $entry;
            }
        }

        return array_values($entries);
    }

    function svGetConfiguredApiTokens()
    {
        static $entries = null;
        if ($entries !== null) {
            return $entries;
        }

        $entries = [];
        $envCandidates = [
            (string) ($_SERVER['SHORT_VIDEOS_API_TOKEN'] ?? ''),
            (string) getenv('SHORT_VIDEOS_API_TOKEN')
        ];

        foreach ($envCandidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }

            $envEntries = svParseApiTokenContent($candidate, 'env');
            if (!empty($envEntries)) {
                foreach ($envEntries as $entry) {
                    $entries[$entry['token']] = $entry;
                }
                return array_values($entries);
            }

            $singleEntry = svBuildApiTokenEntry($candidate, '环境变量', '', '', 'env');
            if ($singleEntry !== null) {
                $entries[$singleEntry['token']] = $singleEntry;
                return array_values($entries);
            }
        }

        foreach (svGetApiTokenFileCandidates() as $tokenFile) {
            if (!is_file($tokenFile) || !is_readable($tokenFile)) {
                continue;
            }

            $content = (string) file_get_contents($tokenFile);
            $fileEntries = svParseApiTokenContent($content, $tokenFile);
            foreach ($fileEntries as $entry) {
                $entries[$entry['token']] = $entry;
            }

            if (!empty($entries)) {
                return array_values($entries);
            }

            $singleValue = trim($content);
            if ($singleValue !== '') {
                $singleEntry = svBuildApiTokenEntry($singleValue, '默认token', '', '', $tokenFile);
                if ($singleEntry !== null) {
                    $entries[$singleEntry['token']] = $singleEntry;
                    return array_values($entries);
                }
            }
        }

        return [];
    }

    function svGetRequestApiToken()
    {
        $candidates = [
            $_GET['token'] ?? null,
            $_POST['token'] ?? null,
            $_SERVER['HTTP_X_API_TOKEN'] ?? null
        ];

        $authorization = trim((string) ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
        if ($authorization !== '' && stripos($authorization, 'Bearer ') === 0) {
            $candidates[] = substr($authorization, 7);
        }

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    function svOutputApiAuthError(array $payload, $statusCode)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        http_response_code($statusCode);
        echo json_encode($payload, SV_API_AUTH_JSON_OPTIONS);
        exit;
    }

    function svFindMatchedApiTokenEntry($requestToken, array $entries)
    {
        foreach ($entries as $entry) {
            if (hash_equals((string) $entry['token'], (string) $requestToken)) {
                return $entry;
            }
        }

        return null;
    }

    function svIsApiTokenExpired(array $entry)
    {
        $expiresTimestamp = $entry['expires_ts'] ?? null;
        if ($expiresTimestamp === null) {
            return false;
        }

        return time() > (int) $expiresTimestamp;
    }

    function svRequireApiToken()
    {
        $configuredEntries = svGetConfiguredApiTokens();
        if (empty($configuredEntries)) {
            svOutputApiAuthError([
                'code' => 500,
                'msg' => '服务端未配置接口token',
                'hint' => '请在项目根目录创建 .api_token 文件'
            ], 500);
        }

        $requestToken = svGetRequestApiToken();
        if ($requestToken === '') {
            svOutputApiAuthError([
                'code' => 401,
                'msg' => '缺少token'
            ], 401);
        }

        $matchedEntry = svFindMatchedApiTokenEntry($requestToken, $configuredEntries);
        if ($matchedEntry === null) {
            svOutputApiAuthError([
                'code' => 401,
                'msg' => 'token错误'
            ], 401);
        }

        if (svIsApiTokenExpired($matchedEntry)) {
            svOutputApiAuthError([
                'code' => 401,
                'msg' => 'token已过期'
            ], 401);
        }

        return $requestToken;
    }

    function svAppendTokenToUrl($url, $token)
    {
        if ($token === '') {
            return $url;
        }

        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . 'token=' . urlencode($token);
    }
}
