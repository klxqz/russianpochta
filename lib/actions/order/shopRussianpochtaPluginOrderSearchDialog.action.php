<?php

class shopRussianpochtaPluginOrderSearchDialogAction extends waViewAction {

    public function execute() {
        try {
            $batch_name = waRequest::get('batch_name', 0, waRequest::TYPE_INT);
            if (!$batch_name) {
                throw new waException('Не определен номер партии');
            }
            $query = waRequest::get('query');
            if ($query) {
                $russianpochta = new shopRussianpochta();
                $orders = $russianpochta->searchOrder($query);
            }
        } catch (Exception $ex) {
            $error_msg = $ex->getMessage();
        }
        $this->view->assign(array(
            'batch_name' => $batch_name,
            'query' => $query,
            'orders' => ifset($orders, array()),
            'error_msg' => ifset($error_msg),
        ));
    }

}
