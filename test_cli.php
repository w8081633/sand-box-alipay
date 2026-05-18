<?php
/**
 * 支付宝沙箱支付 - CLI 测试脚本
 * 用法: php test_cli.php
 * 
 * 功能：
 * 1. 测试 RSA2 签名是否正常生成
 * 2. 用 cURL POST 到支付宝网关，检查响应
 * 3. 测试验签功能
 */
require_once __DIR__ . '/config.php';

echo "========================================\n";
echo "  支付宝沙箱支付 - CLI 测试\n";
echo "========================================\n\n";

// ===== 1. 检查配置 =====
echo "[1/5] 检查配置...\n";
echo "  AppID: " . ALIPAY_APP_ID . "\n";
echo "  网关: " . ALIPAY_GATEWAY . "\n";
echo "  域名: " . ALIPAY_DOMAIN . "\n";
echo "  私钥长度: " . strlen(ALIPAY_PRIVATE_KEY) . " 字符\n";
echo "  公钥长度: " . strlen(ALIPAY_PUBLIC_KEY) . " 字符\n\n";

// ===== 2. 测试签名生成 =====
echo "[2/5] 测试 RSA2 签名...\n";

$testData = 'a=1&b=2&c=3';
try {
    $sign = AlipaySign::rsaSign($testData, ALIPAY_PRIVATE_KEY);
    echo "  ✅ 签名生成成功\n";
    echo "  签名字符串: {$testData}\n";
    echo "  签名结果: " . substr($sign, 0, 40) . "...\n\n";
} catch (Exception $e) {
    echo "  ❌ 签名失败: " . $e->getMessage() . "\n\n";
    echo "可能原因：私钥格式不正确，请检查 config.php 中的密钥是否完整。\n\n";
    exit(1);
}

// ===== 3. 测试验签（签名后立即验证） =====
echo "[3/5] 测试 RSA2 验签...\n";

if (AlipaySign::rsaVerify($testData, $sign, ALIPAY_PUBLIC_KEY)) {
    echo "  ✅ 验签成功（签名 ↔ 验签匹配正确）\n\n";
} else {
    echo "  ❌ 验签失败：签名与公钥不匹配！\n";
    echo "  请确认 ALIPAY_PUBLIC_KEY 是支付宝公钥（非商户公钥）。\n\n";
    exit(1);
}

// ===== 4. 构建支付参数并测试请求 =====
echo "[4/5] 构建支付参数并发送测试请求到支付宝网关...\n";

$outTradeNo = 'TEST' . date('YmdHis') . rand(1000, 9999);
$biz = [
    'out_trade_no' => $outTradeNo,
    'product_code' => 'FAST_INSTANT_TRADE_PAY',
    'total_amount' => '0.01',
    'subject'      => 'CLI测试商品',
    'body'         => 'CLI测试商品描述',
];

$params = [
    'app_id'      => ALIPAY_APP_ID,
    'method'      => 'alipay.trade.page.pay',
    'format'      => 'JSON',
    'charset'     => 'utf-8',
    'sign_type'   => 'RSA2',
    'timestamp'   => date('Y-m-d H:i:s'),
    'version'     => '1.0',
    'notify_url'  => 'http://' . ALIPAY_DOMAIN . '/notify.php',
    'return_url'  => 'http://' . ALIPAY_DOMAIN . '/return.php',
    'biz_content' => json_encode($biz, JSON_UNESCAPED_UNICODE),
];

$signContent = AlipaySign::getSignContent($params);
$params['sign'] = AlipaySign::rsaSign($signContent, ALIPAY_PRIVATE_KEY);

echo "  请求参数:\n";
foreach ($params as $k => $v) {
    $display = strlen($v) > 60 ? substr($v, 0, 60) . '...' : $v;
    echo "    {$k} = {$display}\n";
}
echo "\n";

// cURL POST 请求
echo "  发送 POST 请求到: " . ALIPAY_GATEWAY . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ALIPAY_GATEWAY);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "  HTTP 状态码: {$httpCode}\n";

if ($curlError) {
    echo "  ❌ cURL 错误: {$curlError}\n";
} elseif ($httpCode == 302 || $httpCode == 301) {
    // 页面支付会返回 302 重定向到登录页，这是预期行为
    echo "  ✅ 支付宝返回重定向（预期行为）— 支付请求构造正确\n";
    echo "  说明：页面支付会重定向到支付宝登录/扫码页面。\n";
    echo "  在浏览器中访问 index.php 即可看到完整支付流程。\n";
} elseif ($httpCode == 200) {
    // 分离 header 和 body
    $parts = explode("\r\n\r\n", $response, 2);
    $header = isset($parts[0]) ? $parts[0] : '';
    $body   = isset($parts[1]) ? $parts[1] : $response;

    echo "  响应头（前200字符）:\n    " . substr($header, 0, 200) . "\n";
    echo "  响应体:\n    " . substr($body, 0, 500) . "\n";

    // 尝试解析 JSON 响应
    $json = json_decode($body, true);
    if ($json) {
        echo "\n  JSON 解析:\n";
        echo "    sign 字段存在: " . (isset($json['sign']) ? '✅' : '❌') . "\n";
        if (isset($json['alipay_trade_page_pay_response'])) {
            $resp = $json['alipay_trade_page_pay_response'];
            echo "    code: " . ($resp['code'] ?? 'N/A') . "\n";
            echo "    msg: " . ($resp['msg'] ?? 'N/A') . "\n";
            echo "    sub_code: " . ($resp['sub_code'] ?? 'N/A') . "\n";
            echo "    sub_msg: " . ($resp['sub_msg'] ?? 'N/A') . "\n";
        }
    }
} else {
    echo "  ⚠️ 未预期的 HTTP 状态码\n";
    echo "  响应（前500字符）:\n    " . substr($response, 0, 500) . "\n";
}

// ===== 5. 生成调试用的 URL（用于手动测试） =====
echo "\n[5/5] 调试信息\n";
echo "  签名前的待签名字符串:\n    {$signContent}\n\n";
echo "  如果浏览器端支付仍然失败，请检查:\n";
echo "  1. AppID 是否正确（当前: " . ALIPAY_APP_ID . "）\n";
echo "  2. 密钥是否是沙箱环境生成的一对\n";
echo "  3. 商户私钥 = 应用公钥对应的私钥\n";
echo "  4. 支付宝公钥 = 沙箱环境显示的公钥\n";
echo "  5. wztest.com 是否已在沙箱后台配置回调域名\n\n";

echo "========================================\n";
echo "  测试完成\n";
echo "========================================\n";