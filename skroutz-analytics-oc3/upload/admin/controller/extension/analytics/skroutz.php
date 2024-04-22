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
 * @property ModelSettingSetting $model_setting_setting
 * @property ModelUserUserGroup $model_user_user_group
 * @property ModelSettingEvent $model_setting_event
 * @property Language $language
 * @property Document $document
 * @property Url $url
 * @property Response $response
 * @property User $user
 */
class ControllerExtensionAnalyticsSkroutz extends Controller
{
    private const ERRORS = [
        'warning',
        'code',
    ];

    private const FIELDS = [
        'code',
        'status',
        'widget_status',
        'widget_type',
        'replace_html',
        'replace_position',
    ];

    private const EVENTS = [
        'catalog/controller/checkout/success/before' => 'extension/analytics/skroutz/controllerCheckoutSuccessBefore',
        'catalog/view/common/success/after'          => 'extension/analytics/skroutz/viewCommonSuccessAfter',
        'catalog/view/product/product/after'         => 'extension/analytics/skroutz/viewProductProductAfter',
    ];

    private $error = [];

    public function index(): void
    {
        $store_id = (int)($this->request->get['store_id'] ?? 0);

        $this->load->language('extension/analytics/skroutz');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('analytics_skroutz', $this->request->post, $store_id);

            $data['success'] = $this->language->get('text_success');
        }

        // Errors
        foreach (self::ERRORS as $error) {
            $data['error_' . $error] = $this->error[$error] ?? '';
        }

        // User Token
        $data['user_token'] = $this->session->data['user_token'];

        // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $data['user_token'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $cancel = $this->url->link('marketplace/extension', 'user_token=' . $data['user_token'] . '&type=analytics', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $action = $this->url->link('extension/analytics/skroutz', 'user_token=' . $data['user_token'] . '&store_id=' . $store_id, true),
            ],
        ];

        // Buttons
        $data['cancel'] = $cancel;
        $data['action'] = $action;

        // Fields
        foreach (self::FIELDS as $field) {
            $id = "analytics_skroutz_{$field}";

            $data[$field] = $this->request->post[$id] ?? $this->model_setting_setting->getSettingValue($id, $store_id);
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
        // Permissions
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/analytics/skroutz');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/analytics/skroutz');

        // Event
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('analytics_skroutz');

        foreach (self::EVENTS as $trigger => $action) {
            $this->model_setting_event->addEvent('analytics_skroutz', $trigger, $action);
        }
    }

    public function uninstall(): void
    {
        // Permissions
        $this->load->model('user/user_group');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/analytics/skroutz');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/analytics/skroutz');

        // Events
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('analytics_skroutz');
    }
}