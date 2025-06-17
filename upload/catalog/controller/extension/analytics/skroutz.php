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

        $this->session->data['skroutz_order_id'] = $this->session->data['order_id'] ?? 0;
    }

    public function viewCommonSuccessBefore(string $route, array &$args): void
    {
        $order_id = $this->session->data['skroutz_order_id'] ?? 0;

        if (!$order_id || !$this->config->get('analytics_skroutz_status')) {
            return;
        }

        $this->load->model('extension/analytics/skroutz');

        $order_info = $this->model_extension_analytics_skroutz->getOrder($order_id);

        if (!$order_info) {
            return;
        }

        $data = [
            'order_id'      => (int)$order_info['order_id'],
            'revenue'       => (float)$order_info['sub_total'] + (float)$order_info['tax'] + (float)$order_info['shipping'],
            'shipping'      => (float)$order_info['shipping'],
            'tax'           => (float)$order_info['tax'],
            'paid_by'       => $order_info['payment_code'],
            'paid_by_descr' => $order_info['payment_method'],
            'products'      => $this->model_extension_analytics_skroutz->getOrderProducts($order_id),
        ];

        $args['footer'] = $this->load->view('extension/analytics/skroutz_checkout', $data) . $args['footer'];

        unset($this->session->data['skroutz_order_id']);
    }

    public function viewProductProductAfter(string $route, array $args, string &$output): void
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