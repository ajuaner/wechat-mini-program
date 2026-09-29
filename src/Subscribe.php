<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Exception\MiniProgramException;
use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序订阅模板管理和订阅消息发送能力。
 */
final class Subscribe extends AbstractApi
{
    private const CATEGORY_ENDPOINT = 'wxaapi/newtmpl/getcategory';
    private const PUBLIC_TITLES_ENDPOINT = 'wxaapi/newtmpl/getpubtemplatetitles';
    private const KEYWORDS_ENDPOINT = 'wxaapi/newtmpl/getpubtemplatekeywords';
    private const ADD_ENDPOINT = 'wxaapi/newtmpl/addtemplate';
    private const LIST_ENDPOINT = 'wxaapi/newtmpl/gettemplate';
    private const DELETE_ENDPOINT = 'wxaapi/newtmpl/deltemplate';
    private const SEND_ENDPOINT = 'cgi-bin/message/subscribe/send';

    /**
     * 获取当前小程序账号所属类目。
     *
     * @return array<string, mixed>
     */
    public function getCategories(): array
    {
        return $this->responseToArray(
            $this->client()->get(self::CATEGORY_ENDPOINT),
        );
    }

    /**
     * 获取类目下的公共模板标题。
     *
     * @param array<int, int|string> $categoryIds
     * @return array<string, mixed>
     */
    public function getPublicTemplates(
        array $categoryIds,
        int $start = 0,
        int $limit = 30,
    ): array {
        if ($categoryIds === []) {
            throw new MiniProgramException('订阅消息类目 ID 不能为空。');
        }

        if ($start < 0 || $limit < 1) {
            throw new MiniProgramException(
                '订阅模板分页参数不正确。',
            );
        }

        return $this->responseToArray(
            $this->client()->get(self::PUBLIC_TITLES_ENDPOINT, [
                'query' => [
                    'ids' => implode(',', array_map('strval', $categoryIds)),
                    'start' => $start,
                    'limit' => $limit,
                ],
            ]),
        );
    }

    /**
     * 获取公共模板标题对应的关键词列表。
     *
     * @return array<string, mixed>
     */
    public function getTemplateKeywords(string $templateTitleId): array
    {
        return $this->responseToArray(
            $this->client()->get(self::KEYWORDS_ENDPOINT, [
                'query' => [
                    'tid' => $this->requireString(
                        $templateTitleId,
                        '公共模板标题 ID',
                    ),
                ],
            ]),
        );
    }

    /**
     * 选用公共模板并添加到当前小程序。
     *
     * @param array<int, int|string> $keywordIds
     * @return array<string, mixed>
     */
    public function addTemplate(
        string $templateTitleId,
        array $keywordIds,
        string $sceneDescription = '',
    ): array {
        if ($keywordIds === []) {
            throw new MiniProgramException('订阅模板关键词 ID 不能为空。');
        }

        return $this->responseToArray(
            $this->client()->postJson(self::ADD_ENDPOINT, [
                'tid' => $this->requireString(
                    $templateTitleId,
                    '公共模板标题 ID',
                ),
                'kidList' => array_values($keywordIds),
                'sceneDesc' => trim($sceneDescription),
            ]),
        );
    }

    /**
     * 获取当前小程序已添加的订阅模板。
     *
     * @return array<string, mixed>
     */
    public function getTemplates(): array
    {
        return $this->responseToArray(
            $this->client()->get(self::LIST_ENDPOINT),
        );
    }

    /**
     * 删除当前小程序中的指定订阅模板。
     *
     * @return array<string, mixed>
     */
    public function deleteTemplate(string $privateTemplateId): array
    {
        return $this->responseToArray(
            $this->client()->postJson(self::DELETE_ENDPOINT, [
                'priTmplId' => $this->requireString(
                    $privateTemplateId,
                    '订阅模板 ID',
                ),
            ]),
        );
    }

    /**
     * 发送一次性订阅消息。
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function send(
        string $openId,
        string $templateId,
        array $data,
        ?string $page = null,
        string $state = 'formal',
        string $language = 'zh_CN',
    ): array {
        $message = [
            'touser' => $this->requireString($openId, 'openid'),
            'template_id' => $this->requireString(
                $templateId,
                '订阅模板 ID',
            ),
            'data' => $this->formatData($data),
            'miniprogram_state' => $this->requireString(
                $state,
                '小程序版本',
            ),
            'lang' => $this->requireString($language, '语言'),
        ];

        if ($page !== null && trim($page) !== '') {
            $message['page'] = trim($page);
        }

        return $this->sendRaw($message);
    }

    /**
     * 发送已经组装完成的订阅消息参数。
     *
     * @param array<string, mixed> $message
     * @return array<string, mixed>
     */
    public function sendRaw(array $message): array
    {
        foreach (['touser', 'template_id', 'data'] as $key) {
            if (! isset($message[$key]) || $message[$key] === '') {
                throw new MiniProgramException(sprintf(
                    '订阅消息参数 [%s] 不能为空。',
                    $key,
                ));
            }
        }

        return $this->responseToArray(
            $this->client()->postJson(self::SEND_ENDPOINT, $message),
        );
    }

    /**
     * 将简写数据转换为微信订阅消息字段格式。
     *
     * @param array<string, mixed> $data
     * @return array<string, array<string, string>>
     */
    private function formatData(array $data): array
    {
        if ($data === []) {
            throw new MiniProgramException('订阅消息内容不能为空。');
        }

        $formatted = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && array_key_exists('value', $value)) {
                $value = $value['value'];
            } elseif (is_array($value)) {
                $value = $value[0] ?? '';
            }

            if (! is_scalar($value) && $value !== null) {
                throw new MiniProgramException(sprintf(
                    '订阅消息字段 [%s] 必须是标量或包含 value 的数组。',
                    (string) $key,
                ));
            }

            $formatted[(string) $key] = ['value' => (string) $value];
        }

        return $formatted;
    }
}
