<?php

class shopRussianpochtaPluginBatchMoveToArchiveController extends waJsonController {

    public function execute() {
        try {
            $batch_name = waRequest::post('batch_name', 0, waRequest::TYPE_INT);
            $russianpochta = new shopRussianpochta();
            $russianpochta->moveBatchesToArchive(array($batch_name));
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
