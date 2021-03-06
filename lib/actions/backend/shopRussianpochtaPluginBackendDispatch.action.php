<?php

class shopRussianpochtaPluginBackendDispatchAction extends waViewAction {

    public function execute() {
        try {
            $russianpochta = new shopRussianpochta();
            $batches = $russianpochta->getBatches();
        } catch (Exception $ex) {
            $error_msg = $ex->getMessage();
        }
        $this->view->assign(array(
            'batches' => ifset($batches, array()),
            'error_msg' => ifset($error_msg),
        ));
    }

}
