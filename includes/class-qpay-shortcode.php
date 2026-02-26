<?php

class QPay_Shortcode
{
    public static function register()
    {
        add_shortcode('qpay_payment_form', [self::class, 'render']);
    }

    public static function render($atts)
    {
        $atts = shortcode_atts([
            'amount' => '0',
            'description' => 'Payment',
        ], $atts);

        ob_start();
        ?>
        <div class="qpay-payment-form" data-amount="<?php echo esc_attr($atts['amount']); ?>" data-description="<?php echo esc_attr($atts['description']); ?>">
            <div class="qpay-form-input">
                <?php if ((float) $atts['amount'] <= 0): ?>
                    <label>Дүн (₮):</label>
                    <input type="number" class="qpay-amount" min="1" required>
                <?php else: ?>
                    <p class="qpay-fixed-amount"><?php echo esc_html(number_format((float) $atts['amount'], 0)); ?>₮</p>
                <?php endif; ?>
                <button class="qpay-pay-btn" type="button">QPay-ээр төлөх</button>
            </div>
            <div class="qpay-result" style="display:none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}
