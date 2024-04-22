<?php

/**
 * Skroutz Analytics
 * @author Dionysis Pasenidis
 * @link https://github.com/Prionysis
 * @version 1.3
 *
 * @property Loader $load
 * @property Config $config
 * @property Session $session
 * @property Request $request
 * @property ModelExtensionAnalyticsSkroutz $model_extension_analytics_skroutz
 */
class ControllerExtensionAnalyticsSkroutz extends Controller
{
    public function index(): string
    {
        if (!$this->config->get('analytics_skroutz_status') || !$this->config->get('analytics_skroutz_code')) {
            return '';
        }

        return $this->load->view('extension/analytics/skroutz', [
            'code' => $this->config->get('analytics_skroutz_code'),
        ]);
    }

    public function controllerCheckoutSuccessBefore(): void
    {
        if (!$this->config->get('analytics_skroutz_status')) {
            return;
        }

        $this->session->data['skroutz_order_id'] = $this->session->data['order_id'] ?? null;
    }

    public function viewCommonSuccessAfter(string &$route, array $args, string &$output): void
    {
        if (!$this->config->get('analytics_skroutz_status')) {
            return;
        }

        $order_id = $this->session->data['skroutz_order_id'] ?? 0;

        if (!$order_id) {
            return;
        }

        $this->load->model('extension/analytics/skroutz');

        $order = $this->model_extension_analytics_skroutz->getOrder($order_id);

        if (!$order) {
            return;
        }

        $output = str_replace('<footer>', $this->load->view('extension/analytics/skroutz_checkout', [
                'order_id'      => (int)$order['order_id'],
                'revenue'       => (float)$order['revenue'],
                'shipping'      => (float)$order['shipping'],
                'tax'           => (float)$order['tax'],
                'paid_by'       => $order['payment_code'],
                'paid_by_descr' => $order['payment_method'],
                'products'      => $this->model_extension_analytics_skroutz->getOrderProducts($order_id),
            ]) . '<footer>', $output);

        unset($this->session->data['skroutz_order_id']);
    }

    public function viewProductProductAfter(string &$route, array $args, string &$output): void
    {
        if (!$this->config->get('analytics_skroutz_status')) {
            return;
        }

        if ($this->config->get('analytics_skroutz_widget_type') === 'inline') {
            $widget = '<div id="skroutz-product-reviews-inline" data-product-id="' . $args['product_id'] . '"></div>';
        } else {
            $widget = '<div id="skroutz-product-reviews-extended" data-product-id="' . $args['product_id'] . '"></div>';
        }

        if ($this->config->get('analytics_skroutz_replace_html')) {
            $replace = html_entity_decode($this->config->get('analytics_skroutz_replace_html'));
        } else {
            $replace = '<div class="rating">';
        }

        if ($this->config->get('analytics_skroutz_replace_position')) {
            $output = str_replace($replace, $replace . $widget, $output);
        } else {
            $output = str_replace($replace, $widget . $replace, $output);
        }
    }
}