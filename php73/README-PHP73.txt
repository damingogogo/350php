PHP 7.3.4 普通 PHP 运行说明

1. 把 php73 文件夹复制到 phpStudy Pro 的 WWW 目录。
   示例：D:\phpstudy_pro\WWW\php73

2. phpStudy Pro 启动 Apache，PHP 版本选择 7.3.4。

3. 浏览器访问：
   http://localhost/php73/

如果客户要求目录名称必须是 public，可以把 php73 文件夹改名为 public：
   D:\phpstudy_pro\WWW\public

然后访问：
   http://localhost/public/

注意：普通 PHP 7.3.4 版本不需要访问 http://localhost/public/public/。
这个地址是 Laravel 版本的访问方式，普通版入口就在文件夹根目录 index.php。

4. 默认演示账号：
   管理员：admin / 123456
   教师：teacher1 / 123456
   学生：student1 / 123456

5. 本版本是普通 PHP 版本，不依赖 Laravel、Composer、artisan。
   数据保存在 data/db.json，首次运行会读取 data/seed.json。
   下载文件放在 files 目录，上传文件放在 uploads 目录。

6. 如果要重置演示数据，删除 data/db.json 后刷新页面即可。
