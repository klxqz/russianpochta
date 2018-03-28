<?php

class shopRussianpochtaPluginSettingsConnectionTestController extends waJsonController {

    public function execute() {
        try {
            $russianpochta = new shopRussianpochta();
            $shipping_points = $russianpochta->getShippingPoints();
            if (!empty($shipping_points['code'])) {
                throw new waException(sprintf('Ошибка %s: %s', $shipping_points['code'], $shipping_points['desc']));
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
