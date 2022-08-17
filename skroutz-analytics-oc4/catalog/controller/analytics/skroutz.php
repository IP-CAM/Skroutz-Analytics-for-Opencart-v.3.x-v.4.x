<?php

/**
 * Shopflix Analytics
 * @author Prionysis
 * @website https://github.com/Prionysis
 * @version 1.1
 */

namespace Opencart\Catalog\Controller\Extension\SkroutzAnalytics\Analytics;

use Opencart\System\Engine\Controller;

class Skroutz extends Controller
{
	public function index(): string
	{
		$data = [];

		if ($this->config->get('analytics_skroutz_status') && $this->config->get('analytics_skroutz_code')) {
			$data['analytics_skroutz_code'] = $this->config->get('analytics_skroutz_code');
		}

		var_dump('index');
		return $this->load->view('extension/skroutz_analytics/analytics/skroutz', $data);
	}

	public function loadCheckoutScript(&$route, &$data, &$output): void
	{
		var_dump('loadCheckoutScript');
		if ($this->config->get('analytics_skroutz_status') && $this->config->get('analytics_skroutz_code')) {
			if (isset($this->session->data['order_id'])) {
				$order_id = $this->session->data['order_id'];
			} else if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = null;
			}

			if ($order_id) {
				$this->load->model('extension/skroutz_analytics/analytics/skroutz');

				$order = $this->model_extension_skroutz_analytics_analytics_skroutz->getOrder($order_id);

				if (isset($order)) {
					if (!isset($order['shipping'])) {
						$order['shipping'] = 0;
					}

					if (!isset($order['tax'])) {
						$order['tax'] = 0;
					}

					$data['skroutz']['order_id'] = $order['order_id'];
					$data['skroutz']['revenue'] = $order['revenue'];
					$data['skroutz']['shipping'] = $order['shipping'];
					$data['skroutz']['tax'] = $order['tax'];
					$data['skroutz']['paid_by'] = $order['payment_code'];
					$data['skroutz']['paid_by_descr'] = $order['payment_method'];
					$data['skroutz']['products'] = $this->model_extension_skroutz_analytics_analytics_skroutz->getOrderProducts($order_id);

					$html = $this->load->view('extension/skroutz_analytics/analytics/skroutz_checkout', $data);
					$html .= '<footer>';

					$output = str_replace('<footer>', $html, $output);
				}
			}
		}
	}

	public function loadReviewsWidget(&$route, &$data, &$output): void
	{
		var_dump('loadReviewsWidget');
		if ($this->config->get('analytics_skroutz_status') && $this->config->get('analytics_skroutz_widget')) {
			if ($this->config->get('analytics_skroutz_widget') == 'inline') {
				$widget = '<div id="skroutz-product-reviews-inline" data-product-id="' . $data['product_id'] . '"></div>';
			} else {
				$widget = '<div id="skroutz-product-reviews-extended" data-product-id="' . $data['product_id'] . '"></div>';
			}

			if ($this->config->get('analytics_skroutz_replace_html')) {
				$replace = $this->config->get('analytics_skroutz_replace_html');
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
}