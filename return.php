<?php
/**
 * 支付宝沙箱支付 - 同步回调页面
 * 支付完成后支付宝跳转回来时展示支付结果
 */
require_once __DIR__ . '/config.php';

// 获取支付宝返回的 GET 参数
$params = $_GET;

// 初始化支付提交类
$alipaySubmit = new AlipaySubmit(
    ALIPAY_GATEWAY,
    ALIPAY_APP_ID,
    ALIPAY_PRIVATE_KEY,
    ALIPAY_PUBLIC_KEY,
    ALIPAY_DOMAIN
);

// 验签
$isVerified = $alipaySubmit->verify($params);
$outTradeNo = isset($params['out_trade_no']) ? htmlspecialchars($params['out_trade_no']) : '';
$tradeNo    = isset($params['trade_no'])     ? htmlspecialchars($params['trade_no'])     : '';
$totalAmount = isset($params['total_amount']) ? htmlspecialchars($params['total_amount']) : '';

// ============ 同步更新订单状态 ============
// 本地开发环境无法收到支付宝异步通知（notify.php），因此必须在同步回调中更新订单
// 验签通过后，支付宝返回的 trade_no、total_amount 是真实可信的
if ($isVerified && $outTradeNo) {
    $orderFile = __DIR__ . '/orders/' . $outTradeNo . '.json';
    if (file_exists($orderFile)) {
        $orderData = json_decode(file_get_contents($orderFile), true);
        $orderData['status']       = 'TRADE_SUCCESS';
        $orderData['trade_no']     = $params['trade_no'];
        $orderData['gmt_payment']  = date('Y-m-d H:i:s');
        file_put_contents(
            $orderFile,
            json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付结果</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Microsoft YaHei", sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: #fff; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); padding: 40px; width: 450px; text-align: center; }
        .icon { font-size: 64px; margin-bottom: 20px; }
        .success .icon { color: #52c41a; }
        .fail .icon { color: #ff4d4f; }
        h2 { margin-bottom: 10px; }
        .detail { background: #fafafa; border-radius: 6px; padding: 20px; margin: 20px 0; text-align: left; }
        .detail .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .detail .row:last-child { border-bottom: none; }
        .detail .label { color: #999; }
        .detail .value { color: #333; font-weight: 500; }
        .btn { display: inline-block; padding: 10px 30px; background: #1677FF; color: #fff; border-radius: 4px; text-decoration: none; margin-top: 15px; }
        .btn:hover { background: #4096FF; }
        .warning { margin-top: 20px; padding: 12px; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 4px; font-size: 12px; color: #ad6800; }
    </style>
</head>
<body>
    <div class="container <?php echo $isVerified ? 'success' : 'fail'; ?>">
        <?php if ($isVerified): ?>
            <div class="icon">✅</div>
            <h2>支付成功</h2>
            <p style="color:#999;">验签通过，支付结果可信</p>
        <?php else: ?>
            <div class="icon">❌</div>
            <h2>验签失败</h2>
            <p style="color:#999;">签名验证未通过，请勿将此结果作为支付成功依据</p>
        <?php endif; ?>

        <div class="detail">
            <div class="row">
                <span class="label">商户订单号</span>
                <span class="value"><?php echo $outTradeNo ?: '-'; ?></span>
            </div>
            <div class="row">
                <span class="label">支付宝交易号</span>
                <span class="value"><?php echo $tradeNo ?: '-'; ?></span>
            </div>
            <div class="row">
                <span class="label">支付金额</span>
                <span class="value">¥ <?php echo $totalAmount ?: '-'; ?></span>
            </div>
        </div>

        <a href="index.php" class="btn">返回首页</a>

        <div class="warning">
            <strong>⚠️ 重要提示：</strong><br>
            同步回调仅为页面跳转，<strong>不能</strong>作为支付成功的最终依据。<br>
            请以异步通知 <code>notify.php</code> 验签结果为准来更新订单状态。
        </div>
    </div>
</body>
</html>