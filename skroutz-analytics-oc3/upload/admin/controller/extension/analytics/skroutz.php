<?php

/**
 * Skroutz Analytics
 * @author Dionysis Pasenidis
 * @link https://github.com/Prionysis
 * @version 1.2
 */

class ControllerExtensionAnalyticsSkroutz extends Controller
{
    private $error = [];

    public function index(): void
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
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_code'] = $this->error['code'] ?? '';

        // Breadcrumbs
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/analytics/skroutz', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true),
        ];

        // Buttons
        $data['action'] = $this->url->link('extension/analytics/skroutz', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true);

        $data['user_token'] = $this->session->data['user_token'];

        // Fields
        $fields = [
            'code',
            'status',
            'widget_status',
            'widget_type',
            'replace_html',
            'replace_position',
        ];

        foreach ($fields as $field) {
            $field = "analytics_skroutz_{$field}";

            $data[$field] = $this->request->post[$field] ?? $this->model_setting_setting->getSettingValue($field, $this->request->get['store_id']);
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/analytics/skroutz', $data));
    }

    public function validate(): bool
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

    public function install(): void
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

    public function uninstall(): void
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