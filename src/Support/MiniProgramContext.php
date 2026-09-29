<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram\Support;

use EasyWeChat\MiniApp\Application;
use Psr\SimpleCache\CacheInterface;
use Qinii\WechatCore\Config\ConfigResolver;
use Qinii\WechatMiniProgram\Config\MiniProgramConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 小程序运行上下文，统一保存账号、HTTP 客户端和缓存。
 */
final class MiniProgramContext
{
    private const API_BASE_URI = 'https://api.weixin.qq.com/';

    private ?HttpClientInterface $httpClient = null;
    private ?CacheInterface $cache = null;

    /** 创建小程序运行上下文并绑定账号组。 */
    public function __construct(
        private ConfigResolver $configResolver,
        private string $accountName = 'default',
    ) {
    }

    /** 设置所有小程序接口共用的 HTTP 客户端。 */
    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /** 设置小程序 access_token 共用缓存。 */
    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /** 克隆上下文并切换账号组，避免污染原实例。 */
    public function account(string $name): self
    {
        $context = clone $this;
        $context->accountName = $name;

        return $context;
    }

    /** 获取当前账号组的小程序配置。 */
    public function config(): MiniProgramConfig
    {
        return MiniProgramConfig::fromAccountConfig(
            $this->configResolver->require($this->accountName),
        );
    }

    /**
     * 创建新的 EasyWeChat 小程序应用。
     *
     * 每次调用都创建新应用，避免 Swoole 常驻进程复用请求状态。
     */
    public function application(): Application
    {
        $application = new Application($this->config()->toArray());

        if ($this->httpClient !== null) {
            $application->setHttpClient(
                $this->httpClient->withOptions([
                    'base_uri' => self::API_BASE_URI,
                ]),
            );
        }

        if ($this->cache !== null) {
            $application->setCache($this->cache);
        }

        return $application;
    }
}
