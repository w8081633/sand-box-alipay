<?php
/**
 * 支付宝沙箱支付 - 发起支付请求
 * page.pay / wap.pay 要求参数通过 URL query string 传递
 */
require_once __DIR__ . '/config.php';

// 获取表单参数
$outTradeNo  = isset($_POST['out_trade_no'])  ? trim($_POST['out_trade_no'])  : '';
$subject     = isset($_POST['subject'])       ? trim($_POST['subject'])       : '';
$totalAmount = isset($_POST['total_amount'])  ? trim($_POST['total_amount'])  : '';
$body        = isset($_POST['body'])          ? trim($_POST['body'])          : '';
$payType     = isset($_POST['pay_type'])      ? trim($_POST['pay_type'])      : 'page';

// 参数校验
if (empty($outTradeNo) || empty($subject) || empty($totalAmount)) {
    die('参数错误：订单编号、商品名称、支付金额不能为空');
}

if (!is_numeric($totalAmount) || $totalAmount <= 0) {
    die('参数错误：支付金额无效');
}

if (!in_array($payType, ['page', 'wap'])) {
    $payType = 'page';
}

// 确定 API 方法
$method = ($payType === 'wap') ? 'alipay.trade.wap.pay' : 'alipay.trade.page.pay';
$productCode = ($payType === 'wap') ? 'QUICK_WAP_WAY' : 'FAST_INSTANT_TRADE_PAY';

// 业务参数
$biz = [
    'out_trade_no' => $outTradeNo,
    'product_code' => $productCode,
    'total_amount' => $totalAmount,
    'subject'      => $subject,
    'body'         => $body ?: $subject,
];

// 公共请求参数
$params = [
    'app_id'      => ALIPAY_APP_ID,
    'method'      => $method,
    'format'      => 'JSON',
    'charset'     => 'utf-8',
    'sign_type'   => 'RSA2',
    'timestamp'   => date('Y-m-d H:i:s'),
    'version'     => '1.0',
    'notify_url'  => 'http://' . ALIPAY_DOMAIN . '/notify.php',
    'return_url'  => 'http://' . ALIPAY_DOMAIN . '/return.php',
    'biz_content' => json_encode($biz, JSON_UNESCAPED_UNICODE),
];

// 生成签名（页面支付类接口签名时保留 sign_type）
$signContent = AlipaySign::getSignContent($params, false);
$params['sign'] = AlipaySign::rsaSign($signContent, ALIPAY_PRIVATE_KEY);

// 构建完整 URL（page.pay / wap.pay 要求 GET 方式，参数在 query string 中）
$alipayUrl = ALIPAY_GATEWAY . '?' . http_build_query($params);

// 保存订单记录
$orderDir = __DIR__ . '/orders';
if (!is_dir($orderDir)) {
    mkdir($orderDir, 0755, true);
}
$orderData = [
    'out_trade_no' => $outTradeNo,
    'subject'      => $subject,
    'total_amount' => $totalAmount,
    'status'       => 'WAIT_PAY',
    'created_at'   => date('Y-m-d H:i:s'),
];
file_put_contents(
    $orderDir . '/' . $outTradeNo . '.json',
    json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

// 输出自动跳转页面
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>正在跳转到支付宝...</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:"Microsoft YaHei",sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f6fa;}
        .container{text-align:center;background:#fff;padding:48px 40px;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.06);max-width:500px;width:90%;}
        .spinner{width:44px;height:44px;border:4px solid #e8ecf1;border-top-color:#1677FF;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 20px;}
        @keyframes spin{to{transform:rotate(360deg)}}
        h2{font-size:18px;color:#1a1a2e;margin-bottom:8px;}
        .info{color:#666;font-size:13px;margin-bottom:4px;}
        .orderno{color:#999;font-size:12px;margin-bottom:24px;word-break:break-all;}
        .btn{display:none;width:100%;padding:12px;background:#1677FF;color:#fff;border:none;border-radius:6px;font-size:15px;cursor:pointer;}
        .btn:hover{background:#0958d9;}
        .btn.show{display:block;}
        .tip{display:none;color:#ff4d4f;font-size:13px;margin-top:12px;}
        .tip.show{display:block;}
        .detail{display:none;margin-top:16px;text-align:left;background:#fafafa;padding:12px;border-radius:6px;font-size:12px;word-break:break-all;color:#555;max-height:180px;overflow-y:auto;}
        .detail.show{display:block;}
        .detail-link{display:none;color:#1677FF;font-size:13px;margin-top:8px;cursor:pointer;}
        .detail-link.show{display:block;}
    </style>
</head>
<body>
<div class="container">
    <div class="spinner" id="spinner"></div>
    <h2>正在跳转到支付宝支付页面</h2>
    <p class="info">请稍候，正在构建安全支付链接...</p>
    <p class="orderno">订单号：<?php echo htmlspecialchars($outTradeNo); ?></p>

    <a class="btn" id="btnManual" href="<?php echo htmlspecialchars($alipayUrl); ?>">手动前往支付宝支付</a>
    <p class="tip" id="tip">页面未自动跳转？请点击上方按钮手动前往。</p>

    <a class="detail-link" id="detailLink" href="javascript:void(0)" onclick="toggleDetail()">查看请求详情</a>
    <div class="detail" id="detailBox">
        <strong>网关 URL：</strong><br><?php echo htmlspecialchars(ALIPAY_GATEWAY); ?><br><br>
        <strong>method：</strong><?php echo htmlspecialchars($method); ?><br><br>
        <strong>签名原串：</strong><br><?php echo htmlspecialchars($signContent); ?><br><br>
        <strong>签名值：</strong><br><?php echo htmlspecialchars($params['sign']); ?>
    </div>
</div>

<script>
(function(){
    var fallbackTimer = setTimeout(function(){
        document.getElementById('spinner').style.display = 'none';
        document.getElementById('btnManual').classList.add('show');
        document.getElementById('tip').classList.add('show');
        document.getElementById('detailLink').classList.add('show');
    }, 2000);

    // 用 window.location 直接跳转完整 URL（避免相对路径问题）
    window.location.href = <?php echo json_encode($alipayUrl); ?>;
})();
function toggleDetail(){
    var b = document.getElementById('detailBox');
    b.classList.toggle('show');
}
</script>
</body>
</html>