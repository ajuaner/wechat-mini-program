<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Exception\MiniProgramException;
use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序码生成能力。
 */
final class QrCode extends AbstractApi
{
    private const UNLIMITED_ENDPOINT = 'wxa/getwxacodeunlimit';
    private const SCENE_MAX_CHARACTERS = 32;

    /**
     * 获取不限制生成数量的小程序码二进制内容。
     *
     * @param array<string, mixed> $options
     */
    public function unlimited(string $scene, array $options = []): string
    {
        $scene = $this->requireString($scene, '小程序码场景值');

        $characterCount = preg_match_all('/./us', $scene, $characters);

        if ($characterCount === false) {
            throw new MiniProgramException(
                '小程序码场景值必须是有效的 UTF-8 字符串。',
            );
        }

        if ($characterCount > self::SCENE_MAX_CHARACTERS) {
            throw new MiniProgramException(sprintf(
                '小程序码场景值不能超过 %d 个可见字符。',
                self::SCENE_MAX_CHARACTERS,
            ));
        }

        $payload = array_merge($options, ['scene' => $scene]);
        $response = $this->client()->postJson(
            self::UNLIMITED_ENDPOINT,
            $payload,
        );
        $content = $response->getContent(false);
        $decoded = json_decode($content, true);

        if (is_array($decoded) && isset($decoded['errcode'])) {
            throw new MiniProgramException(
                sprintf(
                    '获取小程序码失败：%s',
                    (string) ($decoded['errmsg'] ?? $decoded['errcode']),
                ),
                (int) $decoded['errcode'],
            );
        }

        return $content;
    }

    /**
     * 获取小程序码并保存到指定文件。
     *
     * @param array<string, mixed> $options
     */
    public function saveUnlimited(
        string $scene,
        string $filename,
        array $options = [],
    ): string {
        $filename = $this->requireString($filename, '保存文件路径');

        if (file_put_contents(
            $filename,
            $this->unlimited($scene, $options),
        ) === false) {
            throw new MiniProgramException(sprintf(
                '小程序码无法写入文件：%s',
                $filename,
            ));
        }

        return $filename;
    }
}
