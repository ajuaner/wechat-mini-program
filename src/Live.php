<?php

declare(strict_types=1);

namespace Qinii\WechatMiniProgram;

use Qinii\WechatMiniProgram\Exception\MiniProgramException;
use Qinii\WechatMiniProgram\Support\AbstractApi;

/**
 * 小程序直播间、商品、成员和长期订阅管理能力。
 */
final class Live extends AbstractApi
{
    private const ROOM_CREATE_ENDPOINT = 'wxaapi/broadcast/room/create';
    private const ROOM_INFO_ENDPOINT = 'wxa/business/getliveinfo';
    private const ROOM_DELETE_ENDPOINT = 'wxaapi/broadcast/room/deleteroom';
    private const ROOM_ADD_GOODS_ENDPOINT = 'wxaapi/broadcast/room/addgoods';
    private const ROOM_EDIT_ENDPOINT = 'wxaapi/broadcast/room/editroom';
    private const ROOM_PUSH_URL_ENDPOINT = 'wxaapi/broadcast/room/getpushurl';
    private const ROOM_SHARED_CODE_ENDPOINT = 'wxaapi/broadcast/room/getsharedcode';
    private const ROOM_GET_SUB_ANCHOR_ENDPOINT = 'wxaapi/broadcast/room/getsubanchor';
    private const ROOM_MODIFY_SUB_ANCHOR_ENDPOINT = 'wxaapi/broadcast/room/modifysubanchor';
    private const ROOM_DELETE_SUB_ANCHOR_ENDPOINT = 'wxaapi/broadcast/room/deletesubanchor';
    private const ROOM_ADD_SUB_ANCHOR_ENDPOINT = 'wxaapi/broadcast/room/addsubanchor';
    private const ROOM_DELETE_GOODS_ENDPOINT = 'wxaapi/broadcast/goods/deleteInRoom';
    private const ROOM_PUSH_GOODS_ENDPOINT = 'wxaapi/broadcast/goods/push';
    private const ROOM_GOODS_ON_SALE_ENDPOINT = 'wxaapi/broadcast/goods/onsale';
    private const ROOM_SORT_GOODS_ENDPOINT = 'wxaapi/broadcast/goods/sort';
    private const ROOM_MODIFY_ASSISTANT_ENDPOINT = 'wxaapi/broadcast/room/modifyassistant';
    private const ROOM_GET_ASSISTANTS_ENDPOINT = 'wxaapi/broadcast/room/getassistantlist';
    private const ROOM_REMOVE_ASSISTANT_ENDPOINT = 'wxaapi/broadcast/room/removeassistant';
    private const ROOM_ADD_ASSISTANT_ENDPOINT = 'wxaapi/broadcast/room/addassistant';
    private const ROOM_UPDATE_COMMENT_ENDPOINT = 'wxaapi/broadcast/room/updatecomment';
    private const ROOM_UPDATE_FEEDS_ENDPOINT = 'wxaapi/broadcast/room/updatefeedpublic';
    private const ROOM_UPDATE_CUSTOMER_SERVICE_ENDPOINT = 'wxaapi/broadcast/room/updatekf';
    private const ROOM_UPDATE_REPLAY_ENDPOINT = 'wxaapi/broadcast/room/updatereplay';
    private const ROOM_DOWNLOAD_GOODS_VIDEO_ENDPOINT = 'wxaapi/broadcast/goods/getVideo';
    private const ROOM_SET_GOODS_KEY_ENDPOINT = 'wxaapi/broadcast/goods/setkey';
    private const ROOM_GET_GOODS_KEY_ENDPOINT = 'wxaapi/broadcast/goods/getkey';
    private const GOODS_ADD_ENDPOINT = 'wxaapi/broadcast/goods/add';
    private const GOODS_AUDIT_ENDPOINT = 'wxaapi/broadcast/goods/audit';
    private const GOODS_WAREHOUSE_ENDPOINT = 'wxa/business/getgoodswarehouse';
    private const GOODS_RESET_AUDIT_ENDPOINT = 'wxaapi/broadcast/goods/resetaudit';
    private const GOODS_UPDATE_ENDPOINT = 'wxaapi/broadcast/goods/update';
    private const GOODS_APPROVED_ENDPOINT = 'wxaapi/broadcast/goods/getapproved';
    private const GOODS_DELETE_ENDPOINT = 'wxaapi/broadcast/goods/delete';
    private const ROLE_ADD_ENDPOINT = 'wxaapi/broadcast/role/addrole';
    private const ROLE_DELETE_ENDPOINT = 'wxaapi/broadcast/role/deleterole';
    private const ROLE_LIST_ENDPOINT = 'wxaapi/broadcast/role/getrolelist';
    private const FOLLOWERS_ENDPOINT = 'wxa/business/get_wxa_followers';
    private const PUSH_MESSAGE_ENDPOINT = 'wxa/business/push_message';

    /**
     * 创建直播间。
     *
     * 图片字段需要传入临时素材接口返回的 media_id。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createRoom(array $payload): array
    {
        return $this->post(
            self::ROOM_CREATE_ENDPOINT,
            $this->requireArray($payload, '创建直播间参数'),
        );
    }

    /**
     * 获取直播间列表。
     *
     * @return array<string, mixed>
     */
    public function getRooms(int $start = 0, int $limit = 10): array
    {
        $this->validatePage($start, $limit);

        return $this->post(self::ROOM_INFO_ENDPOINT, [
            'start' => $start,
            'limit' => $limit,
        ]);
    }

    /**
     * 获取指定直播间的回放列表。
     *
     * @return array<string, mixed>
     */
    public function getPlaybacks(
        int $roomId,
        int $start = 0,
        int $limit = 10,
    ): array {
        $this->validatePage($start, $limit);

        return $this->post(self::ROOM_INFO_ENDPOINT, [
            'action' => 'get_replay',
            'room_id' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'start' => $start,
            'limit' => $limit,
        ]);
    }

    /**
     * 删除直播间。
     *
     * @return array<string, mixed>
     */
    public function deleteRoom(int $roomId): array
    {
        return $this->post(self::ROOM_DELETE_ENDPOINT, [
            'id' => $this->requirePositiveInt($roomId, '直播间 ID'),
        ]);
    }

    /**
     * 将审核通过的商品导入直播间。
     *
     * @param array<int, int> $goodsIds
     * @return array<string, mixed>
     */
    public function importGoods(int $roomId, array $goodsIds): array
    {
        return $this->post(self::ROOM_ADD_GOODS_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'ids' => array_values($this->requireArray($goodsIds, '商品 ID')),
        ]);
    }

    /**
     * 编辑直播间。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function editRoom(array $payload): array
    {
        return $this->post(
            self::ROOM_EDIT_ENDPOINT,
            $this->requireArray($payload, '编辑直播间参数'),
        );
    }

    /**
     * 获取直播间推流地址。
     *
     * @return array<string, mixed>
     */
    public function getPushUrl(int $roomId): array
    {
        return $this->get(self::ROOM_PUSH_URL_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
        ]);
    }

    /**
     * 获取直播间分享二维码。
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function getSharedCode(
        int $roomId,
        array $options = [],
    ): array {
        return $this->get(self::ROOM_SHARED_CODE_ENDPOINT, array_merge(
            $options,
            ['roomId' => $this->requirePositiveInt($roomId, '直播间 ID')],
        ));
    }

    /**
     * 获取直播间主播副号。
     *
     * @return array<string, mixed>
     */
    public function getSubAnchor(int $roomId): array
    {
        return $this->get(self::ROOM_GET_SUB_ANCHOR_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
        ]);
    }

    /**
     * 添加直播间主播副号。
     *
     * @return array<string, mixed>
     */
    public function addSubAnchor(int $roomId, string $wechatId): array
    {
        return $this->post(self::ROOM_ADD_SUB_ANCHOR_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'username' => $this->requireString($wechatId, '主播副号微信号'),
        ]);
    }

    /**
     * 修改直播间主播副号。
     *
     * @return array<string, mixed>
     */
    public function modifySubAnchor(int $roomId, string $wechatId): array
    {
        return $this->post(self::ROOM_MODIFY_SUB_ANCHOR_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'username' => $this->requireString($wechatId, '主播副号微信号'),
        ]);
    }

    /**
     * 删除直播间主播副号。
     *
     * @return array<string, mixed>
     */
    public function deleteSubAnchor(int $roomId): array
    {
        return $this->post(self::ROOM_DELETE_SUB_ANCHOR_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
        ]);
    }

    /**
     * 从直播间删除商品。
     *
     * @return array<string, mixed>
     */
    public function deleteRoomGoods(int $roomId, int $goodsId): array
    {
        return $this->post(self::ROOM_DELETE_GOODS_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'goodsId' => $this->requirePositiveInt($goodsId, '商品 ID'),
        ]);
    }

    /**
     * 在直播间推送商品。
     *
     * @return array<string, mixed>
     */
    public function pushGoods(int $roomId, int $goodsId): array
    {
        return $this->post(self::ROOM_PUSH_GOODS_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'goodsId' => $this->requirePositiveInt($goodsId, '商品 ID'),
        ]);
    }

    /**
     * 设置直播间商品的上下架状态。
     *
     * @return array<string, mixed>
     */
    public function setGoodsOnSale(
        int $roomId,
        int $goodsId,
        bool|int $onSale,
    ): array {
        return $this->post(self::ROOM_GOODS_ON_SALE_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'goodsId' => $this->requirePositiveInt($goodsId, '商品 ID'),
            'onSale' => (int) (bool) $onSale,
        ]);
    }

    /**
     * 调整直播间商品排序。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function sortGoods(array $payload): array
    {
        return $this->post(
            self::ROOM_SORT_GOODS_ENDPOINT,
            $this->requireArray($payload, '商品排序参数'),
        );
    }

    /**
     * 添加直播间小助手。
     *
     * @param array<int, array<string, string>> $users
     * @return array<string, mixed>
     */
    public function addAssistants(int $roomId, array $users): array
    {
        return $this->post(self::ROOM_ADD_ASSISTANT_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'users' => array_values($this->requireArray($users, '助手列表')),
        ]);
    }

    /**
     * 修改直播间小助手信息。
     *
     * @return array<string, mixed>
     */
    public function modifyAssistant(
        int $roomId,
        string $wechatId,
        string $nickname,
    ): array {
        return $this->post(self::ROOM_MODIFY_ASSISTANT_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'username' => $this->requireString($wechatId, '助手微信号'),
            'nickname' => $this->requireString($nickname, '助手昵称'),
        ]);
    }

    /**
     * 查询直播间小助手列表。
     *
     * @return array<string, mixed>
     */
    public function getAssistants(int $roomId): array
    {
        return $this->get(self::ROOM_GET_ASSISTANTS_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
        ]);
    }

    /**
     * 删除直播间小助手。
     *
     * @return array<string, mixed>
     */
    public function removeAssistant(int $roomId, string $wechatId): array
    {
        return $this->post(self::ROOM_REMOVE_ASSISTANT_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'username' => $this->requireString($wechatId, '助手微信号'),
        ]);
    }

    /**
     * 设置直播间禁言状态。
     *
     * @return array<string, mixed>
     */
    public function setComment(
        int $roomId,
        bool|int $disabled,
    ): array {
        return $this->post(self::ROOM_UPDATE_COMMENT_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'banComment' => (int) (bool) $disabled,
        ]);
    }

    /**
     * 设置直播间是否被官方收录。
     *
     * @return array<string, mixed>
     */
    public function setFeedsPublic(
        int $roomId,
        bool|int $enabled,
    ): array {
        return $this->post(self::ROOM_UPDATE_FEEDS_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'isFeedsPublic' => (int) (bool) $enabled,
        ]);
    }

    /**
     * 设置直播间客服功能状态。
     *
     * @return array<string, mixed>
     */
    public function setCustomerService(
        int $roomId,
        bool|int $disabled,
    ): array {
        return $this->post(self::ROOM_UPDATE_CUSTOMER_SERVICE_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'closeKf' => (int) (bool) $disabled,
        ]);
    }

    /**
     * 设置直播间回放功能状态。
     *
     * @return array<string, mixed>
     */
    public function setReplay(
        int $roomId,
        bool|int $disabled,
    ): array {
        return $this->post(self::ROOM_UPDATE_REPLAY_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'closeReplay' => (int) (bool) $disabled,
        ]);
    }

    /**
     * 获取商品讲解视频下载信息。
     *
     * @return array<string, mixed>
     */
    public function downloadGoodsVideo(int $roomId, int $goodsId): array
    {
        return $this->post(self::ROOM_DOWNLOAD_GOODS_VIDEO_ENDPOINT, [
            'roomId' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'goodsId' => $this->requirePositiveInt($goodsId, '商品 ID'),
        ]);
    }

    /**
     * 设置直播挂件全局密钥。
     *
     * @param array<int, string> $goodsKeys
     * @return array<string, mixed>
     */
    public function setDefaultGoodsKey(array $goodsKeys): array
    {
        return $this->post(self::ROOM_SET_GOODS_KEY_ENDPOINT, [
            'goodsKey' => array_values(
                $this->requireArray($goodsKeys, '直播挂件密钥'),
            ),
        ]);
    }

    /**
     * 获取直播挂件全局密钥。
     *
     * @return array<string, mixed>
     */
    public function getDefaultGoodsKey(): array
    {
        return $this->get(self::ROOM_GET_GOODS_KEY_ENDPOINT);
    }

    /**
     * 添加并提审直播商品。
     *
     * @param array<string, mixed> $goodsInfo
     * @return array<string, mixed>
     */
    public function addGoods(array $goodsInfo): array
    {
        return $this->post(self::GOODS_ADD_ENDPOINT, [
            'goodsInfo' => $this->requireArray($goodsInfo, '商品信息'),
        ]);
    }

    /**
     * 重新提交商品审核。
     *
     * @return array<string, mixed>
     */
    public function resubmitGoodsAudit(int $goodsId): array
    {
        return $this->post(self::GOODS_AUDIT_ENDPOINT, [
            'goodsId' => $this->requirePositiveInt($goodsId, '商品 ID'),
        ]);
    }

    /**
     * 获取商品信息及审核状态。
     *
     * @param array<int, int> $goodsIds
     * @return array<string, mixed>
     */
    public function getGoodsWarehouse(array $goodsIds): array
    {
        return $this->post(self::GOODS_WAREHOUSE_ENDPOINT, [
            'goods_ids' => array_values(
                $this->requireArray($goodsIds, '商品 ID'),
            ),
        ]);
    }

    /**
     * 撤回商品审核。
     *
     * @return array<string, mixed>
     */
    public function resetGoodsAudit(int $goodsId, int $auditId): array
    {
        return $this->post(self::GOODS_RESET_AUDIT_ENDPOINT, [
            'goodsId' => $this->requirePositiveInt($goodsId, '商品 ID'),
            'auditId' => $this->requirePositiveInt($auditId, '审核 ID'),
        ]);
    }

    /**
     * 更新直播商品信息。
     *
     * @param array<string, mixed> $goodsInfo
     * @return array<string, mixed>
     */
    public function updateGoods(array $goodsInfo): array
    {
        return $this->post(self::GOODS_UPDATE_ENDPOINT, [
            'goodsInfo' => $this->requireArray($goodsInfo, '商品信息'),
        ]);
    }

    /**
     * 获取指定审核状态的直播商品列表。
     *
     * @return array<string, mixed>
     */
    public function getApprovedGoods(
        int $status = 2,
        int $offset = 0,
        int $limit = 30,
    ): array {
        $this->validatePage($offset, $limit);

        return $this->get(self::GOODS_APPROVED_ENDPOINT, [
            'offset' => $offset,
            'limit' => $limit,
            'status' => $status,
        ]);
    }

    /**
     * 删除直播商品。
     *
     * @return array<string, mixed>
     */
    public function deleteGoods(int $goodsId): array
    {
        return $this->post(self::GOODS_DELETE_ENDPOINT, [
            'goodsId' => $this->requirePositiveInt($goodsId, '商品 ID'),
        ]);
    }

    /**
     * 为小程序成员设置直播角色。
     *
     * @return array<string, mixed>
     */
    public function addRole(string $wechatId, int $role): array
    {
        return $this->post(self::ROLE_ADD_ENDPOINT, [
            'username' => $this->requireString($wechatId, '成员微信号'),
            'role' => $this->requirePositiveInt($role, '成员角色'),
        ]);
    }

    /**
     * 移除小程序成员的直播角色。
     *
     * @return array<string, mixed>
     */
    public function deleteRole(string $wechatId, int $role): array
    {
        return $this->post(self::ROLE_DELETE_ENDPOINT, [
            'username' => $this->requireString($wechatId, '成员微信号'),
            'role' => $this->requirePositiveInt($role, '成员角色'),
        ]);
    }

    /**
     * 查询小程序直播成员列表。
     *
     * @return array<string, mixed>
     */
    public function getRoles(
        int $offset = 0,
        int $limit = 10,
        string $keyword = '',
    ): array {
        $this->validatePage($offset, $limit);

        return $this->get(self::ROLE_LIST_ENDPOINT, [
            'offset' => $offset,
            'limit' => $limit,
            'keyword' => trim($keyword),
        ]);
    }

    /**
     * 获取小程序直播长期订阅用户。
     *
     * @return array<string, mixed>
     */
    public function getFollowers(
        string $pageBreak = '',
        int $limit = 2000,
    ): array {
        if ($limit < 1) {
            throw new MiniProgramException('长期订阅用户数量必须大于零。');
        }

        $payload = ['limit' => $limit];

        if (trim($pageBreak) !== '') {
            $payload['page_break'] = trim($pageBreak);
        }

        return $this->post(self::FOLLOWERS_ENDPOINT, $payload);
    }

    /**
     * 向长期订阅用户发送直播开始事件。
     *
     * @param array<int, string> $openIds
     * @return array<string, mixed>
     */
    public function pushMessage(int $roomId, array $openIds): array
    {
        return $this->post(self::PUSH_MESSAGE_ENDPOINT, [
            'room_id' => $this->requirePositiveInt($roomId, '直播间 ID'),
            'user_openid' => array_values(
                $this->requireArray($openIds, '用户 openid'),
            ),
        ]);
    }

    /**
     * 调用尚未封装的小程序直播接口。
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
     * 发送直播接口 JSON 请求。
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function post(string $endpoint, array $payload = []): array
    {
        return $this->responseToArray(
            $this->client()->postJson($endpoint, $payload),
        );
    }

    /**
     * 发送直播接口查询请求。
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function get(string $endpoint, array $query = []): array
    {
        return $this->responseToArray(
            $this->client()->get($endpoint, ['query' => $query]),
        );
    }

    /** 校验直播接口分页参数。 */
    private function validatePage(int $start, int $limit): void
    {
        if ($start < 0 || $limit < 1) {
            throw new MiniProgramException('直播接口分页参数不正确。');
        }
    }
}
