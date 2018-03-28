<?php

class shopRussianpochtaPluginBatchRevertController extends waJsonController {

    public function execute() {
        try {
            $batch_name = waRequest::post('batch_name', 0, waRequest::TYPE_INT);
            if (!$batch_name) {
                throw new waException('Не определен номер заказа');
            }
            $russianpochta = new shopRussianpochta();
            $russianpochta->revertBatchFromArchive(array($batch_name));
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
