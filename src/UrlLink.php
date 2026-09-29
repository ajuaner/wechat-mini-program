<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序 URL Link 生成和查询能力。
 */
final class UrlLink extends AbstractApi
{
    private const GENERATE_ENDPOINT = 'wxa/generate_urllink';
    private const QUERY_ENDPOINT = 'wxa/query_urllink';

    /**
     * 生成可以从短信、邮件或网页打开的小程序链接。
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function generate(
        string $path = '',
        string $query = '',
        array $options = [],
    ): array {
        $payload = $options;

        if (trim($path) !== '') {
            $payload['path'] = trim($path);
        }

        if (trim($query) !== '') {
            $payload['query'] = trim($query);
        }

        return $this->generateRaw($payload);
    }

    /**
     * 使用完整微信参数生成小程序链接。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function generateRaw(array $payload): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::GENERATE_ENDPOINT, $payload),
        );
    }

    /**
     * 查询指定小程序链接的配置。
     *
     * @return array<string, mixed>
     */
    public function query(string $urlLink): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::QUERY_ENDPOINT, [
                'url_link' => $this->requireString($urlLink, 'URL Link'),
            ]),
        );
    }
}
