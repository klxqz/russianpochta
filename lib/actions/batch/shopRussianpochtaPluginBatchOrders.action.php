<?php

class shopRussianpochtaPluginBatchOrdersAction extends waViewAction {

    public function execute() {
        $action_name = waRequest::get('action_name');
        $batch_name = waRequest::get('batch_name', 0, waRequest::TYPE_INT);
        try {
            $russianpochta = new shopRussianpochta();
            $orders = $russianpochta->getOrdersInBatch($batch_name);
        } catch (Exception $e) {
            $error_msg = $ex->getMessage();
        }
        $this->view->assign(array(
            'orders' => ifset($orders, array()),
            'action_name' => $action_name,
            'error_msg' => ifset($error_msg),
        ));
    }

}
