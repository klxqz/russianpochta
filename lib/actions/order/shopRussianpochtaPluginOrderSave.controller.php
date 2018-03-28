<?php

class shopRussianpochtaPluginOrderSaveController extends waJsonController {

    public function execute() {
        try {
            $batch_name = waRequest::post('batch_name', 0, waRequest::TYPE_INT);
            $order_id = waRequest::post('order_id', 0, waRequest::TYPE_INT);
            if (!$order_id) {
                throw new waException('Не определен номер заказа');
            }
            $data = waRequest::post('russianpochta', array());
            $this->prepare($data);

            $russianpochta = new shopRussianpochta();
            if (!empty($batch_name)) {
                $result = $russianpochta->addOrdersToBatch($batch_name, $data);
                if (!empty($result['error'])) {
                    throw new waException('Ошибка : ' . $result['error']);
                }
                if (!empty($result['errors'])) {
                    throw new waException('Ошибка : ' . $result['errors'][0]['error-codes'][0]['description']);
                }
            } elseif (!empty($data['id'])) {
                $result = $russianpochta->editOrder($data['id'], $data);
                if (!empty($result['error-codes'])) {
                    throw new waException('Ошибка : ' . $result['error-codes'][0]['description']);
                }
            } else {
                $result = $russianpochta->createOrder($data);
                if (!empty($result['error'])) {
                    throw new waException('Ошибка : ' . $result['error']);
                }
                if (!empty($result['errors'])) {
                    throw new waException('Ошибка : ' . $result['errors'][0]['error-codes'][0]['description']);
                }
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

    private function prepare(&$data) {
        $data['insr-value'] = (int) floatval($data['insr-value']) * 100;
        $data['payment'] = (int) floatval($data['payment']) * 100;
    }

}
