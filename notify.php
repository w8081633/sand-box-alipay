<?php
/**
 * 支付宝沙箱支付 - 异步通知页面
 * 支付宝服务端以 POST 方式通知商户支付结果
 * 验签通过后必须输出 "success"，否则支付宝会持续重试
 */
require_once __DIR__ . '/config.php';

// ============ 日志记录函数 ============
function notifyLog($msg)
{
    $logFile = __DIR__ . '/notify.log';
    $time    = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$time}] {$msg}\n", FILE_APPEND);
}

// ============ 获取支付宝 POST 参数 ============
$params = $_POST;

if (empty($params)) {
    notifyLog('收到空POST请求');
    echo 'fail';
    exit;
}

notifyLog('收到异步通知: ' . json_encode($params, JSON_UNESCAPED_UNICODE));

// ============ 验签 ============
$alipaySubmit = new AlipaySubmit(
    ALIPAY_GATEWAY,
    ALIPAY_APP_ID,
    ALIPAY_PRIVATE_KEY,
    ALIPAY_PUBLIC_KEY,
    ALIPAY_DOMAIN
);

$isVerified = $alipaySubmit->verify($params);

if (!$isVerified) {
    notifyLog('验签失败');
    echo 'fail';
    exit;
}

notifyLog('验签通过');

// ============ 获取通知关键信息 ============
$outTradeNo  = isset($params['out_trade_no'])  ? $params['out_trade_no']  : '';
$tradeNo     = isset($params['trade_no'])      ? $params['trade_no']      : '';
$totalAmount = isset($params['total_amount'])  ? $params['total_amount']  : '';
$tradeStatus = isset($params['trade_status'])  ? $params['trade_status']  : '';

// ============ 处理交易状态 ============
if ($tradeStatus === 'TRADE_SUCCESS' || $tradeStatus === 'TRADE_FINISHED') {
    // ================================================================
    // 在这里编写您的业务逻辑：
    //   1. 根据 out_trade_no 查询您的订单
    //   2. 判断 total_amount 是否与订单金额一致
    //   3. 判断 app_id 是否与您的应用ID一致
    //   4. 以上验证全部通过后，更新订单状态为"已支付"
    // ================================================================

    notifyLog("支付成功 - 商户订单号:{$outTradeNo}, 支付宝交易号:{$tradeNo}, 金额:{$totalAmount}, 状态:{$tradeStatus}");

    // 更新订单状态（与 index.php → pay.php 生成的订单结构保持一致）
    $orderFile = __DIR__ . '/orders/' . $outTradeNo . '.json';
    if (file_exists($orderFile)) {
        $orderData = json_decode(file_get_contents($orderFile), true);
    } else {
        $orderData = ['out_trade_no' => $outTradeNo, 'subject' => '', 'total_amount' => $totalAmount];
    }
    $orderData['status']        = $tradeStatus;
    $orderData['trade_no']      = $tradeNo;
    $orderData['notify_time']   = date('Y-m-d H:i:s');
    $orderData['buyer_id']      = isset($params['buyer_id'])       ? $params['buyer_id']       : '';
    $orderData['buyer_logon_id']= isset($params['buyer_logon_id']) ? $params['buyer_logon_id'] : '';
    $orderData['gmt_create']    = isset($params['gmt_create'])     ? $params['gmt_create']     : '';
    $orderData['gmt_payment']   = isset($params['gmt_payment'])    ? $params['gmt_payment']    : '';
    file_put_contents($orderFile, json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // 验签通过且业务处理成功，必须返回 success
    echo 'success';
} else {
    notifyLog("未处理的交易状态: {$tradeStatus}, 商户订单号:{$outTradeNo}");
    echo 'success'; // 即使不是支付成功也返回success，避免支付宝重复通知
}

exit;