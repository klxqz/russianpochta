<?php

class shopRussianpochtaPluginBackendOrdersAction extends waViewAction {

    public function execute() {
        try {
            $query = waRequest::get('query');
            if ($query) {
                $russianpochta = new shopRussianpochta();
                $orders = $russianpochta->searchOrder($query);
            }
        } catch (Exception $ex) {
            $error_msg = $ex->getMessage();
        }
        $this->view->assign(array(
            'query' => $query,
            'orders' => ifset($orders, array()),
            'error_msg' => ifset($error_msg),
        ));
    }

}
