<?php
/**
 * 支付宝沙箱支付配置文件
 *
 * 请前往 https://openhome.alipay.com/develop/sandbox/app
 * 获取您的沙箱应用 AppID、商户私钥、支付宝公钥
 */

// ============ 支付宝沙箱配置 ============

// 应用 ID（沙箱环境）
define('ALIPAY_APP_ID', '9021000123629');

// 商户私钥（一行字符串，不含 -----BEGIN RSA PRIVATE KEY----- 头尾标记和换行）
define('ALIPAY_PRIVATE_KEY', 'MIIEpAIBAAKCAQEAjGczkeqlLI3AImcuocYHmj+neHH3knc049IsgOsSkY855H9EWQD2vlI0DVNnBt/9Y6/8NyOnJtq6BmDlH+vDH/bPPBP779IUqVjz/DvYJfbIleKt7Vj1G8CqeyYukprZMNGv7OLipatyQ8n1YlFERHelv8+o1ogpNH0mjqkY2CXPWLWCUJDr99Y83MlcbkkOA7jLoIIJAvjeFYrID4lgHQyIF46GvzBKCHDU3mgip0h2pjlPTRIRcLsvZ/QolLI6JVXHlulieTbg8dmJWTFVX1ZCWrJOo9mX9aBbnJagBB2KXILvn/Poh+Ri4sEYGtxa4Bfqab1sQwMazu/gsqNXBQIDAQABAoIBAA52hxJt6GvpgjRJCr2xZ5EVI4w9uTIGQ5WATgNNs9D4vw7xqTm95qz+TMxengtQQYrmUwXfD1XqTCViD9g08hm10+0tZdNzgZtcRW3jQEXQ4SLHggEkG8OrGiSrbzq85sinoQa27IACZDflcviYxH6FELbsjkjjJ9N3XORvONbzDfFtyZOhEO1oHzpndp/X7sNjcMwJ86ufIu/cKwJF6NsrO88Xm50GiOLOfC+apyz0eVwcDSfle1JyGYHicqAF4oyAAOqyjy9ySW8IZZ0sMhAlpF0UNnY8KhunOHmG60MrDgWmWio+THU0t9pm2HPJLatxWiBU9CPKuYtK/6bGaUECgYEA8TLs2Vg+ZkuYpQB5IDc/URLNEYiuSPCnUKzTkr+V4eTaQ/XUE82dtIiUF3UU59sYMqTSa4fuvv6cniu/fGm794rcubTHfBIUecAQ1KuIjjNTo4oyRohhrQtV62gPnjVOfl6OGYqgqVto7CO//YuameOI5fse85KVpQsir/4zcPECgYEAlQTXX3bzqZC/Gtr1wZtkWDZrxtqiH6WbvcHwK2X8ZkA7Z+DUs3XB1gbh5bei/AQ1ykJYA9CQ097b3gY/Me7NDpmeH95vpB4yG03GjsY9JrfVZmZNKPecKAy1v3fb4XVhcy8zX7YgrCM5Gd/uOZemt+ZpkYH0khFH+2U1K3r8R1UCgYEA4EbVG2hFZYNHpa3h80XL90v/KR2pyaMUQRzjAqJo8QqGtgjAscVQrk0NPx6cWNdOEdFW46wbILfJ0/2j6UC8CnqxsXBayZBaP7eLLuVtbaRmUjwvcYxhHrHaq7EwTJEOssyjXzabG78mueSoIk+Maym64vZ1mlEkGrpW/8Tj+lECgYBxRknUxl33whCSgGiZL9658zw/30enmMJnHvnKc27F5wOBNfVZKSUb5QVoEgwxV53vzjiLRcohU2F8RvFYqnZzJ7B79yCT92QNPzS0qNopCUqM2SzD/FxWUTsCfUDGA+z8mp+JnK7/SpMIKSEz5CQV3G7Y7ZkUQ1CdN1SQZZ9JNQKBgQCwbijGJalGeg3wk9eJtNXB9X4K18HvdAyFjQxWrm8uUimufJy5RtOHWBfCvIB82QhNkiD3+bdr5SXAJ+uPDy60k+PprC/CDSFeeS8toQ/x7XVQdUuoiEg+VdjQRUlyMlP/9zCpm5cScXLX/7E7JhThpy8fOi8wXBMd+2m302Tq6w==');

// 支付宝公钥（一行字符串，不含 -----BEGIN PUBLIC KEY----- 头尾标记和换行）
// ⚠️ 注意：这是支付宝沙箱后台提供的"支付宝公钥"，不是您上传的应用公钥！
// 请登录 https://openhome.alipay.com/develop/sandbox/app → 查看密钥 → 复制"支付宝公钥"
define('ALIPAY_PUBLIC_KEY', 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtVqWvfonwbQShL/icLb/Xiumy+vFh6fNhH8BQja1AYIJTb7tpoD+x6bQHJZ4B1OAWzD++u7/kh5WxnmPPOhsXVM0SznhVoqCoz9f2PbuDEHdYkbfMFh8BGEMNTB2OmYxVk6weUMapcMpTx/VEJqV6R5yiNa6NUuiaPyWMZ8ILl/jI8VDUJPpErg7mafORj7F+kV/RKU/WjpOp1NyjPjTmp1Jv4xikP7peGGO/xqvkAXeIFQ1oJ8iisyxaKsojgRqGAGgW6cPi+diGpTmUe6qFIw8YFZy6AkLAB3oTEcnzm23bg1V4E1NakfBxGBqi+Ibcj8sf3kw2IzyytqwAu/fVwIDAQAB');

// 支付宝网关（沙箱环境）
define('ALIPAY_GATEWAY', 'https://openapi-sandbox.dl.alipaydev.com/gateway.do');

// 签名类型
define('ALIPAY_SIGN_TYPE', 'RSA2');

// 回调域名
define('ALIPAY_DOMAIN', 'wztest.com');

// 字符编码
define('ALIPAY_CHARSET', 'utf-8');

// ============ 自动加载工具类 ============
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/lib/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});