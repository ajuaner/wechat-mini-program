# 功能清单

本文档列出当前微信小程序扩展包已经实现和暂未直接封装的能力。

## 门面和公共能力

- `MiniProgram::create()`：使用数组配置创建门面。
- `account()`：切换账号组并返回独立门面。
- `setHttpClient()`：注入 Symfony 或 Swoole HTTP 客户端。
- `setCache()`：注入 PSR-16 缓存。
- `application()`：获取底层 EasyWeChat 小程序应用。

## 登录和用户信息

- `Auth::codeToSession()`：使用登录 code 换取 openid 和 session_key。
- `Auth::decryptData()`：使用 session_key 解密旧版加密数据。
- `Auth::getPhoneNumber()`：使用手机号授权 code 获取手机号。

## 小程序码与链接

- `QrCode::unlimited()`：获取不限制数量的小程序码二进制内容。
- `QrCode::saveUnlimited()`：获取小程序码并保存到文件。
- `UrlLink::generate()`：生成小程序 URL Link。
- `UrlLink::generateRaw()`：使用完整微信参数生成 URL Link。
- `UrlLink::query()`：查询 URL Link 配置。

## 临时素材和内容安全

- `Media::upload()`：上传图片、语音、视频或缩略图临时素材。
- `Media::uploadImage()`：上传临时图片素材。
- `ContentSecurity::checkText()`：检测文本内容。
- `ContentSecurity::checkMedia()`：异步检测图片或音频内容。

## 订阅消息

- `Subscribe::getCategories()`：获取小程序账号类目。
- `Subscribe::getPublicTemplates()`：获取类目下的公共模板标题。
- `Subscribe::getTemplateKeywords()`：获取公共模板关键词。
- `Subscribe::addTemplate()`：选用公共模板。
- `Subscribe::getTemplates()`：获取已添加模板。
- `Subscribe::deleteTemplate()`：删除已添加模板。
- `Subscribe::send()`：格式化并发送订阅消息。
- `Subscribe::sendRaw()`：发送完整微信订阅消息参数。

## 交易发货

- `Shipping::upload()`：使用完整微信参数录入发货信息。
- `Shipping::uploadByOutTradeNo()`：按商户订单号组装并录入发货信息。
- `Shipping::uploadCombined()`：录入合单发货信息。
- `Shipping::getOrder()`：查询单笔支付单的发货状态。
- `Shipping::getOrderList()`：查询支付单列表。
- `Shipping::notifyConfirmReceive()`：提醒用户确认收货。
- `Shipping::setMessageJumpPath()`：设置发货消息跳转路径。
- `Shipping::isTradeManaged()`：查询是否开通发货信息管理服务。
- `Shipping::isManagementConfirmationCompleted()`：查询是否完成交易结算管理确认。
- `Shipping::getDeliveryList()`：获取运力 ID 列表。
- `Shipping::raw()`：调用尚未封装的交易管理接口。

## 直播间管理

- `Live::createRoom()`：创建直播间。
- `Live::getRooms()`：获取直播间列表。
- `Live::getPlaybacks()`：获取直播间回放。
- `Live::deleteRoom()`：删除直播间。
- `Live::editRoom()`：编辑直播间。
- `Live::getPushUrl()`：获取推流地址。
- `Live::getSharedCode()`：获取直播间分享二维码。
- `Live::getSubAnchor()`：获取主播副号。
- `Live::addSubAnchor()`：添加主播副号。
- `Live::modifySubAnchor()`：修改主播副号。
- `Live::deleteSubAnchor()`：删除主播副号。
- `Live::importGoods()`：向直播间导入商品。
- `Live::deleteRoomGoods()`：从直播间删除商品。
- `Live::pushGoods()`：在直播间推送商品。
- `Live::setGoodsOnSale()`：设置直播间商品上下架状态。
- `Live::sortGoods()`：调整直播间商品排序。
- `Live::setComment()`：设置直播间禁言状态。
- `Live::setFeedsPublic()`：设置官方收录状态。
- `Live::setCustomerService()`：设置客服功能状态。
- `Live::setReplay()`：设置回放功能状态。
- `Live::downloadGoodsVideo()`：获取商品讲解视频下载信息。
- `Live::setDefaultGoodsKey()`：设置直播挂件全局密钥。
- `Live::getDefaultGoodsKey()`：获取直播挂件全局密钥。

## 直播商品管理

- `Live::addGoods()`：添加并提审商品。
- `Live::resubmitGoodsAudit()`：重新提交商品审核。
- `Live::getGoodsWarehouse()`：获取商品信息和审核状态。
- `Live::resetGoodsAudit()`：撤回商品审核。
- `Live::updateGoods()`：更新商品信息。
- `Live::getApprovedGoods()`：获取商品列表。
- `Live::deleteGoods()`：删除商品。

## 直播成员和长期订阅

- `Live::addAssistants()`：添加直播间小助手。
- `Live::modifyAssistant()`：修改直播间小助手。
- `Live::getAssistants()`：查询直播间小助手。
- `Live::removeAssistant()`：删除直播间小助手。
- `Live::addRole()`：设置成员角色。
- `Live::deleteRole()`：移除成员角色。
- `Live::getRoles()`：查询成员列表。
- `Live::getFollowers()`：获取长期订阅用户。
- `Live::pushMessage()`：发送直播开始事件。
- `Live::raw()`：调用尚未封装的直播接口。

## 暂未直接封装

以下能力没有在当前 CRMEB 项目中确认到稳定业务调用，因此暂不增加独立公开方法：

- 客服消息和小程序消息回调；
- 数据分析；
- 插件管理；
- 即时配送和完整物流助手；
- 特殊发货报备、品牌申请和交易类型变更；
- 附近小程序、OCR、人脸核身和虚拟支付等垂直能力。

未封装能力可以通过 `MiniProgram::application()` 获取 EasyWeChat 应用调用；交易发货和直播的新接口也可以先通过对应模块的 `raw()` 调用。
