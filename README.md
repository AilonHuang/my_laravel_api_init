# 将Laravel框架进行一些配置处理，让其在开发API时更得心应手

## 来源

配置过程以及配置原理来自大佬博客:

[手摸手教你让Laravel开发Api更得心应手](https://www.guaosi.com/2019/02/26/laravel-api-initialization-preparation/)

[新手使用 Laravel 开发 API 时的前置准备](https://learnku.com/articles/66142)

这里是方便懒人下载，快速搭建，不需要再重新配置一遍。

目前使用的`Laravel`版本是`9`

## 实现功能

- 统一Response响应处理

- Api-Resource资源返回

- 使用Enum枚举

- jwt-auth用户认证与无感知自动刷新

- jwt-auth多角色认证不串号

- 单一设备登陆

- horizon管理异步队列

## 环境

| 程序 | 版本       |
| -------- |----------|
| PHP| `>= 8.1` |
| MySQL| `>= 5.6` |
| Redis| `>= 5.0`  |

## 安装

1.先把项目克隆到本地

```
git clone git@github.com:AilonHuang/my_laravel_api_init.git
```

2.打开项目目录，下载依赖扩展包

```
composer install
```

3.复制配置文件

```
cp .env.example .env
```
自行配置`.env`里的相关配置信息

4.生成`APP_KEY`和`JWT_SECRET`
```
php artisan key:generate
php artisan jwt:secret
```

5.创建迁移
```
php artisan migrate
```
