# 项目记忆文件

最后更新：2026-03-26（补充近几日完整工作纪要）

## 项目定位

- 项目名称：`api2.jumh989.gq`
- 主要用途：短视频解析接口站，包含聚合入口、单平台解析接口、独立门户展示站
- 线上主域名：`https://api2.jumh989.gq`
- 门户访问地址：`https://api2.jumh989.gq/www/`

## 目录约定

- `short_videos/`
  说明：聚合入口，目前重点是 `sv1.php`、`sv2.php`
- `api/`
  说明：各平台单独解析接口
- `www/`
  说明：独立门户站目录，只负责展示、文档、在线体验，不直接改主接口业务
- `docs/`
  说明：项目文档、修复记录、同步说明、接口清单、记忆文件

## 当前关键约定

### 1. token 鉴权

- 所有公开解析接口统一要求 `?token=xxx`
- 服务端真实 token 文件：
  `/www/wwwroot/api2.jumh989.gq/.api_token`
- `.api_token` 支持多 token 写法：
  `token|使用人|过期时间|备注`
- 不过期统一写 `never`
- 需要失效控制时写具体时间，例如：
  `2026-12-31 23:59:59`

### 2. 门户目录

- 门户目录名已经统一改为小写 `www`
- 不要再改回 `WWW`、`site_portal` 之类大小写或旧名字
- 宝塔部署目录示例：
  `/www/wwwroot/api2.jumh989.gq/www`
- 门户配置与入口重点文件：
  - `www/index.php`
  - `www/playground_proxy.php`
  - `www/config/catalog.php`
  - `www/assets/site.css`
  - `www/assets/site.js`

### 3. 门户站内体验

- 访客模式默认 token 文件：
  `/www/wwwroot/api2.jumh989.gq/www/.portal_token`
- 示例文件：
  `www/.portal_token.example`
- 留空 token 时会走：
  `www/playground_proxy.php`
- 同一 IP 每天最多解析 10 次
- 次数记录文件：
  `www/storage/playground_limits.json`
- 如果 `www/storage/` 没写权限，程序会降级写入系统临时目录

### 4. 门户与 API 的关系

- `www/` 只是门户，不要把 API 主逻辑挪进来
- API 主入口仍旧保持原路径：
  - `short_videos/sv1.php`
  - `short_videos/sv2.php`
  - `api/...`
- 用户曾明确要求撤回“根域名 `/` 直接显示门户”的改动
- 当前约定仍然是：
  - 门户走 `/www/`
  - API 继续走原接口路径

## 已知接口情况

- 聚合入口：
  - `short_videos/sv1.php`
  - `short_videos/sv2.php`
- 已重点验证过的平台链路：
  - 抖音
  - 快手
  - 小红书
  - B 站
- 完整接口清单文档：
  `docs/api-endpoints-with-token.md`

## 相关文档索引

- 接口清单：
  `docs/api-endpoints-with-token.md`
- 2026-03-23 修复记录：
  `docs/2026-03-23-fix-record.md`
- 宝塔 Git 拉取后保留本地覆盖：
  `docs/baota-git-local-overrides.md`
- 上游同步规程：
  `docs/upstream-sync-guide.md`
- 门户目录说明：
  `www/README.md`

## 近几日工作纪要

### 2026-03-23

- 修复聚合入口 `sv1.php`、`sv2.php`
- 修复抖音、快手底层解析链路
- 修复记录见：
  `docs/2026-03-23-fix-record.md`

### 2026-03-24

- 所有公开入口统一接入 token 鉴权
- 服务端 token 文件固定为：
  `/www/wwwroot/api2.jumh989.gq/.api_token`
- `.api_token` 从单 token 扩展为支持多 token 格式：
  `token|使用人|过期时间|备注`
- token 过期规则明确为：
  - `never`
  - 具体时间，例如 `2026-12-31 23:59:59`
- 已验证过：
  - 正确 token 可调用
  - 不带 token 返回 `缺少token`
  - 错误 token 返回 `token错误`
  - 过期 token 返回 `token已过期`
- 输出了一份接口清单文档：
  `docs/api-endpoints-with-token.md`

### 2026-03-24 到 2026-03-25

- 为宝塔 Git 部署增加“本地覆盖保护”方案
- 核心脚本：
  `deploy/baota-local-overrides.sh`
- 作用：
  防止宝塔每次 Git 拉取后，把线上已修好的 `short_videos/sv1.php`、`short_videos/sv2.php` 覆盖掉
- 配套文档：
  `docs/baota-git-local-overrides.md`
- 同时补了一份上游同步规程，明确以后不能无脑整仓库跟上游同步：
  `docs/upstream-sync-guide.md`
- 固定原则：
  - 原作者仓库 = 参考源
  - 你的仓库 = 生产主仓
  - 宝塔只拉你的仓库

### 2026-03-25 到 2026-03-26

- 新增独立门户站
- 门户最开始使用过 `site_portal` 作为目录名
- 后来按用户要求改成 `WWW`
- 最终又统一为小写 `www`
- 当前最终有效目录只认：
  `www`
- 不要再改回 `site_portal` 或 `WWW`

### 门户站这几天做过的主要内容

- 门户入口、首页、详情页、在线调试区已经接上
- 详情页包含：
  - `接口详情`
  - `请求参数`
  - `示例代码`
  - `返回示例`
  - `在线调试`
- 在线调试支持原地切换，不再点一下滚到页面下面
- 支持 `GET / POST` 切换
- 支持请求预览联动
- 支持访客模式默认 token
- 支持同 IP 每天最多 10 次解析限制
- 访客模式代理：
  `www/playground_proxy.php`
- 访客默认 token 文件：
  `/www/wwwroot/api2.jumh989.gq/www/.portal_token`
- 访客默认 token 示例文件：
  `www/.portal_token.example`
- 限额记录文件：
  `www/storage/playground_limits.json`
- 如果 `www/storage/` 不可写，会降级使用系统临时目录：
  `mumu_api_www`

### 门户站这几天做过的样式与交互类调整

- 首页 API 卡片改为整张卡片可点击进入详情页
- 卡片内部原来的“接口详情 / 在线体验”按钮已去掉
- 首页搜索、分类、状态筛选做过多轮缩尺寸和靠近参考站样式的调整
- 详情页头部做过多轮收缩，按钮位置改到右上
- 代码高亮范围扩展到：
  - 示例代码
  - 返回示例
  - 在线调试返回结果
- 修过 JSON 高亮令牌未还原的问题，避免出现 `__CODE_TOKEN_x__`
- 清掉过门户首页残留的多余 badge 文案

### 门户站当前仍需记住的实现细节

- 门户现在走：
  `https://api2.jumh989.gq/www/`
- 用户曾要求把门户直接挂到根域名 `/`
- 该改动后来已明确撤回
- 当前不要再默认把根域名改成门户
- 门户本地缓存键当前为：
  - token：`mumu_api_portal_token`
  - 主题：`mumu_api_portal_theme`
- 为兼容旧数据，前端仍会读取旧 token 键：
  `shine_portal_token`

### 2026-03-26

- 新增本项目固定记忆文件：
  `docs/project-memory.md`
- 更新项目级 `AGENTS.md`
- 明确规则：
  每次进入本项目先读 `AGENTS.md` 和 `docs/project-memory.md`
- 每次涉及结构、部署、鉴权、关键约定变化后，都要回写本文件

## 当前仓库状态提醒

- 项目级 `AGENTS.md` 已存在于仓库根目录
- 当前项目要求保留“记忆文件”，本文件就是固定记忆入口
- 后续每次处理任务前，应优先阅读：
  - `AGENTS.md`
  - `docs/project-memory.md`
- 后续每次任务结束后，应补记：
  - 本次做了什么
  - 改了哪些关键路径或约定
  - 有什么未完成项或坑点

## 当前待办

- 继续迭代 `www/` 门户样式与交互，方向仍然是更贴近参考站，减少“AI 味”
- 需要继续盯住首页 API 卡片密度、间距、信息层级，保持 4 列但不要拥挤
- 需要继续盯住详情页头部、代码示例区、在线调试区的一致性
- 如果后续再次调整门户目录或访问入口，必须先同步更新：
  - `docs/project-memory.md`
  - `www/README.md`
  - `.gitignore`
  - 门户代码里的默认路径
- 如果后续准备推送仓库，还要先确认当前未跟踪内容是否都应进入版本库

## 已知坑点 / 不要再踩

### 1. 门户目录名坑

- 门户目录名已经来回折腾过：
  - `site_portal`
  - `WWW`
  - `www`
- 当前最终只认小写 `www`
- Linux 服务器区分大小写，不要再说“WWW 和 www 差不多”

### 2. 根域名入口坑

- 用户曾要求把门户直接挂到 `https://api2.jumh989.gq/`
- 该改动后来已明确撤回
- 当前不要默认再去改根域名入口
- 现在仍然是：
  - 门户：`/www/`
  - API：原路径不动

### 3. 宝塔覆盖坑

- 宝塔 Git 拉取会覆盖线上文件
- `short_videos/sv1.php`、`short_videos/sv2.php` 是高风险文件
- 如果线上版本已确认稳定，继续依赖：
  `deploy/baota-local-overrides.sh`
- 相关说明见：
  `docs/baota-git-local-overrides.md`

### 4. 上游同步坑

- 不要整仓库无脑跟原作者上游同步
- 当前仓库已经是生产主仓，不是上游镜像
- 以后同步遵循：
  原作者仓库 -> 本地挑改动 -> 推到你的仓库 -> 宝塔拉你的仓库
- 相关说明见：
  `docs/upstream-sync-guide.md`

### 5. token 配置坑

- 线上真实 token 文件是：
  `/www/wwwroot/api2.jumh989.gq/.api_token`
- 门户访客默认 token 文件是：
  `/www/wwwroot/api2.jumh989.gq/www/.portal_token`
- 这两个都不要提交到公开仓库
- `.api_token` 建议始终使用统一格式：
  `token|使用人|过期时间|备注`

### 6. 门户本地缓存坑

- 门户当前本地缓存键是：
  - `mumu_api_portal_token`
  - `mumu_api_portal_theme`
- 旧键 `shine_portal_token` 仍在兼容读取
- 如果后面又改键名，记得兼容旧值，别把已有浏览器缓存体验搞炸

### 7. 本地验证能力坑

- 当前本地能做的验证：
  - `node --check`
  - 文本检索、自检、结构核对
- 当前本地不能直接做的验证：
  - `php -l`
  - 本地跑 PHP 页面
- 所以涉及 PHP 改动时，要明确告诉用户哪些只做了静态检查，哪些要上宝塔实测

## 下次开工前优先确认

- 用户本次要继续改的是：
  - API 主逻辑
  - token 鉴权
  - 宝塔部署
  - 门户 `www/`
- 如果是改门户，先看：
  - `www/index.php`
  - `www/assets/site.css`
  - `www/assets/site.js`
  - `www/config/catalog.php`
- 如果是改鉴权或接口调用，先看：
  - `docs/api-endpoints-with-token.md`
  - `.api_token` 相关逻辑
  - 聚合入口与单平台入口

## 后续操作规则

- 非用户明确要求，不要随便改门户目录名
- 非用户明确要求，不要再把根域名 `/` 改成直接跑门户
- 需要提交、推送、删除、强覆盖时，必须按项目规则先确认
- 如果后续继续改门户样式，优先基于 `www/` 这套现状迭代，不要重新另起一套目录

## 本次补记

- 新增项目记忆文件：`docs/project-memory.md`
- 目的：让后续会话能快速恢复项目上下文，减少重复解释和重复踩坑
- 本次已把 2026-03-23 到 2026-03-26 的主要工作、目录变更、部署方案、门户演进和 token 规则补充进来
