<?php
/**
 * 支付宝沙箱支付 - 商品演示页面
 */
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>支付宝沙箱支付 Demo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Microsoft YaHei", sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: #fff; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); padding: 40px; width: 450px; }
        h1 { text-align: center; color: #1677FF; margin-bottom: 8px; font-size: 22px; }
        .subtitle { text-align: center; color: #999; margin-bottom: 30px; font-size: 13px; }
        .product { background: #fafafa; border-radius: 6px; padding: 20px; margin-bottom: 25px; }
        .product h3 { color: #333; margin-bottom: 10px; }
        .product .price { color: #FF4D4F; font-size: 28px; font-weight: bold; }
        .product .price span { font-size: 16px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; color: #666; margin-bottom: 6px; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #d9d9d9; border-radius: 4px; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .form-group input:focus, .form-group select:focus { border-color: #1677FF; }
        .pay-types { display: flex; gap: 12px; }
        .pay-type-card { flex: 1; border: 2px solid #e8e8e8; border-radius: 6px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.2s; }
        .pay-type-card:hover { border-color: #1677FF; }
        .pay-type-card.selected { border-color: #1677FF; background: #f0f5ff; }
        .pay-type-card .icon { font-size: 32px; margin-bottom: 8px; }
        .pay-type-card .name { font-size: 14px; color: #333; font-weight: bold; }
        .pay-type-card .desc { font-size: 12px; color: #999; margin-top: 4px; }
        .pay-type-card input[type="radio"] { display: none; }
        .btn { width: 100%; padding: 12px; background: #1677FF; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; margin-top: 15px; transition: background 0.2s; }
        .btn:hover { background: #4096FF; }
        .note { margin-top: 20px; padding: 12px; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 4px; font-size: 12px; color: #ad6800; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 支付宝沙箱支付</h1>
        <p class="subtitle">使用沙箱环境进行支付测试</p>

        <div class="product">
            <h3>测试商品 - PHP支付Demo</h3>
            <p class="price"><span>¥</span>0.01</p>
        </div>

        <form action="pay.php" method="POST">
            <div class="form-group">
                <label>订单编号</label>
                <input type="text" name="out_trade_no" value="<?php echo date('YmdHis') . rand(1000, 9999); ?>" readonly>
            </div>
            <div class="form-group">
                <label>商品名称</label>
                <input type="text" name="subject" value="测试商品-01" required>
            </div>
            <div class="form-group">
                <label>支付金额（元）</label>
                <input type="text" name="total_amount" value="0.01" required>
            </div>
            <div class="form-group">
                <label>商品描述</label>
                <input type="text" name="body" value="这是一个测试商品">
            </div>

            <div class="form-group">
                <label>支付方式</label>
                <div class="pay-types">
                    <label class="pay-type-card selected" id="card-page">
                        <input type="radio" name="pay_type" value="page" checked>
                        <div class="icon">🖥️</div>
                        <div class="name">电脑网站支付</div>
                        <div class="desc">PC 网页扫码支付</div>
                    </label>
                    <label class="pay-type-card" id="card-wap">
                        <input type="radio" name="pay_type" value="wap">
                        <div class="icon">📱</div>
                        <div class="name">手机网站支付</div>
                        <div class="desc">手机 H5 唤醒支付</div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn">立即支付</button>
        </form>

        <div class="note">
            <strong>📌 使用说明：</strong><br>
            1. 请先修改 <code>config.php</code> 填入您的沙箱 AppID、密钥<br>
            2. 使用支付宝沙箱账号登录支付<br>
            3. 回调地址为 <strong>wztest.com</strong>
        </div>
    </div>

    <script>
        // 支付方式切换样式
        document.querySelectorAll('.pay-type-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.pay-type-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
    </script>
</body>
</html>