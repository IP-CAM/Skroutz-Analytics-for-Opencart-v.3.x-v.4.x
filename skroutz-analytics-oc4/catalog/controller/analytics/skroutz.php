<?php

/**
 * Skroutz Analytics
 * @author Dionysis Pasenidis
 * @link https://github.com/Prionysis
 * @version 1.3
 */

namespace Opencart\Catalog\Controller\Extension\SkroutzAnalytics\Analytics;

use Opencart\System\Engine\Controller;

class Skroutz extends Controller
{
    private string $code;
    private bool $status;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->code = $this->config->get('analytics_skroutz_code');
        $this->status = (bool)$this->config->get('analytics_skroutz_status');
    }

    public function index(): string
    {
        if (!$this->status || !$this->code) {
            return '';
        }

        return $this->load->view('extension/skroutz_analytics/analytics/skroutz', [
            'code' => $this->config->get('analytics_skroutz_code'),
        ]);
    }

    public function loadCheckoutScript(string &$route, array &$args, string &$output): void
    {
        if (!$this->status || !$this->code) {
            return;
        }

        $order_id = $this->session->data['skroutz_order_id'] ?? $this->session->data['order_id'] ?? $this->request->get['order_id'] ?? null;

        if (!$order_id) {
            return;
        }

        $this->load->model('extension/skroutz_analytics/analytics/skroutz');

        $order = $this->model_extension_skroutz_analytics_analytics_skroutz->getOrder($order_id);

        if (!$order) {
            return;
        }

        $data['order_id'] = $order['order_id'];
        $data['revenue'] = $order['revenue'];
        $data['shipping'] = $order['shipping'] ?? 0;
        $data['tax'] = $order['tax'] ?? 0;
        $data['paid_by'] = $order['payment_code'];
        $data['paid_by_descr'] = $order['payment_method'];
        $data['products'] = $this->model_extension_skroutz_analytics_analytics_skroutz->getOrderProducts($order_id);

        $output = str_replace('<footer>', $this->load->view('extension/skroutz_analytics/analytics/skroutz_checkout', $data) . '<footer>', $output);
    }

    public function loadReviewsWidget(string &$route, array &$args, string &$output): void
    {
        if (!$this->status || !$this->code) {
            return;
        }

        if ($this->config->get('analytics_skroutz_widget_type') == 'inline') {
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