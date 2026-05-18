# 支付宝沙箱支付 Demo（PHP 无框架版）

一个基于原生 PHP 的支付宝沙箱支付接入示例，支持 **电脑网站支付 (page.pay)** 和 **手机网站支付 (wap.pay)** 两种方式，含完整的签名、验签、回调处理逻辑。

---

## 环境要求

- PHP 7.4+（推荐 8.0+）
- PHP OpenSSL 扩展（`openssl`）
- Web 服务器（Apache / Nginx / 或 PHP 内置服务器）
- （可选）`ab`（Apache Bench）用于压力测试

---

## 快速开始

### 1. 克隆项目

```bash
git clone <your-repo-url>
cd 202605181608
```

### 2. 修改配置

编辑 `config.php`，填入您的沙箱应用信息：

| 配置项 | 说明 | 获取位置 |
|-|-|-|
| `ALIPAY_APP_ID` | 沙箱应用 ID | [支付宝沙箱控制台](https://openhome.alipay.com/develop/sandbox/app) |
| `ALIPAY_PRIVATE_KEY` | 商户私钥（RSA2） | 工具生成后复制 |
| `ALIPAY_PUBLIC_KEY` | **支付宝公钥**（非应用公钥） | 沙箱控制台 → 查看密钥 → **支付宝公钥** |
| `ALIPAY_DOMAIN` | 回调域名 | 默认 `wztest.com`，可根据需要修改 |

```php
// config.php 示例
define('ALIPAY_APP_ID',       '9021000123629');
define('ALIPAY_PRIVATE_KEY',  'MIIEpAIBAAKCAQEA...');
define('ALIPAY_PUBLIC_KEY',   'MIIBIjANBgkqhkiG9w0BAQEF...');
define('ALIPAY_DOMAIN',       'wztest.com');
```

> ⚠️ **重要**：`ALIPAY_PUBLIC_KEY` 必须填支付宝沙箱后台显示的 **"支付宝公钥"**，而不是您上传的应用公钥。两者不同，填错会导致回调验签失败。

### 3. 配置本地 hosts（回调域名）

以管理员身份编辑 `C:\Windows\System32\drivers\etc\hosts`，添加：

```
127.0.0.1 wztest.com
```

### 4. 启动服务

**方式一：PHP 内置服务器（推荐快速测试）**

```bash
php -S 127.0.0.1:80 -t e:\acode\202605181608
```

**方式二：Apache / Nginx**

将项目根目录指向 Web 服务器虚拟主机，域名设为 `wztest.com`。

### 5. 发起支付

浏览器访问 `http://wztest.com`，点击 **"立即支付"**，使用支付宝沙箱买家账号登录完成支付。

---

## 沙箱账号

在 [沙箱控制台](https://openhome.alipay.com/develop/sandbox/app) 中可查看：

- **买家账号**：用于登录沙箱支付宝完成支付
- **卖家账号**：收款方（通常是沙箱应用的创建者）

---

## 项目结构

```
├── config.php              # 支付宝配置（AppID、密钥、网关）
├── index.php               # 商品展示页 / 支付入口
├── pay.php                 # 发起支付请求（生成签名并跳转支付宝）
├── return.php              # 同步回调（支付成功后浏览器跳转回来）
├── notify.php              # 异步通知（支付宝服务端 POST 通知）
├── test_cli.php            # CLI 签名/验签/网关连通性测试
├── test_verify.php         # 验签逻辑模拟测试
├── README.md               # 本文件
├── lib/
│   ├── AlipaySign.php      # 签名/验签核心工具类
│   └── AlipaySubmit.php    # 支付构建类（含验签方法）
└── orders/
    └── *.json              # 订单记录文件（模拟数据库）
```

---

## 核心流程

```
用户 → index.php → pay.php → 支付宝支付页 → 支付完成
                                                │
                    ┌───────────────────────────┤
                    ▼                           ▼
              return.php                  notify.php
           （同步，浏览器跳转）         （异步，支付宝服务端 POST）
                    │                           │
                    ▼                           ▼
              展示支付结果                  更新订单状态
          同步更新订单状态               输出 "success" 
                                              │
                                              ▼
                                        支付宝停止重试
```

### 回调说明

| 回调类型 | 文件 | 触发方式 | 本地生效 |
|-|-|-|-|
| 同步回调 | `return.php` | 用户浏览器跳转 | ✅ 是 |
| 异步通知 | `notify.php` | 支付宝服务端 POST | ❌ 否（外网不可达本地） |

由于本地开发环境无法接收支付宝异步通知，`return.php` 中已加入**同步更新订单状态**的逻辑，验签通过后直接修改订单文件。

---

## 技术要点

### 签名规则

- **生成签名**（我方→支付宝）：保留 `sign_type` 参与签名
- **验签**（我方验证支付宝）：剔除 `sign` 和 `sign_type` 后验签

签名原串按字段名 **ASCII 升序** 排列，以 `key=value&` 格式拼接。

### 沙箱网关

```
https://openapi-sandbox.dl.alipaydev.com/gateway.do
```

### alipay.trade.page.pay 参数传递

电脑网站支付（`page.pay`）和手机网站支付（`wap.pay`）要求参数通过 **URL query string** 传递，而非 POST body。

---

## 测试

### 基础测试

```bash
php test_cli.php
```

验证签名生成、签名验签自检、网关地址连通性。

### 验签模拟测试

```bash
php test_verify.php
```

模拟回调验签全流程，验证 `verify()` 方法的正确性。

---

## 高并发测试

### 测试工具

推荐使用 Apache Bench (`ab`)：

```bash
# 测试 index.php 并发能力
ab -n 500 -c 20 http://wztest.com/index.php

# 测试 pay.php 签名处理
ab -n 100 -c 10 -p pay_data.txt -T "application/x-www-form-urlencoded" http://wztest.com/pay.php
```

### 可能的问题及对应优化

| 问题 | 原因 | 优化方案 |
|-|-|-|
| 订单文件损坏 | 并发写入 `.json` 无锁 | 使用 `flock()` 文件锁 |
| 订单重复/丢失 | 非原子读取+写入 | 改用 SQLite/MySQL 数据库 |
| 签名性能瓶颈 | `openssl_sign` 每次重新加载密钥 | 缓存密钥资源句柄 |
| 重复通知处理 | 同一订单多次回调 | 添加幂等性检查 |
| PHP 内置服务器单线程 | `php -S` 一次处理一个请求 | 换用 Nginx/Apache 多线程模式 |

---

## 常见问题

### Q: 跳转到支付宝后显示 "验签失败" 或页面空白？

1. 检查 `ALIPAY_PRIVATE_KEY` 是否正确（与应用公钥配对）
2. 检查签名规则：`page.pay` / `wap.pay` 签名时请求参数通过 **URL query string** 传递
3. 沙箱网关地址是否正确（域名中含 `sandbox`）

### Q: 支付完成后 return.php 显示 "验签失败"？

**几乎都是 ALIPAY_PUBLIC_KEY 配置错误。**

登录 [沙箱控制台](https://openhome.alipay.com/develop/sandbox/app) → 查看密钥 → 复制 **"支付宝公钥"**（不是应用公钥），填入 `config.php` 的 `ALIPAY_PUBLIC_KEY`。

### Q: 订单状态一直是 WAIT_PAY？

本地开发环境无法收到支付宝异步通知（支付宝服务器无法访问 `wztest.com`）。`return.php` 中的同步更新逻辑已处理此问题，请确保 `return.php` 文件中包含更新订单状态的代码（验证通过后自动写入）。

### Q: 并发写入订单文件导致 JSON 损坏？

当前版本使用文件系统存储，高并发下建议：
1. 升级为 SQLite/MySQL 数据库
2. 或使用 `flock()` 文件锁
3. 或改用 Redis 队列处理订单

---

## License

MIT