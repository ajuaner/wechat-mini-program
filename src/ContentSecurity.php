<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序文本、图片和音频内容安全检测能力。
 */
final class ContentSecurity extends AbstractApi
{
    private const TEXT_ENDPOINT = 'wxa/msg_sec_check';
    private const MEDIA_ENDPOINT = 'wxa/media_check_async';

    /**
     * 检测用户提交的文本内容。
     *
     * @return array<string, mixed>
     */
    public function checkText(
        string $content,
        int $scene,
        string $openId,
    ): array {
        return $this->responseToArray(
            $this->client()->postJson(self::TEXT_ENDPOINT, [
                'content' => $this->requireString($content, '检测文本'),
                'version' => 2,
                'scene' => $this->requirePositiveInt($scene, '检测场景'),
                'openid' => $this->requireString($openId, 'openid'),
            ]),
        );
    }

    /**
     * 异步检测图片或音频内容。
     *
     * @return array<string, mixed>
     */
    public function checkMedia(
        string $mediaUrl,
        int $mediaType,
        int $scene,
        string $openId,
    ): array {
        return $this->responseToArray(
            $this->client()->postJson(self::MEDIA_ENDPOINT, [
                'media_url' => $this->requireString($mediaUrl, '媒体地址'),
                'media_type' => $this->requirePositiveInt(
                    $mediaType,
                    '媒体类型',
                ),
                'version' => 2,
                'scene' => $this->requirePositiveInt($scene, '检测场景'),
                'openid' => $this->requireString($openId, 'openid'),
            ]),
        );
    }
}
