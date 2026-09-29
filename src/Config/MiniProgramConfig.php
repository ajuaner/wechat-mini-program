<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram\Config;

use Qinii\WechatCore\Enum\ApplicationType;
use Qinii\WechatMiniProgram\Exception\MiniProgramException;

/**
 * 小程序配置对象，负责提取并规范 EasyWeChat 所需配置。
 */
final class MiniProgramConfig
{
    /**
     * 保存已经规范化的小程序配置。
     *
     * @param array<string, mixed> $items
     */
    private function __construct(
        private array $items,
    ) {
    }

    /**
     * 从完整账号组配置创建小程序配置。
     *
     * @param array<string, mixed> $accountConfig
     */
    public static function fromAccountConfig(array $accountConfig): self
    {
        $config = $accountConfig[ApplicationType::MINI_PROGRAM] ?? null;

        if (! is_array($config)) {
            throw new MiniProgramException(sprintf(
                '微信账号配置缺少 [%s] 节点。',
                ApplicationType::MINI_PROGRAM,
            ));
        }

        $appId = self::firstString($config, ['app_id', 'appid']);
        $secret = self::firstString($config, [
            'secret',
            'appsecret',
            'app_secret',
        ]);

        if ($appId === '') {
            throw new MiniProgramException(
                '微信小程序配置 [app_id] 不能为空。',
            );
        }

        if ($secret === '') {
            throw new MiniProgramException(
                '微信小程序配置 [secret/appsecret] 不能为空。',
            );
        }

        $items = [
            'app_id' => $appId,
            'secret' => $secret,
            'token' => self::firstString($config, ['token']),
            'aes_key' => self::firstString($config, [
                'aes_key',
                'encoding_aes_key',
            ]),
            'use_stable_access_token' => (bool) (
                $config['use_stable_access_token'] ?? true
            ),
        ];

        if (isset($config['http'])) {
            if (! is_array($config['http'])) {
                throw new MiniProgramException(
                    '微信小程序配置 [http] 必须是数组。',
                );
            }

            $items['http'] = $config['http'];
        }

        return new self($items);
    }

    /**
     * 返回 EasyWeChat 小程序应用配置。
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /** 获取小程序 AppID。 */
    public function appId(): string
    {
        return (string) $this->items['app_id'];
    }

    /** 获取小程序 AppSecret。 */
    public function secret(): string
    {
        return (string) $this->items['secret'];
    }

    /**
     * 按顺序读取第一个非空字符串配置。
     *
     * @param array<string, mixed> $config
     * @param array<int, string> $keys
     */
    private static function firstString(array $config, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $config[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }
}
