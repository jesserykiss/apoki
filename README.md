# apoki

礼金往来记录网站。使用 PHP 8.1+ 原生代码、PDO/MySQL 8、服务端渲染和 Bootstrap 5 CDN 构建。

## 部署到 BYET

1. 在 VistaPanel 中创建 MySQL 数据库和数据库用户，并授予该数据库全部权限。
2. 编辑实际使用的 `config/config.php`，将 BYET 的数据库信息写入其中。`config/config.example.php` 只是配置模板；如果部署目录中还没有 `config.php`，才复制并重命名示例文件。
3. 在 phpMyAdmin 中依次导入 `database/schema.sql` 和 `database/seed.sql`。
4. 通过 FTP 将 `public/` 内的文件上传到 `htdocs/`。
5. 将 `app/`、`config/`、`database/`、`tools/` 上传到 `htdocs/` 的上级 FTP 目录，使 `public/index.php` 可以通过 `../app` 找到业务代码。
6. 将 `public/.htaccess` 随入口一起保留，未知路由会由 `index.php` 处理。

`config/config.php` 已被 `.gitignore` 排除，避免真实数据库密码进入 Git；当前工作区中已经生成该文件，因此直接编辑即可，不要再用 `config.example.php` 覆盖它。

首次联调可将 `tools/healthcheck.php` 临时上传为 `htdocs/healthcheck.php` 后访问检查扩展、数据表和系统字典；验收完成后从主机删除该文件，并确认 `config/config.php` 中的 `debug` 保持为 `false`。

## 本地运行

将 Web 根目录指向 `public/`，通过 Apache 或 Nginx 运行 PHP 8.1+。Nginx 可参考 `nginx.conf.example`。数据库初始化完成后，浏览器打开首页并注册账号。
