<?php
/**
 * 支付宝支付请求构建类
 * 支持 PC 电脑网站支付 和 WAP 手机网站支付
 */
class AlipaySubmit
{
    /** @var string 支付宝网关 */
    private $gatewayUrl;

    /** @var string 应用 ID */
    private $appId;

    /** @var string 商户私钥 */
    private $privateKey;

    /** @var string 支付宝公钥 */
    private $alipayPublicKey;

    /** @var string 回调域名 */
    private $domain;

    public function __construct($gatewayUrl, $appId, $privateKey, $alipayPublicKey, $domain)
    {
        $this->gatewayUrl     = $gatewayUrl;
        $this->appId          = $appId;
        $this->privateKey     = $privateKey;
        $this->alipayPublicKey = $alipayPublicKey;
        $this->domain         = $domain;
    }

    /**
     * 发起支付
     * @param string $type    支付方式: page(电脑网站) / wap(手机网站)
     * @param array  $biz     业务参数: out_trade_no, total_amount, subject, body(可选)
     */
    public function pay($type, $biz)
    {
        // 公共请求参数
        $params = [
            'app_id'      => $this->appId,
            'method'      => ($type === 'wap') ? 'alipay.trade.wap.pay' : 'alipay.trade.page.pay',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'notify_url'  => 'http://' . $this->domain . '/notify.php',
            'return_url'  => 'http://' . $this->domain . '/return.php',
            'biz_content' => json_encode($biz, JSON_UNESCAPED_UNICODE),
        ];

        // 生成签名（页面支付类接口签名时保留 sign_type）
        $params['sign'] = AlipaySign::rsaSign(
            AlipaySign::getSignContent($params, false),
            $this->privateKey
        );

        return $this->gatewayUrl . '?' . http_build_query($params);
    }

    /**
     * 验签 — 用于同步回调和异步通知
     * @param array $params  支付宝返回的 GET/POST 参数
     * @return bool
     */
    public function verify($params)
    {
        if (empty($params['sign']) || empty($params['sign_type'])) {
            return false;
        }

        $sign     = $params['sign'];
        $signType = $params['sign_type'];

        // 剔除 sign 和 sign_type 后验签（支付宝回调签名规则：剔除 sign 和 sign_type）
        unset($params['sign'], $params['sign_type']);
        $content = AlipaySign::getSignContent($params);

        return AlipaySign::rsaVerify($content, $sign, $this->alipayPublicKey);
    }
}