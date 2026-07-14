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
        font-size: 11px;
        color: #000;
        line-height: 1.4;
        padding: 20px 30px;
    }
    /* ── Header ── */
    .header {
        width: 100%;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 14px;
    }
    .header td {
        vertical-align: top;
    }
    .header h1 {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .header .logo img {
        max-height: 40px;
        max-width: 160px;
    }
    .company-info {
        font-size: 10px;
        line-height: 1.5;
        padding: 0 16px;
    }
    .order-meta {
        text-align: right;
        font-size: 10px;
        white-space: nowrap;
    }
    .order-meta strong {
        font-size: 12px;
    }
    /* ── Addresses ── */
    .addresses {
        width: 100%;
        margin-bottom: 14px;
    }
    .addresses td {
        width: 50%;
        vertical-align: top;
        padding-right: 16px;
    }
    .addresses h3 {
        font-size: 9px;
        letter-spacing: 1px;
        margin-bottom: 4px;
        border-bottom: 1px solid #000;
        padding-bottom: 3px;
    }
    .addresses p {
        font-size: 11px;
        line-height: 1.5;
    }
    /* ── Items ── */
    table.items {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    table.items thead th {
        background: #f5f5f5;
        text-align: left;
        padding: 5px 8px;
        font-size: 9px;
        letter-spacing: 1px;
        border-bottom: 1px solid #000;
    }
    table.items thead th:nth-last-child(-n+2),
    table.items tbody td:nth-last-child(-n+2) {
        text-align: right;
    }
    table.items tbody td {
        padding: 6px 8px;
        border-bottom: 1px solid #000;
        font-size: 11px;
    }
    table.items .col-sku {
        font-size: 9px;
        white-space: nowrap;
    }
    /* ── Totals ── */
    .contact-info {
        font-size: 10px;
        margin-top: 4px;
    }
    .totals {
        width: 288px;
        margin-left: auto;
        margin-bottom: 14px;
    }
    .totals table {
        width: 100%;
        border-collapse: collapse;
    }
    .totals td {
        padding: 3px 0;
        font-size: 11px;
    }
    .totals td:last-child {
        text-align: right;
    }
    .totals .grand-total td {
        font-weight: 700;
        font-size: 13px;
        border-top: 2px solid #000;
        padding-top: 6px;
    }
    /* ── Note ── */
    .note {
        background: #fafafa;
        border-left: 3px solid #000;
        padding: 8px 12px;
        font-size: 10px;
        margin-bottom: 14px;
    }
    .note strong {
        display: block;
        margin-bottom: 2px;
    }
    .totals .subtotal-row td {
        border-bottom: 1px solid #000;
        padding-bottom: 6px;
    }
    /* @page outside @media print so DOMPDF applies it unconditionally */
    @page {
        margin: 0;
    }
    @media print {
        @page {
            margin: 0;
        }
    }
</style>
</head>
<body>

<table class="header">
    <tr>
        <td>
            <?php if (!empty($data['logo'])) : ?>
                <div class="logo"><img src="<?php echo esc_attr($data['logo']); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"></div>
            <?php else : ?>
                <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <?php endif; ?>
        </td>
        <?php if (!empty($data['company_info'])) : ?>
        <td class="company-info">
            <?php echo nl2br(esc_html($data['company_info'])); ?>
        </td>
        <?php endif; ?>
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
            <?php if (!empty($data['billing_phone']) || !empty($data['billing_email'])) : ?>
            <div class="contact-info">
                <?php if (!empty($data['billing_phone'])) : ?>
                    <?php echo esc_html($data['billing_phone']); ?><br>
                <?php endif; ?>
                <?php if (!empty($data['billing_email'])) : ?>
                    <?php echo esc_html($data['billing_email']); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
            <th><?php esc_html_e('SKU', 'order-printing-woocommerce'); ?></th>
            <th><?php esc_html_e('Product', 'order-printing-woocommerce'); ?></th>
            <th><?php esc_html_e('Qty', 'order-printing-woocommerce'); ?></th>
            <th><?php esc_html_e('Unit Price', 'order-printing-woocommerce'); ?></th>
            <th><?php esc_html_e('Total', 'order-printing-woocommerce'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['items'] as $item) : ?>
        <tr>
            <td class="col-sku"><?php echo esc_html($item['sku']); ?></td>
            <td><?php echo esc_html($item['name']); ?></td>
            <td><?php echo esc_html($item['quantity']); ?></td>
            <td><?php echo wp_kses_post($item['unit_price']); ?></td>
            <td><?php echo wp_kses_post($item['total']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="totals">
    <table>
        <tr class="subtotal-row">
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
    <?php echo nl2br(esc_html($data['customer_note'])); ?>
</div>
<?php endif; ?>

</body>
</html>
