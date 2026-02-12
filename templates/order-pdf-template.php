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
        font-size: 0.6875rem;
        color: #000;
        line-height: 1.4;
        padding: 1.25rem 1.875rem;
    }
    /* ── Header ── */
    .header {
        width: 100%;
        border-bottom: 0.125rem solid #000;
        padding-bottom: 0.625rem;
        margin-bottom: 0.875rem;
    }
    .header td {
        vertical-align: top;
    }
    .header h1 {
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.031rem;
    }
    .header .logo img {
        max-height: 2.5rem;
        max-width: 10rem;
    }
    .company-info {
        font-size: 0.625rem;
        line-height: 1.5;
        padding: 0 1rem;
    }
    .order-meta {
        text-align: right;
        font-size: 0.625rem;
        white-space: nowrap;
    }
    .order-meta strong {
        font-size: 0.75rem;
    }
    /* ── Addresses ── */
    .addresses {
        width: 100%;
        margin-bottom: 0.875rem;
    }
    .addresses td {
        width: 50%;
        vertical-align: top;
        padding-right: 1rem;
    }
    .addresses h3 {
        font-size: 0.5625rem;
        letter-spacing: 0.0625rem;
        margin-bottom: 0.25rem;
        border-bottom: 0.0625rem solid #000;
        padding-bottom: 0.1875rem;
    }
    .addresses p {
        font-size: 0.6875rem;
        line-height: 1.5;
    }
    /* ── Items ── */
    table.items {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0.875rem;
    }
    table.items thead th {
        background: #f5f5f5;
        text-align: left;
        padding: 0.3125rem 0.5rem;
        font-size: 0.5625rem;
        letter-spacing: 0.05rem;
        border-bottom: 0.0625rem solid #000;
    }
    table.items thead th:nth-last-child(-n+2),
    table.items tbody td:nth-last-child(-n+2) {
        text-align: right;
    }
    table.items tbody td {
        padding: 0.375rem 0.5rem;
        border-bottom: 0.0625rem solid #000;
        font-size: 0.6875rem;
    }
    table.items .col-sku {
        font-size: 0.5625rem;
        white-space: nowrap;
    }
    /* ── Totals ── */
    .contact-info {
        font-size: 0.625rem;
        margin-top: 0.25rem;
    }
    .totals {
        width: 18rem;
        margin-left: auto;
        margin-bottom: 0.875rem;
    }
    .totals table {
        width: 100%;
        border-collapse: collapse;
    }
    .totals td {
        padding: 0.1875rem 0;
        font-size: 0.6875rem;
    }
    .totals td:last-child {
        text-align: right;
    }
    .totals .grand-total td {
        font-weight: 700;
        font-size: 0.8125rem;
        border-top: 0.125rem solid #000;
        padding-top: 0.375rem;
    }
    /* ── Note ── */
    .note {
        background: #fafafa;
        border-left: 0.1875rem solid #000;
        padding: 0.5rem 0.75rem;
        font-size: 0.625rem;
        margin-bottom: 0.875rem;
    }
    .note strong {
        display: block;
        margin-bottom: 0.125rem;
    }
    .totals .subtotal-row td {
        border-bottom: 0.0625rem solid #000;
        padding-bottom: 0.375rem;
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
            <th>SKU</th>
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
    <?php echo esc_html($data['customer_note']); ?>
</div>
<?php endif; ?>

</body>
</html>
