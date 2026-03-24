# 上游同步规程

这份文档只干一件事:

防止以后同步上游仓库时，把你自己仓库里已经跑通的生产改动一把冲烂。

当前仓库已经不是“纯上游镜像”，而是你自己的生产仓库。

所以以后不要再用“整仓库无脑同步上游”这种做法。

正确思路是:

```text
上游仓库 = 参考源 / 补丁源
你的仓库 = 生产主仓
宝塔 = 只部署你的仓库
```

## 一句话原则

以后同步上游时，只做:

```text
先看 -> 再挑 -> 再合 -> 再测试 -> 最后再推
```

不要做:

```text
上游一更新 -> 直接整仓库同步
```

## 哪些文件不要无脑跟上游

下面这些文件，默认视为你的“定制区”:

- `short_videos/sv1.php`
- `short_videos/sv2.php`
- `api/common/ApiAuth.php`
- `api/douyin/douyin.php`
- `api/kuaishou/ksjx.php`
- `api/bilibili/index.php`
- `api/bilibili/bilibili.php`
- `api/xiaohongshu/xhsjx.php`
- `api/dymusic.php`
- `api/pipigx.php`
- `api/ppxia.php`
- `api/svproxyurl.php`
- `api/toutiao.php`
- `api/weibo.php`
- `api/weibo_v.php`
- `api/zuiyou.php`
- `api/dyzy/dyzy.php`
- `api/douyin/No Cookie/douyin.php`
- `api/douyin/No Cookie/dylive.php`
- `.gitignore`
- `.api_token.example`
- `deploy/`
- `docs/`

这些文件如果上游也改了，必须人工看，不要闭眼同步。

## 哪些内容可以优先参考上游

下面这些改动通常更值得关注:

- 某个平台底层解析逻辑修复
- 新平台支持
- 某个平台接口字段更新
- 某个平台解析失败问题修复
- 不涉及你自定义鉴权逻辑的代码调整

## 每次同步上游前先做什么

先在你本地有仓库的机器上操作，不要直接在宝塔服务器上合上游。

先确认远端:

```bash
git remote -v
```

正常应该是:

- `origin` = 你的仓库 `https://github.com/shine85/short_videos.git`
- `upstream` = 原作者仓库

然后获取最新上游信息:

```bash
git checkout main
git fetch origin --prune
git fetch upstream --prune
git pull --ff-only origin main
```

## 先看上游改了什么

查看上游比你当前分支多了哪些提交:

```bash
git log --oneline main..upstream/main
```

查看这些提交改了哪些文件:

```bash
git diff --stat main..upstream/main
```

如果你要看某个具体提交内容:

```bash
git show 提交号 --stat
```

## 推荐同步方式

### 方式一: 挑提交合并

适合上游某次提交修了明确问题，你只想拿那一个提交。

```bash
git cherry-pick 提交号
```

### 方式二: 挑文件同步

适合你只想拿上游某个文件的最新版本。

```bash
git checkout upstream/main -- 路径/文件.php
```

然后你再人工检查这个文件是不是会影响你现有逻辑。

## 不推荐的做法

不要直接跑这种:

```bash
git merge upstream/main
```

除非你已经明确知道上游改动范围，并且准备好处理冲突。

也不要直接让宝塔去拉原作者仓库。

## 如果确实要整合一批上游更新

推荐流程:

1. 先看提交列表
2. 看文件影响范围
3. 挑少量必要提交或必要文件
4. 合到本地
5. 测试
6. 推到你的仓库
7. 宝塔再从你的仓库拉

## 每次同步后至少检查什么

最少要检查这些:

- `short_videos/sv1.php`
- `short_videos/sv2.php`
- 抖音解析
- 快手解析
- B站解析
- 小红书解析
- token 鉴权
- 过期 token 拦截

## 线上部署链路

以后固定记住这条链路:

```text
原作者仓库 -> 你本地挑改动 -> 推到你的仓库 -> 宝塔拉你的仓库
```

不是:

```text
原作者仓库 -> 宝塔直接拉
```

## 最后结论

你现在这个仓库已经有自己的生产逻辑了。

所以以后同步上游时:

- 不要追求“和上游完全一致”
- 要追求“只吸收你需要的修复，保住你自己的稳定逻辑”

最稳的做法永远是:

```text
小步挑选同步，小步测试，小步发布
```
