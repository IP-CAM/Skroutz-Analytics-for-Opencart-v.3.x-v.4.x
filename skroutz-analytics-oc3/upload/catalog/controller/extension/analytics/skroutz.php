<?php

/**
 * Shopflix Analytics
 * @author Prionysis
 * @link https://github.com/Prionysis
 * @version 1.2
 */

class ControllerExtensionAnalyticsSkroutz extends Controller
{
	public function index()
	{
		$data = [];

		if ($this->config->get('analytics_skroutz_status') && $this->config->get('analytics_skroutz_code')) {
			$data['analytics_skroutz_code'] = $this->config->get('analytics_skroutz_code');
		}

		return $this->load->view('extension/analytics/skroutz', $data);
	}

	public function loadCheckoutScript(&$route, &$data, &$output)
	{
		if ($this->config->get('analytics_skroutz_status') && $this->config->get('analytics_skroutz_code')) {
			if (isset($this->session->data['order_id'])) {
				$order_id = $this->session->data['order_id'];
			} else if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = null;
			}

			if ($order_id) {
				$this->load->model('extension/analytics/skroutz');

				$order = $this->model_extension_analytics_skroutz->getOrder($order_id);

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
					$data['skroutz']['products'] = $this->model_extension_analytics_skroutz->getOrderProducts($order_id);

					$html = $this->load->view('extension/analytics/skroutz_checkout', $data);
					$html .= '<footer>';

					$output = str_replace('<footer>', $html, $output);
				}
			}
		}
	}

	public function loadReviewsWidget(&$route, &$data, &$output)
	{
		if ($this->config->get('analytics_skroutz_status') && $this->config->get('analytics_skroutz_widget_status')) {
			if ($this->config->get('analytics_skroutz_widget_type') == 'inline') {
				$widget = '<div id="skroutz-product-reviews-inline" data-product-id="' . $data['product_id'] . '"></div>';
			} else {
				$widget = '<div id="skroutz-product-reviews-extended" data-product-id="' . $data['product_id'] . '"></div>';
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
}