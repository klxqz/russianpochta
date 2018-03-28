<?php

class shopRussianpochtaPluginBatchSetSendingDateController extends waJsonController {

    public function execute() {
        try {
            $batch_name = waRequest::post('batch_name', 0, waRequest::TYPE_INT);
            if (!$batch_name) {
                throw new waException('Не определен номер заказа');
            }
            $date = waRequest::post('date');
            $date_int = strtotime($date);

            $russianpochta = new shopRussianpochta();
            $result = $russianpochta->setBatchSendingDate($batch_name, date('Y', $date_int), date('m', $date_int), date('d', $date_int));
            if (!empty($result['error-code'])) {
                switch ($result['error-code']) {
                    case 'PAST_DUE_DATE':
                        throw new waException('Дата отправки просрочена');
                        break;
                    default:
                        throw new waException($result['error-code']);
                        break;
                }
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
