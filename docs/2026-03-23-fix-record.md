# 2026-03-23 修复留档

## 目标

修复以下入口和底层解析接口:

- `short_videos/sv1.php`
- `short_videos/sv2.php`
- `api/douyin/douyin.php`
- `api/kuaishou/ksjx.php`

并确保以下平台链路可用:

- B 站
- 快手
- 抖音
- 小红书

## 根因

### 1. 聚合入口路由指错

`sv1.php` 和 `sv2.php` 之前请求的不是仓库里真实存在的脚本路径，导致上游直接 404，聚合层只能报“接口返回格式不正确”或者“Syntax error”。

### 2. 聚合入口取参不完整

当用户直接把嵌套视频链接拼在 `?url=` 后面、却没有做 URL 编码时，原来的取参方式会把 `&authorId=...` 这类后续参数截断，结果传给底层接口的根本不是完整链接。

### 3. 抖音分享页解析链路失效

抖音原逻辑依赖的页面和字段已经不稳，拿不到稳定结构时就容易挂。

### 4. 快手解析过度依赖跳转和页面结构

快手原逻辑在以下地方都比较脆:

- 强依赖重定向拿作品 ID
- 超时设置太短
- 请求头不够完整
- `__APOLLO_STATE__` 结构兼容性不够

## 修复内容

### 提交 `64b2395`

修复 `short_videos/sv1.php`、`short_videos/sv2.php`:

- 从原始查询串提取完整 `url`，兼容未编码嵌套链接
- 平台分发改为仓库中真实存在的脚本路径
- 补充上游请求异常和非 JSON 返回兜底

### 提交 `715db84`

修复抖音与快手底层解析:

- `api/douyin/DouyinParser.php`
  - 改为走 `https://www.iesdouyin.com/share/video/{id}`、`share/note/{id}`、`share/slides/{id}`
  - 从 `window._ROUTER_DATA` 提取详情
  - 增加分享页请求头
- `api/kuaishou/KuaishouSpider.php`
  - 如果原始链接已包含作品 ID，则直接解析
  - 兼容 `defaultClient` 和顶层 `__APOLLO_STATE__`

### 提交 `422407b`

继续修快手请求稳定性:

- 超时调整为更合理的秒级配置
- 增加连接超时
- 增加 `CURLOPT_ENCODING`
- 增加 `Accept-Encoding` 和 `Referer`

## 已验证结果

线上站点 `https://api2.jumh989.gq` 已实际 `curl` 验证通过:

- `short_videos/sv1.php`
  - 抖音: 成功
  - 快手: 成功
  - 小红书: 成功
- `short_videos/sv2.php`
  - 抖音: 成功
  - 快手: 成功
  - 小红书: 成功
- 底层接口
  - `api/douyin/douyin.php`: 成功
  - `api/kuaishou/ksjx.php`: 成功
  - `api/xiaohongshu/xhsjx.php`: 成功
  - `api/bilibili/index.php`: 成功

## 当前线上状态

当前确认可用:

- B 站
- 快手
- 抖音
- 小红书
- 聚合入口 `sv1.php`
- 聚合入口 `sv2.php`

## 后续建议

1. 宝塔 Git 部署加上“拉取后执行脚本”，保住 `sv1.php` / `sv2.php` 本地稳定版本。
2. 后续如果仓库中这两个文件再次调整，先在线下或测试环境过一遍，再决定是否取消覆盖策略。
