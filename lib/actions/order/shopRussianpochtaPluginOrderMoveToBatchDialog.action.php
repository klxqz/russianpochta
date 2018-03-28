<?php

class shopRussianpochtaPluginOrderMoveToBatchDialogAction extends waViewAction {

    public function execute() {
        try {
            $order_id = waRequest::get('order_id', 0, waRequest::TYPE_INT);
            if (!$order_id) {
                throw new waException('Не определен номер заказа');
            }
            $russianpochta = new shopRussianpochta();
            $batches = $russianpochta->getBatches();
        } catch (Exception $ex) {
            $error_msg = $ex->getMessage();
        }
        $this->view->assign(array(
            'order_id' => ifset($order_id),
            'batches' => ifset($batches, array()),
            'error_msg' => ifset($error_msg),
        ));
    }

}
