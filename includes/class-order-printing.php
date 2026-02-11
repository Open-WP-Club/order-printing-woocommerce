<?php

namespace Screeen\OrderPrinting;

defined('ABSPATH') || exit;

class Order_Printing {

    private static ?Order_Printing $instance = null;

    public static function instance(): Order_Printing {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('admin_init', [$this, 'handle_pdf_request']);
        add_filter('woocommerce_admin_order_actions', [$this, 'add_list_action'], 10, 2);
        add_action('admin_head', [$this, 'admin_css']);
    }

    /**
     * Add a meta box to the HPOS order edit screen.
     */
    public function add_meta_box(): void {
        $screen = wc_get_page_screen_id('shop-order');

        add_meta_box(
            'opw_print_pdf',
            __('Print Order', 'order-printing-woocommerce'),
            [$this, 'render_meta_box'],
            $screen,
            'side',
            'high'
        );
    }

    /**
     * Render the meta box — in HPOS the callback receives a WC_Order directly.
     */
    public function render_meta_box(\WC_Order $order): void {
        $url = wp_nonce_url(
            admin_url('admin.php?action=opw_print_pdf&order_id=' . $order->get_id()),
            'opw_print_pdf_' . $order->get_id()
        );

        printf(
            '<a href="%s" class="button button-primary" target="_blank" style="width:100%%;text-align:center;">%s</a>',
            esc_url($url),
            esc_html__('Download PDF', 'order-printing-woocommerce')
        );
    }

    /**
     * Handle the PDF generation request.
     */
    public function handle_pdf_request(): void {
        if (!isset($_GET['action']) || 'opw_print_pdf' !== $_GET['action']) {
            return;
        }

        if (!current_user_can('edit_shop_orders')) {
            wp_die(esc_html__('You do not have permission to do this.', 'order-printing-woocommerce'));
        }

        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        if (!$order_id) {
            wp_die(esc_html__('Invalid order ID.', 'order-printing-woocommerce'));
        }

        check_admin_referer('opw_print_pdf_' . $order_id);

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die(esc_html__('Order not found.', 'order-printing-woocommerce'));
        }

        $generator = new Order_PDF_Generator($order);
        $generator->stream();
        exit;
    }

    /**
     * Add a "Print PDF" action button in the orders list table.
     */
    public function add_list_action(array $actions, \WC_Order $order): array {
        $url = wp_nonce_url(
            admin_url('admin.php?action=opw_print_pdf&order_id=' . $order->get_id()),
            'opw_print_pdf_' . $order->get_id()
        );

        $actions['opw_print_pdf'] = [
            'url'    => $url,
            'name'   => __('Print PDF', 'order-printing-woocommerce'),
            'action' => 'opw_print_pdf',
        ];

        return $actions;
    }

    /**
     * Minimal CSS for the action button icon in the orders list.
     */
    public function admin_css(): void {
        $screen = get_current_screen();
        if (!$screen || 'woocommerce_page_wc-orders' !== $screen->id) {
            return;
        }

        echo '<style>
            .wc-action-button-opw_print_pdf::after {
                content: "\f497" !important;
                font-family: dashicons !important;
            }
        </style>';
    }
}
