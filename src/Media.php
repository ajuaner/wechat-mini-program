<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Exception\MiniProgramException;
use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序临时素材上传能力。
 */
final class Media extends AbstractApi
{
    private const UPLOAD_ENDPOINT = 'cgi-bin/media/upload';
    private const SUPPORTED_TYPES = ['image', 'voice', 'video', 'thumb'];

    /**
     * 上传指定类型的临时素材。
     *
     * @return array<string, mixed>
     */
    public function upload(string $type, string $path): array
    {
        $type = strtolower(trim($type));

        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new MiniProgramException(sprintf(
                '不支持的临时素材类型 [%s]。',
                $type,
            ));
        }

        if (! is_file($path) || ! is_readable($path)) {
            throw new MiniProgramException(sprintf(
                '临时素材文件不存在或不可读：%s',
                $path,
            ));
        }

        $response = $this->client()
            ->withFile($path, 'media', basename($path))
            ->post(self::UPLOAD_ENDPOINT, [
                'query' => ['type' => $type],
            ]);

        return $this->responseToArray($response);
    }

    /** 上传临时图片素材。 */
    public function uploadImage(string $path): array
    {
        return $this->upload('image', $path);
    }
}
