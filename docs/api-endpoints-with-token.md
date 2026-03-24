# 接口清单

当前线上域名:

```text
https://api2.jumh989.gq
```

调用规则:

```text
所有接口都必须携带 token 参数
```

你的服务端 token 保存在:

```text
/www/wwwroot/api2.jumh989.gq/.api_token
```

调用时请把下面文档里的 `你的token` 替换成你自己的真实 token。

## token 配置格式

支持单 token 和多 token。

### 兼容旧写法

```text
awNmuGH2_8yM8s_ZwekfK3_Gf_Z83ev_2026
```

### 推荐写法

```text
token|使用人|过期时间|备注
```

示例:

```text
# 一行一个 token
main_token_example|self|never|main
client_demo_token_001|clientA|2026-04-30 23:59:59|demo
temp_test_token_002|test_box|2026-03-31|temporary
```

过期时间支持:

- `2026-12-31`
- `2026-12-31 23:59:59`
- `never`
- `permanent`
- `long-term`

注释行支持:

- `#`
- `;`
- `//`

## 聚合入口

### sv1

```text
https://api2.jumh989.gq/short_videos/sv1.php?token=你的token&url=视频链接
```

### sv2

```text
https://api2.jumh989.gq/short_videos/sv2.php?token=你的token&url=视频链接
```

## 单平台接口

### 抖音

```text
https://api2.jumh989.gq/api/douyin/douyin.php?token=你的token&url=视频链接
```

### 快手

```text
https://api2.jumh989.gq/api/kuaishou/ksjx.php?token=你的token&url=视频链接
```

### B站 新版

```text
https://api2.jumh989.gq/api/bilibili/index.php?token=你的token&url=视频链接
```

### B站 旧版

```text
https://api2.jumh989.gq/api/bilibili/bilibili.php?token=你的token&url=视频链接
```

### 小红书

```text
https://api2.jumh989.gq/api/xiaohongshu/xhsjx.php?token=你的token&url=视频链接
```

### 皮皮虾

```text
https://api2.jumh989.gq/api/ppxia.php?token=你的token&url=视频链接
```

### 皮皮搞笑

```text
https://api2.jumh989.gq/api/pipigx.php?token=你的token&url=视频链接
```

### 微博 官方版

```text
https://api2.jumh989.gq/api/weibo.php?token=你的token&url=视频链接
```

### 微博 爬虫版

```text
https://api2.jumh989.gq/api/weibo_v.php?token=你的token&url=视频链接
```

### 今日头条

```text
https://api2.jumh989.gq/api/toutiao.php?token=你的token&url=视频链接
```

### 汽水音乐

```text
https://api2.jumh989.gq/api/dymusic.php?token=你的token&url=分享链接
```

### 最右

```text
https://api2.jumh989.gq/api/zuiyou.php?token=你的token&url=视频链接
```

### 抖音主页作品

```text
https://api2.jumh989.gq/api/dyzy/dyzy.php?token=你的token&url=分享链接
```

### 抖音 No Cookie

```text
https://api2.jumh989.gq/api/douyin/No%20Cookie/douyin.php?token=你的token&url=视频链接
```

### 抖音直播扩展

```text
https://api2.jumh989.gq/api/douyin/No%20Cookie/dylive.php?token=你的token&url=视频链接
```

## 代理接口

这个接口不是普通解析入口，主要用于代理视频资源地址。

### 抖音代理

```text
https://api2.jumh989.gq/api/svproxyurl.php?token=你的token&proxyurl=Base64后的真实地址&type=douyin
```

### 微博代理

```text
https://api2.jumh989.gq/api/svproxyurl.php?token=你的token&proxyurl=Base64后的真实地址&type=weibo
```

## 常用测试示例

### 抖音 sv1

```bash
curl "https://api2.jumh989.gq/short_videos/sv1.php?token=你的token&url=https%3A%2F%2Fwww.douyin.com%2Fjingxuan%3Fmodal_id%3D7605952867372117937"
```

### 快手 sv1

```bash
curl "https://api2.jumh989.gq/short_videos/sv1.php?token=你的token&url=https%3A%2F%2Fwww.kuaishou.com%2Fshort-video%2F3x6ha2p2m3puvce%3FauthorId%3D3xuc5xaw5b4qqrw%26streamSource%3Dfind%26area%3Dhomexxbrilliant"
```

### 小红书 sv1

```bash
curl "https://api2.jumh989.gq/short_videos/sv1.php?token=你的token&url=https%3A%2F%2Fwww.xiaohongshu.com%2Fexplore%2F69a00e04000000002602f4ba%3Fxsec_token%3DABy_FDK6dEb41Lld2bm1yMuYwL-5yM7xWXzvAP5Vp2OGc%253D%26xsec_source%3Dpc_feed"
```

### B站

```bash
curl "https://api2.jumh989.gq/api/bilibili/index.php?token=你的token&url=https%3A%2F%2Fwww.bilibili.com%2Fvideo%2FBV1FBAZzsErn%2F%3Fspm_id_from%3D333.1007.tianma.6-3-21.click"
```

## 返回规则

- token 正确: 正常返回解析结果
- 缺少 token: 返回 `401` 和 `缺少token`
- token 错误: 返回 `401` 和 `token错误`
- token 过期: 返回 `401` 和 `token已过期`

## 建议

- `url` 参数尽量做 URL 编码
- 不要把真实 token 写进仓库文档
- 如果更换 token，只需要改 `.api_token` 文件内容
