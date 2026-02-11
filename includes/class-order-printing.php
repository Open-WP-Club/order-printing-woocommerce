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
        add_action('wp_ajax_opw_preview_html', [$this, 'handle_html_preview']);
        add_action('wp_ajax_opw_bulk_print', [$this, 'handle_bulk_print']);
        add_filter('woocommerce_get_sections_advanced', [$this, 'add_settings_section']);
        add_filter('woocommerce_get_settings_advanced', [$this, 'get_settings'], 10, 2);
        add_filter('bulk_actions-woocommerce_page_wc-orders', [$this, 'register_bulk_action']);
        add_filter('handle_bulk_actions-woocommerce_page_wc-orders', [$this, 'handle_bulk_action'], 10, 3);
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
        $order_id = $order->get_id();
        $preview_nonce = wp_create_nonce('opw_preview_html_' . $order_id);
        ?>
        <button type="button" class="button button-primary" id="opw-print-btn"
            data-order-id="<?php echo esc_attr($order_id); ?>"
            data-nonce="<?php echo esc_attr($preview_nonce); ?>"
            style="width:100%;text-align:center;">
            <?php esc_html_e('Print Order', 'order-printing-woocommerce'); ?>
        </button>
        <iframe id="opw-print-frame" style="position:absolute;left:-9999px;width:0;height:0;border:none;"></iframe>

        <script>
        (function(){
            var btn   = document.getElementById('opw-print-btn'),
                frame = document.getElementById('opw-print-frame');

            function triggerPrint(){
                if (btn.disabled) return;
                btn.disabled = true;
                var url = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>'
                    + '?action=opw_preview_html'
                    + '&order_id=' + btn.dataset.orderId
                    + '&_wpnonce=' + btn.dataset.nonce;
                frame.src = url;
                frame.onload = function(){
                    frame.contentWindow.print();
                    btn.disabled = false;
                };
            }

            btn.addEventListener('click', triggerPrint);

            document.addEventListener('keydown', function(e){
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    triggerPrint();
                }
            });
        })();
        </script>
        <?php
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
     * AJAX handler: return rendered HTML for the inline preview iframe.
     */
    public function handle_html_preview(): void {
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;

        if (!current_user_can('edit_shop_orders') || !$order_id) {
            wp_die(-1);
        }

        check_ajax_referer('opw_preview_html_' . $order_id);

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die(-1);
        }

        $generator = new Order_PDF_Generator($order);
        echo $generator->render_html_public();
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
     * Register bulk action in orders list.
     */
    public function register_bulk_action(array $actions): array {
        $actions['opw_bulk_print'] = __('Print Orders', 'order-printing-woocommerce');
        return $actions;
    }

    /**
     * Handle the bulk action — redirect to a print page.
     */
    public function handle_bulk_action(string $redirect_to, string $action, array $ids): string {
        if ('opw_bulk_print' !== $action || empty($ids)) {
            return $redirect_to;
        }

        $key = 'opw_bulk_' . wp_generate_password(12, false);
        set_transient($key, array_map('absint', $ids), 300);

        $url = wp_nonce_url(
            admin_url('admin-ajax.php?action=opw_bulk_print&batch=' . $key),
            'opw_bulk_print'
        );

        return add_query_arg('opw_print_url', rawurlencode($url), $redirect_to);
    }

    /**
     * AJAX handler: render multiple orders as HTML with page breaks, auto-print.
     */
    public function handle_bulk_print(): void {
        if (!current_user_can('edit_shop_orders')) {
            wp_die(-1);
        }

        check_ajax_referer('opw_bulk_print');

        $key = isset($_GET['batch']) ? sanitize_text_field($_GET['batch']) : '';
        $ids = $key ? get_transient($key) : [];
        if (empty($ids) || !is_array($ids)) {
            wp_die(-1);
        }
        delete_transient($key);

        $parts = [];
        foreach ($ids as $id) {
            $order = wc_get_order($id);
            if (!$order) {
                continue;
            }
            $generator = new Order_PDF_Generator($order);
            $parts[]   = $generator->render_html_public();
        }

        if (empty($parts)) {
            wp_die(-1);
        }

        // Extract <body> content from each rendered HTML and combine with page breaks.
        $bodies = [];
        foreach ($parts as $html) {
            if (preg_match('/<body[^>]*>(.*)<\/body>/s', $html, $m)) {
                $bodies[] = $m[1];
            }
        }

        // Use the first template's <head> for styles.
        $head = '';
        if (preg_match('/<head[^>]*>(.*)<\/head>/s', $parts[0], $m)) {
            $head = $m[1];
        }

        echo '<!DOCTYPE html><html><head>' . $head . '
        <style>.page-break { page-break-after: always; }</style>
        </head><body>';

        foreach ($bodies as $i => $body) {
            echo '<div class="page-break">' . $body . '</div>';
        }

        echo '</body></html>';
        exit;
    }

    /**
     * Add settings section under WooCommerce > Settings > Advanced.
     */
    public function add_settings_section(array $sections): array {
        $sections['opw_printing'] = __('Order Printing', 'order-printing-woocommerce');
        return $sections;
    }

    /**
     * Settings fields for the Order Printing section.
     */
    public function get_settings(array $settings, string $current_section): array {
        if ('opw_printing' !== $current_section) {
            return $settings;
        }

        return [
            [
                'title' => __('Order Printing', 'order-printing-woocommerce'),
                'type'  => 'title',
                'id'    => 'opw_printing_options',
            ],
            [
                'title'    => __('Company Info', 'order-printing-woocommerce'),
                'desc_tip' => __('Displayed in the header of printed orders, next to the logo. Use for contact details, VAT number, etc.', 'order-printing-woocommerce'),
                'id'       => 'opw_company_info',
                'type'     => 'textarea',
                'css'      => 'width:400px; height:120px;',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'opw_printing_options',
            ],
        ];
    }

    /**
     * CSS and JS for the orders list page.
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

        // If redirected back from bulk action, load print in hidden iframe.
        if (!empty($_GET['opw_print_url'])) {
            $print_url = esc_url(rawurldecode($_GET['opw_print_url']));
            echo '<iframe id="opw-bulk-frame" src="' . $print_url . '" style="position:absolute;left:-9999px;width:0;height:0;border:none;"></iframe>';
            echo '<script>
                document.getElementById("opw-bulk-frame").onload = function(){
                    this.contentWindow.print();
                };
            </script>';
        }
    }
}
