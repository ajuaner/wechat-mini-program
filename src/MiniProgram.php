<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use EasyWeChat\MiniApp\Application;
use Psr\SimpleCache\CacheInterface;
use Qinii\WechatCore\Config\ArrayConfigProvider;
use Qinii\WechatCore\Config\ConfigResolver;
use Qinii\WechatMiniProgram\Support\MiniProgramContext;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 微信小程序统一门面。
 */
final class MiniProgram
{
    private MiniProgramContext $context;

    /** 创建门面并绑定默认账号组。 */
    public function __construct(
        ConfigResolver $configResolver,
        string $accountName = 'default',
    ) {
        $this->context = new MiniProgramContext(
            $configResolver,
            $accountName,
        );
    }

    /**
     * 使用普通数组配置快速创建门面。
     *
     * @param array<string, array<string, mixed>> $config
     */
    public static function create(
        array $config,
        string $defaultAccount = 'default',
    ): self {
        return new self(
            new ConfigResolver(new ArrayConfigProvider($config)),
            $defaultAccount,
        );
    }

    /** 设置所有小程序接口共用的 HTTP 客户端。 */
    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->context->setHttpClient($httpClient);

        return $this;
    }

    /** 设置小程序 access_token 共用缓存。 */
    public function setCache(CacheInterface $cache): self
    {
        $this->context->setCache($cache);

        return $this;
    }

    /** 切换账号组并返回独立的新门面。 */
    public function account(string $name): self
    {
        $miniProgram = clone $this;
        $miniProgram->context = $this->context->account($name);

        return $miniProgram;
    }

    /** 获取登录、会话和手机号能力。 */
    public function auth(): Auth
    {
        return new Auth($this->context);
    }

    /** 获取小程序码能力。 */
    public function qrCode(): QrCode
    {
        return new QrCode($this->context);
    }

    /** 获取 URL Link 能力。 */
    public function urlLink(): UrlLink
    {
        return new UrlLink($this->context);
    }

    /** 获取订阅消息能力。 */
    public function subscribe(): Subscribe
    {
        return new Subscribe($this->context);
    }

    /** 获取内容安全检测能力。 */
    public function contentSecurity(): ContentSecurity
    {
        return new ContentSecurity($this->context);
    }

    /** 获取临时素材能力。 */
    public function media(): Media
    {
        return new Media($this->context);
    }

    /** 获取小程序交易发货能力。 */
    public function shipping(): Shipping
    {
        return new Shipping($this->context);
    }

    /** 获取小程序直播能力。 */
    public function live(): Live
    {
        return new Live($this->context);
    }

    /** 获取底层 EasyWeChat 应用，调用尚未封装的接口。 */
    public function application(): Application
    {
        return $this->context->application();
    }
}
