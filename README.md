# Qinii WeChat Mini Program

基于 EasyWeChat 6 的微信小程序扩展包，提供 CRMEB 当前实际使用的小程序登录、小程序码、订阅消息、内容安全、交易发货和直播能力，并复用 `qinii/wechat-core` 的多账号配置与 HTTP 客户端。

## 环境要求

- PHP 8.0 及以上
- EasyWeChat 6.19.1 及以上
- `qinii/wechat-core`

## 安装

```bash
composer require qinii/wechat-mini-program
```

## 配置

配置按账号组组织，小程序配置固定放在 `mini_program` 节点。`secret`、`appsecret` 和 `app_secret` 均可使用，内部统一转换为 EasyWeChat 所需的 `secret`。

```php
use Qinii\WechatCore\Enum\ApplicationType;
use Qinii\WechatMiniProgram\MiniProgram;

$miniProgram = MiniProgram::create([
    'default' => [
        ApplicationType::MINI_PROGRAM => [
            'app_id' => 'wx1234567890',
            'appsecret' => 'your-app-secret',
            'token' => 'your-callback-token',
            'aes_key' => 'your-encoding-aes-key',
            'use_stable_access_token' => true,
            'http' => [
                'timeout' => 5,
            ],
        ],
    ],
]);
```

使用依赖注入时，可以直接注入 Core 的 `ConfigResolver`：

```php
use Qinii\WechatCore\Config\ConfigResolver;
use Qinii\WechatMiniProgram\MiniProgram;

$miniProgram = new MiniProgram(
    configResolver: $configResolver,
    accountName: 'default',
);
```

## 公共能力

```php
// 切换账号，不修改原门面
$other = $miniProgram->account('other');

// 注入 PSR-16 缓存，生产环境建议使用 Redis 等共享缓存
$miniProgram->setCache($cache);

// 注入 Symfony HttpClientInterface 实例
$miniProgram->setHttpClient($httpClient);

// 获取底层 EasyWeChat 应用，调用尚未封装的接口
$application = $miniProgram->application();
```

Swoole 环境可以直接注入 `wechat-core` 提供的客户端：

```php
use Qinii\WechatCore\Http\SwooleHttpClient;

$miniProgram->setHttpClient(new SwooleHttpClient([
    'http_version' => '1.1',
    'timeout' => 5,
]));
```

扩展包每次调用都会创建新的 EasyWeChat 应用，避免 Swoole 常驻进程中的账号和请求状态互相污染。

## 登录和手机号

```php
$session = $miniProgram->auth()->codeToSession($code);
$openid = $session['openid'];
$sessionKey = $session['session_key'];

$userData = $miniProgram->auth()->decryptData(
    $sessionKey,
    $iv,
    $encryptedData,
);

$phone = $miniProgram->auth()->getPhoneNumber($phoneCode);
```

`decryptData()` 用于兼容仍在使用旧版加密数据的业务；新接入手机号能力建议使用 `getPhoneNumber()`。

## 小程序码和 URL Link

```php
$binary = $miniProgram->qrCode()->unlimited('order:1001', [
    'page' => 'pages/order/detail',
    'width' => 280,
    'check_path' => true,
]);

$miniProgram->qrCode()->saveUnlimited(
    'order:1001',
    '/absolute/path/order-1001.png',
    ['page' => 'pages/order/detail'],
);

$result = $miniProgram->urlLink()->generate(
    'pages/order/detail',
    'id=1001',
    ['is_expire' => false],
);
```

## 临时素材和内容安全

```php
$media = $miniProgram->media()->uploadImage('/absolute/path/cover.jpg');

$textResult = $miniProgram->contentSecurity()->checkText(
    content: $content,
    scene: 2,
    openId: $openid,
);

$mediaResult = $miniProgram->contentSecurity()->checkMedia(
    mediaUrl: $imageUrl,
    mediaType: 2,
    scene: 2,
    openId: $openid,
);
```

## 订阅消息

```php
$categories = $miniProgram->subscribe()->getCategories();
$keywords = $miniProgram->subscribe()
    ->getTemplateKeywords($templateTitleId);

$template = $miniProgram->subscribe()->addTemplate(
    $templateTitleId,
    [1, 2, 3],
    '订单状态通知',
);

$miniProgram->subscribe()->send(
    openId: $openid,
    templateId: $template['priTmplId'],
    data: [
        'character_string1' => 'ORDER-1001',
        'amount2' => ['value' => '99.00 元'],
    ],
    page: 'pages/order/detail?id=1001',
);
```

扩展包只负责微信模板的查询、选用、删除和发送，不绑定项目数据库。CRMEB 的模板自动同步流程应读取自身模板配置，再调用 `getTemplates()`、`addTemplate()` 和 `deleteTemplate()` 完成编排。

## 交易发货

商户号是发货请求中的业务参数，不从支付扩展包读取。普通商户传下单商户号，服务商场景按微信文档传实际下单商户号。

```php
$miniProgram->shipping()->uploadByOutTradeNo(
    merchantId: $mchId,
    outTradeNo: $outTradeNo,
    logisticsType: 1,
    shippingList: [[
        'tracking_no' => 'SF1234567890',
        'express_company' => 'SF',
        'item_desc' => '商品名称*1',
        'contact' => [
            'receiver_contact' => '138****8000',
        ],
    ]],
    payerOpenId: $openid,
);
```

复杂发货、合单发货和查询直接传递微信原始参数：

```php
$miniProgram->shipping()->upload($payload);
$miniProgram->shipping()->uploadCombined($combinedPayload);
$miniProgram->shipping()->getOrder($order);
$miniProgram->shipping()->getOrderList($query);
```

## 小程序直播

直播能力包含直播间管理、商品管理、成员管理和长期订阅。创建直播间使用的图片应先通过临时素材接口上传。

```php
$cover = $miniProgram->media()->uploadImage('/absolute/path/cover.jpg');
$share = $miniProgram->media()->uploadImage('/absolute/path/share.jpg');
$feeds = $miniProgram->media()->uploadImage('/absolute/path/feeds.jpg');

$room = $miniProgram->live()->createRoom([
    'name' => '新品直播间',
    'coverImg' => $cover['media_id'],
    'startTime' => $startTime,
    'endTime' => $endTime,
    'anchorName' => '主播昵称',
    'anchorWechat' => 'anchor_wechat',
    'shareImg' => $share['media_id'],
    'feedsImg' => $feeds['media_id'],
    'type' => 1,
    'closeLike' => 0,
    'closeGoods' => 0,
    'closeComment' => 0,
]);

$rooms = $miniProgram->live()->getRooms();
$pushUrl = $miniProgram->live()->getPushUrl($room['roomId']);
$miniProgram->live()->importGoods($room['roomId'], $goodsIds);
```

直播商品、助手、角色、功能开关和长期订阅等完整方法见 [功能清单](docs/features.md)。

## 扩展接口

交易发货和直播模块提供 `raw()`，可在微信增加新接口而扩展包尚未升级时直接调用：

```php
$result = $miniProgram->live()->raw(
    'POST',
    'wxaapi/broadcast/example',
    ['json' => $payload],
);
```

其他未封装能力可以通过 `application()` 获取 EasyWeChat 应用继续调用。

## CRMEB 常用方法迁移

| 旧方法 | 新调用方式 |
| --- | --- |
| `MiniProgram::getUserInfo()` | `$miniProgram->auth()->codeToSession()` |
| `MiniProgram::decryptData()` | `$miniProgram->auth()->decryptData()` |
| `MiniProgram::appCodeUnlimit()` | `$miniProgram->qrCode()->unlimited()` |
| `MiniProgram::temporaryUpload()` | `$miniProgram->media()->uploadImage()` |
| `MiniProgram::msgSecCheck()` | `$miniProgram->contentSecurity()->checkText()` 或 `checkMedia()` |
| `MiniProgram::sendSubscribeTemlate()` | `$miniProgram->subscribe()->send()` |
| `MiniProgram::shippingByTradeNo()` | `$miniProgram->shipping()->uploadByOutTradeNo()` |
| `MiniProgram::createLiveRoom()` | `$miniProgram->live()->createRoom()` |

## 测试

```bash
composer test
```

开发仓库中也可以复用已有项目的 Composer autoload：

```bash
WECHAT_VENDOR_AUTOLOAD=/absolute/path/vendor/autoload.php php tests/run.php
```

## 功能范围

完整方法列表和暂未实现能力见 [功能清单](docs/features.md)。
