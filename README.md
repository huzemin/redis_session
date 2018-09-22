NextCloud (Owncloud) Session App
===

### 安装

1. 手动安装App, 将`redis_session` 放在目录 `apps/`
2. 使用管理员权限登陆Nextcloud，启用`redis_session`


### 配置

修改配置文件`config/config.php`

```php

'session.redis.enabled' => true,
'session.redis' => 
  array (
    'host' => 'localhost',
    'port' => 6379,
    'timeout' => 0.0,
    'password' => '',
    'dbindex' => 11,
 ),

```


### 关闭代码检查

> 代码没有进行代码签名，因此会有代码检查错误提醒

```php

'integrity.check.disabled' => true

```