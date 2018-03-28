<?php

class shopRussianpochtaPluginBatchDeleteDialogAction extends waViewAction {

    public function execute() {
        try {
            $batch_name = waRequest::get('batch_name', 0, waRequest::TYPE_INT);
            if (!$batch_name) {
                throw new waException('Не определен номер партии');
            }

            $russianpochta = new shopRussianpochta();
            $orders = $russianpochta->getOrdersInBatch($batch_name);
        } catch (Exception $e) {
            $error_msg = $ex->getMessage();
        }

        $this->view->assign(array(
            'orders' => ifset($orders),
            'error_msg' => ifset($error_msg),
        ));
    }

}
