<?php

class shopRussianpochtaPluginSettingsAction extends waViewAction {

    public function execute() {
        try {
            $russianpochta = new shopRussianpochta();
            $shipping_points = $russianpochta->getShippingPoints();
            
            $user_settings = $russianpochta->getUserSettings();
        } catch (Exception $ex) {
            
        }

        $this->view->assign(array(
            'plugin' => wa()->getPlugin('russianpochta'),
            'shipping_points' => ifset($shipping_points, array()),
            'user_settings' => ifset($user_settings),
        ));
    }

}
