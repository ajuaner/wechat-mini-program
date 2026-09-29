<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序登录、会话解密和手机号获取能力。
 */
final class Auth extends AbstractApi
{
    /**
     * 使用登录 code 换取 openid、session_key 和 unionid。
     *
     * @return array<string, mixed>
     */
    public function codeToSession(string $code): array
    {
        return $this->application()->getUtils()->codeToSession(
            $this->requireString($code, '登录 code'),
        );
    }

    /**
     * 使用 session_key 解密小程序加密数据。
     *
     * @return array<string, mixed>
     */
    public function decryptData(
        string $sessionKey,
        string $iv,
        string $encryptedData,
    ): array {
        return $this->application()->getUtils()->decryptSession(
            $this->requireString($sessionKey, 'session_key'),
            $this->requireString($iv, '数据初始向量'),
            $this->requireString($encryptedData, '加密数据'),
        );
    }

    /**
     * 使用手机号授权 code 获取用户手机号。
     *
     * @return array<string, mixed>
     */
    public function getPhoneNumber(string $code): array
    {
        return $this->application()->getUtils()->getPhoneNumber(
            $this->requireString($code, '手机号授权 code'),
        );
    }
}
