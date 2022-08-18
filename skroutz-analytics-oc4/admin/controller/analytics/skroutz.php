<?php

/**
 * Shopflix Analytics
 * @author Prionysis
 * @link https://github.com/Prionysis
 * @version 1.2
 */

namespace Opencart\Admin\Controller\Extension\SkroutzAnalytics\Analytics;

use Opencart\System\Engine\Controller;

class Skroutz extends Controller
{
	public function index()
	{
		$this->load->language('extension/skroutz_analytics/analytics/skroutz');

		$this->document->setTitle($this->language->get('heading_title'));

		// Breadcrumbs
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/skroutz_analytics/analytics/skroutz', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		];

		// Buttons
		$data['save'] = $this->url->link('extension/skroutz_analytics/analytics/skroutz|save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true);

		// Code
		$data['analytics_skroutz_code'] = $this->model_setting_setting->getValue('analytics_skroutz_code', $this->request->get['store_id']);

		// Status
		$data['analytics_skroutz_status'] = $this->model_setting_setting->getValue('analytics_skroutz_status', $this->request->get['store_id']);

		// Widget Status
		$data['analytics_skroutz_widget_status'] = $this->model_setting_setting->getValue('analytics_skroutz_widget_status', $this->request->get['store_id']);

		// Widget Type
		$data['analytics_skroutz_widget_type'] = $this->model_setting_setting->getValue('analytics_skroutz_widget_type', $this->request->get['store_id']);

		// Widget Replace HTML
		$data['analytics_skroutz_replace_html'] = $this->model_setting_setting->getValue('analytics_skroutz_replace_html', $this->request->get['store_id']);

		// Widget Replace Position
		$data['analytics_skroutz_replace_position'] = $this->model_setting_setting->getValue('analytics_skroutz_replace_position', $this->request->get['store_id']);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/skroutz_analytics/analytics/skroutz', $data));
	}

	public function save(): void
	{
		$this->load->language('extension/skroutz_analytics/analytics/skroutz');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/skroutz_analytics/analytics/skroutz')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (empty($this->request->post['analytics_skroutz_code'])) {
			$json['error']['code'] = $this->language->get('error_code');
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('analytics_skroutz', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');
			$this->model_setting_event->deleteEventByCode('analytics_skroutz');


			$this->model_setting_event->addEvent([
				'code' => 'analytics_skroutz',
				'description' => '',
				'trigger' => 'catalog/view/common/success/after',
				'action' => 'extension/skroutz_analytics/analytics/skroutz|loadCheckoutScript',
				'status' => true,
				'sort_order' => 1
			]);


			$this->model_setting_event->addEvent([
				'code' => 'analytics_skroutz',
				'description' => '',
				'trigger' => 'catalog/view/product/product/after',
				'action' => 'extension/skroutz_analytics/analytics/skroutz|loadReviewsWidget',
				'status' => true,
				'sort_order' => 1
			]);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void
	{
		// Event
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('analytics_skroutz');

		if (version_compare(VERSION, '4.0.1.0', '>=')) {
			$this->model_setting_event->addEvent([
				'code' => 'analytics_skroutz',
				'description' => '',
				'trigger' => 'catalog/view/common/success/after',
				'action' => 'extension/skroutz_analytics/analytics/skroutz|loadCheckoutScript',
				'status' => true,
				'sort_order' => 0
			]);

			$this->model_setting_event->addEvent([
				'code' => 'analytics_skroutz',
				'description' => '',
				'trigger' => 'catalog/view/product/product/after',
				'action' => 'extension/skroutz_analytics/analytics/skroutz|loadReviewsWidget',
				'status' => true,
				'sort_order' => 0
			]);
		} else {
			$this->model_setting_event->addEvent('analytics_skroutz', '', 'catalog/view/common/success/after', 'extension/skroutz_analytics/analytics/skroutz|loadCheckoutScript');
			$this->model_setting_event->addEvent('analytics_skroutz', '', 'catalog/view/product/product/after', 'extension/skroutz_analytics/analytics/skroutz|loadReviewsWidget');
		}

		// Permissions
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/skroutz_analytics/analytics/skroutz');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/skroutz_analytics/analytics/skroutz');
	}

	public function uninstall(): void
	{
		// Events
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('analytics_skroutz');

		// Permissions
		$this->load->model('user/user_group');

		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/skroutz_analytics/analytics/skroutz');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/skroutz_analytics/analytics/skroutz');
	}
}