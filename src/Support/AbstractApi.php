<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram\Support;

use EasyWeChat\Kernel\HttpClient\AccessTokenAwareClient;
use EasyWeChat\MiniApp\Application;
use Qinii\WechatMiniProgram\Exception\MiniProgramException;

/**
 * 小程序接口公共基类，仅提供应用、客户端和通用校验能力。
 */
abstract class AbstractApi
{
    /** 创建业务接口并绑定小程序运行上下文。 */
    public function __construct(
        protected MiniProgramContext $context,
    ) {
    }

    /** 获取新的 EasyWeChat 小程序应用。 */
    protected function application(): Application
    {
        return $this->context->application();
    }

    /** 获取携带 access_token 的微信接口客户端。 */
    protected function client(): AccessTokenAwareClient
    {
        return $this->application()->createClient();
    }

    /**
     * 将微信接口响应转换为数组。
     *
     * @return array<string, mixed>
     */
    protected function responseToArray(object $response): array
    {
        if (! method_exists($response, 'toArray')) {
            throw new MiniProgramException('微信接口响应无法转换为数组。');
        }

        $data = $response->toArray();

        if (! is_array($data)) {
            throw new MiniProgramException('微信接口响应格式不正确。');
        }

        return $data;
    }

    /** 校验字符串参数不为空。 */
    protected function requireString(string $value, string $name): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new MiniProgramException(sprintf('%s 不能为空。', $name));
        }

        return $value;
    }

    /** 校验整数参数大于零。 */
    protected function requirePositiveInt(int $value, string $name): int
    {
        if ($value < 1) {
            throw new MiniProgramException(sprintf('%s 必须大于零。', $name));
        }

        return $value;
    }

    /** 校验数组参数不为空。 */
    protected function requireArray(array $value, string $name): array
    {
        if ($value === []) {
            throw new MiniProgramException(sprintf('%s 不能为空。', $name));
        }

        return $value;
    }

    /**
     * 发送自定义接口请求并转换响应。
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function requestArray(
        string $method,
        string $endpoint,
        array $options = [],
    ): array {
        return $this->responseToArray(
            $this->client()->request(
                strtoupper($this->requireString($method, '请求方法')),
                $this->requireString($endpoint, '接口地址'),
                $options,
            ),
        );
    }
}
