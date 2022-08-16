<?php

/**
 * Shopflix Analytics
 * @author Prionysis
 * @website https://github.com/Prionysis
 * @version 1.0
 */

class ControllerExtensionAnalyticsSkroutz extends Controller
{
	public function index()
	{
		if ($this->config->get('analytics_skroutz_status')) {
			$data['analytics_skroutz_code'] = $this->config->get('analytics_skroutz_code');

			return $this->load->view('extension/analytics/skroutz', $data);
		}
	}

	public function success(&$route, &$data, &$output)
	{
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
