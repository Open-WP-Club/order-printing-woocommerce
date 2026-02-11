<?php

namespace Screeen\OrderPrinting;

use Dompdf\Dompdf;
use Dompdf\Options;

defined('ABSPATH') || exit;

class Order_PDF_Generator {

    private \WC_Order $order;

    public function __construct(\WC_Order $order) {
        $this->order = $order;
    }

    /**
     * Stream the PDF directly to the browser.
     */
    public function stream(): void {
        $html = $this->render_html();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = sprintf('order-%s.pdf', $this->order->get_order_number());
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    /**
     * Build the HTML for the PDF from the template.
     */
    private function render_html(): string {
        $order = $this->order;
        $data  = $this->gather_order_data();

        ob_start();
        include OPW_PLUGIN_DIR . 'templates/order-pdf-template.php';
        return ob_get_clean();
    }

    /**
     * Collect all order data needed by the template.
     */
    private function gather_order_data(): array {
        $order = $this->order;

        $items = [];
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $items[] = [
                'name'     => $item->get_name(),
                'sku'      => $product ? $product->get_sku() : '',
                'quantity' => $item->get_quantity(),
                'total'    => $order->get_formatted_line_subtotal($item),
            ];
        }

        return [
            'order_number'    => $order->get_order_number(),
            'order_date'      => wc_format_datetime($order->get_date_created()),
            'status'          => wc_get_order_status_name($order->get_status()),
            'payment_method'  => $order->get_payment_method_title(),
            'billing'         => $order->get_formatted_billing_address(),
            'shipping'        => $order->get_formatted_shipping_address(),
            'items'           => $items,
            'subtotal'        => $order->get_subtotal_to_display(),
            'shipping_total'  => $order->get_shipping_to_display(),
            'tax_total'       => wc_price($order->get_total_tax()),
            'total'           => $order->get_formatted_order_total(),
            'customer_note'   => $order->get_customer_note(),
        ];
    }
}
