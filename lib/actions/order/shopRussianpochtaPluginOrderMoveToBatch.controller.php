<?php

class shopRussianpochtaPluginOrderMoveToBatchController extends waJsonController {

    public function execute() {
        try {
            $order_id = waRequest::post('order_id', 0, waRequest::TYPE_INT);
            $batch_name = waRequest::post('batch_name', 0, waRequest::TYPE_INT);
            if (!$order_id) {
                throw new waException('Не определен номер заказа');
            }
            if (!$batch_name) {
                throw new waException('Не определен номер партии');
            }

            $russianpochta = new shopRussianpochta();
            $result = $russianpochta->moveOrdersToBatch($batch_name, array($order_id));
            if (!empty($result['error'])) {
                throw new waException('Ошибка: ' . $result['message']);
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
