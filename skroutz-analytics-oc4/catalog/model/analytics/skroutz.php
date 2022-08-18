<?php

/**
 * Shopflix Analytics
 * @author Prionysis
 * @link https://github.com/Prionysis
 * @version 1.2
 */


namespace Opencart\Catalog\Model\Extension\SkroutzAnalytics\Analytics;

use Opencart\System\Engine\Model;

class Skroutz extends Model
{
	public function getOrder(int $order_id): array
	{
		$query = "SELECT o.order_id, o.payment_code, o.payment_method,
        	MAX(CASE WHEN (ot.code = 'tax') THEN value END) AS tax,
        	MAX(CASE WHEN (ot.code = 'shipping') THEN value END) AS shipping,
        	MAX(CASE WHEN (ot.code = 'total') THEN value END) AS revenue
        FROM " . DB_PREFIX . "order o
            LEFT JOIN " . DB_PREFIX . "order_total ot ON ot.order_id = o.order_id
        WHERE o.order_id = '" . $this->db->escape($order_id) . "'";

		return $this->db->query($query)->row;
	}

	public function getOrderProducts(int $order_id): array
	{
		$query = "SELECT op.product_id, op.quantity, 
        	QUOTE(IF(LENGTH(GROUP_CONCAT(DISTINCT oo.value SEPARATOR ', ')) > 0, CONCAT(op.name, ' - ', GROUP_CONCAT(DISTINCT oo.value SEPARATOR ', ')), op.name)) AS name, 
        	IF(" . $this->config->get('config_tax') . " = 1, op.price + op.tax, op.price) AS price
        FROM " . DB_PREFIX . "order_product op
        	LEFT JOIN " . DB_PREFIX . "order_option oo ON op.order_product_id = oo.order_product_id
        WHERE op.order_id = '" . $this->db->escape($order_id) . "' GROUP BY op.product_id";

		return $this->db->query($query)->rows;
	}
}