# 宝塔 Git 部署保留 `sv1.php` / `sv2.php`

这套做法专门解决一个破事: 宝塔每次 Git 拉取后，仓库里的 `short_videos/sv1.php` 和 `short_videos/sv2.php` 可能把你线上已经修好的版本重新覆盖掉。

思路很直接:

1. 先把线上当前可用的 `sv1.php` / `sv2.php` 备份到站点外目录。
2. 宝塔每次 Git 拉取完成后，自动执行恢复脚本，把这两个文件重新盖回去。

## 脚本位置

仓库里已经加好了脚本:

`deploy/baota-local-overrides.sh`

支持三个动作:

- `init`: 初始化备份
- `apply`: 把备份重新覆盖回站点
- `status`: 查看当前状态

## 一次性初始化

先在服务器执行一次，把当前线上可用文件备份出来:

```bash
cd /www/wwwroot/api2.jumh989.gq
bash deploy/baota-local-overrides.sh init
```

默认会把备份存到:

```bash
/www/backup/api2-short-videos-local
```

## 宝塔 Git 拉取后执行

把下面这条命令填到宝塔 Git 部署的“拉取后执行脚本”里:

```bash
cd /www/wwwroot/api2.jumh989.gq
bash deploy/baota-local-overrides.sh apply
```

这样每次仓库更新后，`sv1.php` / `sv2.php` 都会被重新恢复成你确认可用的版本。

## 查看状态

如果你想确认备份和线上文件是否都在:

```bash
cd /www/wwwroot/api2.jumh989.gq
bash deploy/baota-local-overrides.sh status
```

## 自定义目录

如果你的站点目录不是 `/www/wwwroot/api2.jumh989.gq`，或者你想把备份放别的位置，可以这样跑:

```bash
cd /www/wwwroot/api2.jumh989.gq
SITE_ROOT=/www/wwwroot/api2.jumh989.gq \
OVERRIDE_DIR=/www/backup/api2-short-videos-local \
bash deploy/baota-local-overrides.sh init
```

宝塔拉取后脚本同理:

```bash
cd /www/wwwroot/api2.jumh989.gq
SITE_ROOT=/www/wwwroot/api2.jumh989.gq \
OVERRIDE_DIR=/www/backup/api2-short-videos-local \
bash deploy/baota-local-overrides.sh apply
```

## 什么时候取消这个覆盖

满足下面两个条件后，就可以去掉这个保底脚本:

1. 仓库里的 `sv1.php` / `sv2.php` 已经是你确认没问题的版本。
2. 你不再需要强制保留服务器本地版本。

去掉方法也很简单:

1. 删掉宝塔 Git 部署里的拉取后执行脚本。
2. 如果需要，重新执行一次 `init`，把新的稳定版本备份进去。

## 建议

- 这个脚本只覆盖 `short_videos/sv1.php` 和 `short_videos/sv2.php`，不碰别的文件。
- 备份目录放在站点外更稳，省得被误删或者被 Web 直接访问。
- 每次你手工修了线上这两个文件后，记得再执行一次 `init`，把新版本备份进去。
