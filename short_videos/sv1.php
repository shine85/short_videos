<?php
/**
 * 聚合短视频解析入口
 */

require_once dirname(__DIR__) . '/api/common/ApiAuth.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

const SV1_JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
const SV1_DEFAULT_BASE_URL = 'https://api2.jumh989.gq';
const SV1_REQUEST_TIMEOUT = 30;

$requestToken = svRequireApiToken();
$url = getInputUrl();
if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
    respond([
        'code' => 400,
        'msg' => '请输入有效的链接'
    ], 400);
}

$platforms = [
    [
        'keywords' => ['douyin'],
        'path' => '/api/douyin/douyin.php'
    ],
    [
        'keywords' => ['kuaishou'],
        'path' => '/api/kuaishou/ksjx.php'
    ],
    [
        'keywords' => ['bilibili'],
        'path' => '/api/bilibili/index.php'
    ],
    [
        'keywords' => ['pipix'],
        'path' => '/api/ppxia.php'
    ],
    [
        'keywords' => ['ippzone', 'pipigx'],
        'path' => '/api/pipigx.php'
    ],
    [
        'keywords' => ['weibo'],
        'path' => '/api/weibo.php'
    ],
    [
        'keywords' => ['xhs', 'xiaohongshu'],
        'path' => '/api/xiaohongshu/xhsjx.php'
    ]
];

$matchedPlatform = matchPlatform($url, $platforms);
if ($matchedPlatform === null) {
    respond([
        'code' => 201,
        'msg' => '不支持您输入的链接平台'
    ]);
}

$apiUrl = buildBaseUrl() . $matchedPlatform['path'] . '?url=' . urlencode($url);
$apiUrl = svAppendTokenToUrl($apiUrl, $requestToken);
$response = requestUrl($apiUrl);

if ($response['ok'] && isValidJson($response['body'])) {
    echo $response['body'];
    exit;
}

$errorData = [
    'code' => 500,
    'msg' => $response['ok'] ? '接口返回格式不正确' : '请求接口失败'
];

if ($response['http_code'] > 0) {
    $errorData['http_code'] = $response['http_code'];
}

if ($response['error'] !== '') {
    $errorData['error'] = $response['error'];
}

if ($response['body'] !== '') {
    $errorData['data'] = limitString($response['body']);
}

respond($errorData, 500);

function getInputUrl()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return trim((string) ($_POST['url'] ?? ''));
    }

    $queryString = (string) ($_SERVER['QUERY_STRING'] ?? '');
    $urlParamPos = strpos($queryString, 'url=');
    if ($urlParamPos !== false) {
        return trim(urldecode(substr($queryString, $urlParamPos + 4)));
    }

    return trim((string) ($_GET['url'] ?? ''));
}

function matchPlatform($url, array $platforms)
{
    $lowerUrl = strtolower($url);
    foreach ($platforms as $platform) {
        foreach ($platform['keywords'] as $keyword) {
            if (strpos($lowerUrl, $keyword) !== false) {
                return $platform;
            }
        }
    }

    return null;
}

function buildBaseUrl()
{
    $fallback = parse_url(SV1_DEFAULT_BASE_URL);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : (string) ($_SERVER['REQUEST_SCHEME'] ?? ($fallback['scheme'] ?? 'https'));
    $host = (string) ($_SERVER['HTTP_HOST'] ?? ($fallback['host'] ?? 'api2.jumh989.gq'));

    return $scheme . '://' . $host;
}

function requestUrl($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => SV1_REQUEST_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_ENCODING => '',
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
    ]);

    $body = curl_exec($ch);
    $error = '';
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($body === false) {
        $error = curl_error($ch);
        $body = '';
    }

    curl_close($ch);

    return [
        'ok' => $error === '' && $httpCode < 400,
        'body' => (string) $body,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

function isValidJson($string)
{
    if ($string === '') {
        return false;
    }

    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

function limitString($value, $limit = 500)
{
    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, $limit) . '...';
}

function respond(array $data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, SV1_JSON_OPTIONS);
    exit;
}
