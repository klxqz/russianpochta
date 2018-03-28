<?php

class shopRussianpochtaPluginBatchDeleteOrdersController extends waJsonController {

    public function execute() {
        try {
            $order_ids = waRequest::post('order_ids', null, waRequest::TYPE_ARRAY_INT);
            if (!$order_ids) {
                throw new waException('Выбирете заказы для удаления');
            }
            $russianpochta = new shopRussianpochta();
            $russianpochta->deleteOrdersFromBatch($order_ids);
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
