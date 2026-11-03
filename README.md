# PhalApi Kernal 2.x 核心框架

PhalApi Kernal 2.x 框架核心部分，基于 composer 发布。完整项目请见 [PhalApi 开源接口框架](https://github.com/phalapi/phalapi)。

## 版本与运行环境

| 项目 | 说明 |
|---|---|
| 当前版本 | **v8.0.0** |
| PHP 要求 | **>= 8.0**（已实测 8.0 与 8.4） |
| PHP 7.x | 不支持 |
| 依赖 | `phalapi/notorm: 8.*.*` |

> **版本号说明**：`v8.0.0` 中的 **8 表示“支持 PHP 8”**，不是 PhalAPI 的第 8 代版本。

## 安装

作为框架内核，通常不需要单独安装，随项目一并引入即可：

```bash
composer require phalapi/kernal:^8.0
```

## v8.0.0 主要变更

+ `PhalApi\Api` 基类加 `#[\AllowDynamicProperties]`，消除接口参数的动态属性弃用（PHP 8.2+）；
+ `DependenceInjection` 的 4 个 `offset*` 方法补 `#[\ReturnTypeWillChange]`，消除 ArrayAccess 返回类型弃用（PHP 8.1+）；
+ `Error\ApiError` 移除 `case E_STRICT:` 分支（该常量在 PHP 8.4 已弃用）；
+ `Model\Proxy` 的隐式可空参数改为显式 `?Type`（PHP 8.4 弃用）；
+ **`Crypt\McryptCrypt` 由 mcrypt 改为 OpenSSL AES-256-CBC**。mcrypt 自 PHP 7.2 起已从 PHP 移除，原实现在 PHP 8 下必然致命；类名与 `encrypt()` / `decrypt()` 接口保持不变；
+ `Crypt\MultiMcryptCrypt` 对畸形密文与错误密钥按原契约返回 `false`。

### 不兼容说明

`McryptCrypt` 的历史密文无法用新版本解密。mcrypt 自 PHP 7.2 起已不存在，这些数据在 7.2+ 环境本就不可用；如需迁移，须先在 PHP 7.1 及以下解密，再用新版本重新加密。

## 相关仓库

+ [phalapi/phalapi](https://github.com/phalapi/phalapi) 完整项目
+ [phalapi/notorm](https://github.com/phalapi/notorm) 数据库底层包
+ [升级到 v8.0.0（PHP 8 支持）](http://docs.phalapi.net/#/v2.0/php8-upgrade) 完整升级指南
