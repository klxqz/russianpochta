<?php

class shopRussianpochtaPluginBatchCreateController extends waJsonController {

    public function execute() {
        try {
            $order_id = waRequest::post('order_id', 0, waRequest::TYPE_INT);
            if (!$order_id) {
                throw new waException('Не определен номер заказа');
            }
            $russianpochta = new shopRussianpochta();
            $russianpochta->сreateBatch(array($order_id));
            
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
