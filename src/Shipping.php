<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Exception\MiniProgramException;
use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序交易管理服务的发货和订单查询能力。
 */
final class Shipping extends AbstractApi
{
    private const UPLOAD_ENDPOINT = 'wxa/sec/order/upload_shipping_info';
    private const UPLOAD_COMBINED_ENDPOINT = 'wxa/sec/order/upload_combined_shipping_info';
    private const GET_ORDER_ENDPOINT = 'wxa/sec/order/get_order';
    private const GET_ORDER_LIST_ENDPOINT = 'wxa/sec/order/get_order_list';
    private const NOTIFY_CONFIRM_ENDPOINT = 'wxa/sec/order/notify_confirm_receive';
    private const SET_MESSAGE_PATH_ENDPOINT = 'wxa/sec/order/set_msg_jump_path';
    private const IS_MANAGED_ENDPOINT = 'wxa/sec/order/is_trade_managed';
    private const IS_CONFIRMED_ENDPOINT = 'wxa/sec/order/is_trade_management_confirmation_completed';
    private const DELIVERY_LIST_ENDPOINT = 'cgi-bin/express/delivery/open_msg/get_delivery_list';

    /**
     * 使用完整微信参数录入单笔支付单的发货信息。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function upload(array $payload): array
    {
        $this->requireKeys($payload, [
            'order_key',
            'logistics_type',
            'delivery_mode',
            'shipping_list',
            'upload_time',
            'payer',
        ]);

        return $this->responseToArray(
            $this->client()->postJson(self::UPLOAD_ENDPOINT, $payload),
        );
    }

    /**
     * 按商户订单号组装并录入发货信息。
     *
     * @param array<int, array<string, mixed>> $shippingList
     * @return array<string, mixed>
     */
    public function uploadByOutTradeNo(
        string $merchantId,
        string $outTradeNo,
        int $logisticsType,
        array $shippingList,
        string $payerOpenId,
        int $deliveryMode = 1,
        bool $isAllDelivered = true,
        ?string $uploadTime = null,
    ): array {
        $payload = [
            'order_key' => [
                'order_number_type' => 1,
                'mchid' => $this->requireString($merchantId, '商户号'),
                'out_trade_no' => $this->requireString(
                    $outTradeNo,
                    '商户订单号',
                ),
            ],
            'logistics_type' => $this->requirePositiveInt(
                $logisticsType,
                '物流模式',
            ),
            'delivery_mode' => $this->requirePositiveInt(
                $deliveryMode,
                '发货模式',
            ),
            'shipping_list' => $this->requireArray(
                $shippingList,
                '物流信息列表',
            ),
            'upload_time' => $uploadTime === null
                ? date(DATE_RFC3339)
                : $this->requireString($uploadTime, '上传时间'),
            'payer' => [
                'openid' => $this->requireString(
                    $payerOpenId,
                    '支付者 openid',
                ),
            ],
        ];

        if ($deliveryMode === 2) {
            $payload['is_all_delivered'] = $isAllDelivered;
        }

        return $this->upload($payload);
    }

    /**
     * 使用完整微信参数录入合单发货信息。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function uploadCombined(array $payload): array
    {
        $this->requireKeys($payload, [
            'order_key',
            'sub_orders',
            'upload_time',
            'payer',
        ]);

        return $this->responseToArray(
            $this->client()->postJson(
                self::UPLOAD_COMBINED_ENDPOINT,
                $payload,
            ),
        );
    }

    /**
     * 查询指定支付单的发货状态。
     *
     * @param array<string, mixed> $order
     * @return array<string, mixed>
     */
    public function getOrder(array $order): array
    {
        return $this->responseToArray(
            $this->client()->postJson(
                self::GET_ORDER_ENDPOINT,
                $this->requireArray($order, '订单标识'),
            ),
        );
    }

    /**
     * 按完整微信查询参数获取支付单列表。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function getOrderList(array $payload): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::GET_ORDER_LIST_ENDPOINT, $payload),
        );
    }

    /**
     * 提醒指定订单的用户确认收货。
     *
     * @return array<string, mixed>
     */
    public function notifyConfirmReceive(
        string $merchantId,
        string $merchantTradeNo,
        int $receivedTime,
    ): array {
        return $this->responseToArray(
            $this->client()->postJson(self::NOTIFY_CONFIRM_ENDPOINT, [
                'merchant_id' => $this->requireString(
                    $merchantId,
                    '商户号',
                ),
                'merchant_trade_no' => $this->requireString(
                    $merchantTradeNo,
                    '商户订单号',
                ),
                'received_time' => $this->requirePositiveInt(
                    $receivedTime,
                    '签收时间',
                ),
            ]),
        );
    }

    /**
     * 设置发货消息和确认收货消息的小程序跳转路径。
     *
     * @return array<string, mixed>
     */
    public function setMessageJumpPath(string $path): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::SET_MESSAGE_PATH_ENDPOINT, [
                'path' => $this->requireString($path, '消息跳转路径'),
            ]),
        );
    }

    /**
     * 查询小程序是否已开通发货信息管理服务。
     *
     * @return array<string, mixed>
     */
    public function isTradeManaged(): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::IS_MANAGED_ENDPOINT, [
                'appid' => $this->context->config()->appId(),
            ]),
        );
    }

    /**
     * 查询小程序是否已完成交易结算管理确认。
     *
     * @return array<string, mixed>
     */
    public function isManagementConfirmationCompleted(): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::IS_CONFIRMED_ENDPOINT, [
                'appid' => $this->context->config()->appId(),
            ]),
        );
    }

    /**
     * 获取微信物流服务支持的运力 ID 列表。
     *
     * @return array<string, mixed>
     */
    public function getDeliveryList(): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::DELIVERY_LIST_ENDPOINT),
        );
    }

    /**
     * 调用尚未封装的小程序交易管理接口。
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function raw(
        string $method,
        string $endpoint,
        array $options = [],
    ): array {
        return $this->requestArray($method, $endpoint, $options);
    }

    /**
     * 校验接口需要的顶层参数是否存在。
     *
     * @param array<string, mixed> $payload
     * @param array<int, string> $keys
     */
    private function requireKeys(array $payload, array $keys): void
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $payload)) {
                throw new MiniProgramException(sprintf(
                    '发货参数 [%s] 不能为空。',
                    $key,
                ));
            }
        }
    }
}
