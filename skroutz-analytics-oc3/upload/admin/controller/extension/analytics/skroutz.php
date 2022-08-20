<?php

/**
 * Shopflix Analytics
 * @author Dionysis Pasenidis
 * @link https://github.com/Prionysis
 * @version 1.2
 */

class ControllerExtensionAnalyticsSkroutz extends Controller
{
	private $error = [];

	public function index()
	{
		$this->load->language('extension/analytics/skroutz');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('analytics_skroutz', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true));
		}

		// Errors
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

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
			'href' => $this->url->link('extension/analytics/skroutz', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		];

		// Buttons
		$data['action'] = $this->url->link('extension/analytics/skroutz', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true);

		$data['user_token'] = $this->session->data['user_token'];

		// Code
		if (isset($this->request->post['analytics_skroutz_code'])) {
			$data['analytics_skroutz_code'] = $this->request->post['analytics_skroutz_code'];
		} else {
			$data['analytics_skroutz_code'] = $this->model_setting_setting->getSettingValue('analytics_skroutz_code', $this->request->get['store_id']);
		}

		// Status
		if (isset($this->request->post['analytics_skroutz_status'])) {
			$data['analytics_skroutz_status'] = $this->request->post['analytics_skroutz_status'];
		} else {
			$data['analytics_skroutz_status'] = $this->model_setting_setting->getSettingValue('analytics_skroutz_status', $this->request->get['store_id']);
		}

		// Widget Status
		if (isset($this->request->post['analytics_skroutz_widget_status'])) {
			$data['analytics_skroutz_widget_status'] = $this->request->post['analytics_skroutz_widget_status'];
		} else {
			$data['analytics_skroutz_widget_status'] = $this->model_setting_setting->getSettingValue('analytics_skroutz_widget_status', $this->request->get['store_id']);
		}

		// Widget Type
		if (isset($this->request->post['analytics_skroutz_widget_type'])) {
			$data['analytics_skroutz_widget_type'] = $this->request->post['analytics_skroutz_widget_type'];
		} else {
			$data['analytics_skroutz_widget_type'] = $this->model_setting_setting->getSettingValue('analytics_skroutz_widget_type', $this->request->get['store_id']);
		}

		// Widget Replace HTML
		if (isset($this->request->post['analytics_skroutz_replace_html'])) {
			$data['analytics_skroutz_replace_html'] = $this->request->post['analytics_skroutz_replace_html'];
		} else {
			$data['analytics_skroutz_replace_html'] = $this->model_setting_setting->getSettingValue('analytics_skroutz_replace_html', $this->request->get['store_id']);
		}

		// Widget Replace Position
		if (isset($this->request->post['analytics_skroutz_replace_position'])) {
			$data['analytics_skroutz_replace_position'] = $this->request->post['analytics_skroutz_replace_position'];
		} else {
			$data['analytics_skroutz_replace_position'] = $this->model_setting_setting->getSettingValue('analytics_skroutz_replace_position', $this->request->get['store_id']);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/analytics/skroutz', $data));
	}

	public function validate()
	{
		$this->load->language('extension/analytics/skroutz');

		if (!$this->user->hasPermission('modify', 'extension/analytics/skroutz')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (empty($this->request->post['analytics_skroutz_code'])) {
			$this->error['code'] = $this->language->get('error_code');
		}

		return !$this->error;
	}

	public function install()
	{
		// Event
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('analytics_skroutz');

		$this->model_setting_event->addEvent('analytics_skroutz', 'catalog/view/common/success/after', 'extension/analytics/skroutz/loadCheckoutScript');
		$this->model_setting_event->addEvent('analytics_skroutz', 'catalog/view/product/product/after', 'extension/analytics/skroutz/loadReviewsWidget');

		// Permissions
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/analytics/skroutz');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/analytics/skroutz');
	}

	public function uninstall()
	{
		// Events
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('analytics_skroutz');

		// Permissions
		$this->load->model('user/user_group');

		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/analytics/skroutz');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/analytics/skroutz');
	}
}