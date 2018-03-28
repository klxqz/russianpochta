<?php

class shopRussianpochtaPluginOrderDialogAction extends waViewAction {

    public function execute() {
        try {
            $batch_name = waRequest::get('batch_name', 0, waRequest::TYPE_INT);
            $order_num = waRequest::get('order_num', 0, waRequest::TYPE_INT);
            $order_id = waRequest::get('order_id', 0, waRequest::TYPE_INT);

            if (!$order_num && !$order_id) {
                throw new waException('Не определен номер заказа');
            }

            $country_model = new waCountryModel();
            $countries = $country_model->all();

            $russianpochta = new shopRussianpochta();
            $shipping_points = $russianpochta->getShippingPoints();

            $currency = waCurrency::getInfo('RUB');

            if ($order_num) {
                $order_model = new shopOrderModel();
                $order = $order_model->getOrder($order_num);
                $this->prepareOrder($order);
                $contact = new waContact($order['contact']['id']);

                $region_model = new waRegionModel();
                $regions = $region_model->getByCountry($order['params']['shipping_address.country']);

                $found_orders = $russianpochta->searchOrder($order['id']);
                if ($found_orders) {
                    $russianpochta_order = reset($found_orders);
                }

                $this->view->assign(array(
                    'batch_name' => $batch_name,
                    'order' => $order,
                    'contact' => $contact,
                    'countries' => $countries,
                    'regions' => $regions,
                    'settings' => wa()->getPlugin('russianpochta')->getSettings(),
                    'shipping_points' => ifset($shipping_points, array()),
                    'russianpochta_order' => ifset($russianpochta_order, null),
                    'currency' => $currency,
                ));
            } elseif ($order_id) {
                $russianpochta_order = $russianpochta->getOrderInBatch($order_id);

                $this->view->assign(array(
                    'batch_name' => $batch_name,
                    'countries' => $countries,
                    'shipping_points' => ifset($shipping_points, array()),
                    'russianpochta_order' => ifset($russianpochta_order, null),
                    'currency' => $currency,
                ));
            }
        } catch (Exception $ex) {
            $error_msg = $ex->getMessage();
            $this->view->assign('error_msg', $error_msg);
        }
    }

    private function prepareOrder(&$order) {
        $region_model = new waRegionModel();
        if ($region = $region_model->getByField('code', $order['params']['shipping_address.region'])) {
            $order['params']['shipping_address.region_name'] = $region['name'];
        }
        $country_model = new waCountryModel();
        if ($country = $country_model->getByField('iso3letter', $order['params']['shipping_address.country'])) {
            $order['params']['shipping_address.country_code'] = $country['isonumeric'];
        }
    }

}
