<?php
/**
 * 支付宝 RSA2 签名 & 验签工具类
 */
class AlipaySign
{
    /**
     * RSA2 签名
     * @param string $data          待签名字符串
     * @param string $privateKey    商户私钥（一行，无头尾标记和换行）
     * @return string               Base64 编码的签名
     * @throws Exception
     */
    public static function rsaSign($data, $privateKey)
    {
        $privateKey = self::formatPrivateKey($privateKey);
        $res = openssl_get_privatekey($privateKey);
        if (!$res) {
            throw new Exception('私钥格式错误');
        }
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($res);
        }
        return base64_encode($sign);
    }

    /**
     * RSA2 验签
     * @param string $data          待签名字符串（不含 sign 参数）
     * @param string $sign          Base64 编码的签名
     * @param string $publicKey     支付宝公钥（一行，无头尾标记和换行）
     * @return bool
     */
    public static function rsaVerify($data, $sign, $publicKey)
    {
        $publicKey = self::formatPublicKey($publicKey);
        $res = openssl_get_publickey($publicKey);
        if (!$res) {
            return false;
        }
        $result = (bool) openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($res);
        }
        return $result;
    }

    /**
     * 将请求参数按照 key 字母升序排列，拼接成 key=value&key=value 的待签名字符串
     * @param array $params
     * @return string
     */
    public static function getSignContent($params, $filterSignType = true)
    {
        ksort($params);
        $parts = [];
        foreach ($params as $key => $value) {
            // sign 始终过滤空值始终过滤
            if ($key === 'sign' || $value === '' || $value === null) {
                continue;
            }
            // 页面支付类接口（page.pay / wap.pay）签名时保留 sign_type
            if ($key === 'sign_type' && $filterSignType === false) {
                $parts[] = "{$key}={$value}";
                continue;
            }
            // 普通查询类接口签名时过滤 sign_type
            if ($key === 'sign_type') {
                continue;
            }
            $parts[] = "{$key}={$value}";
        }
        return implode('&', $parts);
    }

    /**
     * 格式化私钥（自动添加 PEM 头尾标记）
     */
    private static function formatPrivateKey($key)
    {
        if (strpos($key, '-----BEGIN') === false) {
            $key = chunk_split($key, 64, "\n");
            return "-----BEGIN RSA PRIVATE KEY-----\n{$key}-----END RSA PRIVATE KEY-----";
        }
        return $key;
    }

    /**
     * 格式化公钥（自动添加 PEM 头尾标记）
     */
    private static function formatPublicKey($key)
    {
        if (strpos($key, '-----BEGIN') === false) {
            $key = chunk_split($key, 64, "\n");
            return "-----BEGIN PUBLIC KEY-----\n{$key}-----END PUBLIC KEY-----";
        }
        return $key;
    }
}