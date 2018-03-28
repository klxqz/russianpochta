<?php

class shopRussianpochtaPluginOrderShopOrdersDialogAction extends waViewAction {

    public function execute() {
        $batch_name = waRequest::get('batch_name');
        $query = waRequest::get('query');
        $collection = new shopOrdersCollection();
        if ($query) {
            $collection = new shopOrdersCollection('search/id=' . $query);
        } else {
            $collection = new shopOrdersCollection();
        }
        $collection->orderBy('create_datetime', 'desc');
        $offset = 0;
        $limit = 100;
        $orders = $collection->getOrders("*,items,contact,params", $offset, $limit);
        shopHelper::workupOrders($orders);
        $this->view->assign(array(
            'batch_name' => $batch_name,
            'query' => $query,
            'orders' => $orders,
        ));
    }

}
