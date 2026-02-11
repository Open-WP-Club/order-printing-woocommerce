<?php
/**
 * Order PDF template.
 *
 * Variables available: $order (WC_Order), $data (array).
 *
 * @var WC_Order $order
 * @var array    $data
 */

defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 12px;
        color: #1a1a1a;
        line-height: 1.5;
        padding: 40px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        border-bottom: 2px solid #1a1a1a;
        padding-bottom: 16px;
        margin-bottom: 24px;
    }
    .header h1 {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .header .logo img {
        max-height: 50px;
        max-width: 200px;
    }
    .order-meta {
        text-align: right;
        font-size: 11px;
        color: #555;
    }
    .order-meta strong {
        color: #1a1a1a;
    }
    .addresses {
        width: 100%;
        margin-bottom: 24px;
    }
    .addresses td {
        width: 50%;
        vertical-align: top;
        padding-right: 20px;
    }
    .addresses h3 {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
        margin-bottom: 6px;
        border-bottom: 1px solid #eee;
        padding-bottom: 4px;
    }
    .addresses p {
        font-size: 12px;
        line-height: 1.6;
    }
    table.items {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
    }
    table.items thead th {
        background: #f5f5f5;
        text-align: left;
        padding: 8px 10px;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #555;
        border-bottom: 1px solid #ddd;
    }
    table.items thead th:last-child,
    table.items tbody td:last-child {
        text-align: right;
    }
    table.items tbody td {
        padding: 10px;
        border-bottom: 1px solid #eee;
        font-size: 12px;
    }
    .sku {
        color: #999;
        font-size: 10px;
    }
    .totals {
        width: 260px;
        margin-left: auto;
        margin-bottom: 24px;
    }
    .totals table {
        width: 100%;
        border-collapse: collapse;
    }
    .totals td {
        padding: 5px 0;
        font-size: 12px;
    }
    .totals td:last-child {
        text-align: right;
    }
    .totals .grand-total td {
        font-weight: 700;
        font-size: 14px;
        border-top: 2px solid #1a1a1a;
        padding-top: 8px;
    }
    .note {
        background: #fafafa;
        border-left: 3px solid #ddd;
        padding: 10px 14px;
        font-size: 11px;
        color: #555;
        margin-bottom: 24px;
    }
    .note strong {
        display: block;
        margin-bottom: 4px;
        color: #1a1a1a;
    }
    .footer {
        text-align: center;
        font-size: 10px;
        color: #aaa;
        border-top: 1px solid #eee;
        padding-top: 12px;
    }
</style>
</head>
<body>

<table style="width:100%; margin-bottom:24px;">
    <tr>
        <td>
            <?php if (!empty($data['logo'])) : ?>
                <div class="logo"><img src="<?php echo esc_attr($data['logo']); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"></div>
            <?php else : ?>
                <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <?php endif; ?>
        </td>
        <td class="order-meta">
            <strong><?php
                /* translators: %s: order number */
                printf(esc_html__('Order #%s', 'order-printing-woocommerce'), esc_html($data['order_number']));
            ?></strong><br>
            <?php echo esc_html($data['order_date']); ?><br>
            <?php echo esc_html($data['status']); ?><br>
            <?php echo esc_html($data['payment_method']); ?>
        </td>
    </tr>
</table>

<table class="addresses">
    <tr>
        <td>
            <h3><?php esc_html_e('Billing Address', 'order-printing-woocommerce'); ?></h3>
            <p><?php echo wp_kses_post($data['billing']); ?></p>
        </td>
        <?php if (!empty($data['shipping'])) : ?>
        <td>
            <h3><?php esc_html_e('Shipping Address', 'order-printing-woocommerce'); ?></h3>
            <p><?php echo wp_kses_post($data['shipping']); ?></p>
        </td>
        <?php endif; ?>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th><?php esc_html_e('Product', 'order-printing-woocommerce'); ?></th>
            <th><?php esc_html_e('Qty', 'order-printing-woocommerce'); ?></th>
            <th><?php esc_html_e('Total', 'order-printing-woocommerce'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['items'] as $item) : ?>
        <tr>
            <td>
                <?php echo esc_html($item['name']); ?>
                <?php if (!empty($item['sku'])) : ?>
                    <br><span class="sku">SKU: <?php echo esc_html($item['sku']); ?></span>
                <?php endif; ?>
            </td>
            <td><?php echo esc_html($item['quantity']); ?></td>
            <td><?php echo wp_kses_post($item['total']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="totals">
    <table>
        <tr>
            <td><?php esc_html_e('Subtotal', 'order-printing-woocommerce'); ?></td>
            <td><?php echo wp_kses_post($data['subtotal']); ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Shipping', 'order-printing-woocommerce'); ?></td>
            <td><?php echo wp_kses_post($data['shipping_total']); ?></td>
        </tr>
        <?php if ($order->get_total_tax() > 0) : ?>
        <tr>
            <td><?php esc_html_e('Tax', 'order-printing-woocommerce'); ?></td>
            <td><?php echo wp_kses_post($data['tax_total']); ?></td>
        </tr>
        <?php endif; ?>
        <tr class="grand-total">
            <td><?php esc_html_e('Total', 'order-printing-woocommerce'); ?></td>
            <td><?php echo wp_kses_post($data['total']); ?></td>
        </tr>
    </table>
</div>

<?php if (!empty($data['customer_note'])) : ?>
<div class="note">
    <strong><?php esc_html_e('Customer Note', 'order-printing-woocommerce'); ?></strong>
    <?php echo esc_html($data['customer_note']); ?>
</div>
<?php endif; ?>

<div class="footer">
    <?php echo esc_html(get_bloginfo('name')); ?> &mdash; <?php echo esc_html(home_url()); ?>
</div>

</body>
</html>
