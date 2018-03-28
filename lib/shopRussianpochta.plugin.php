<?php

class shopRussianpochtaPlugin extends shopPlugin {

    public function backendOrder($order) {
        if (!$this->getSettings('status') && !wa('shop')->getUser()->getRights('shop', 'russianpochtaplugin')) {
            return;
        }
        $view = wa()->getView();
        $view->assign('order', $order);
        $html = $view->fetch($this->path . '/templates/handlers/BackendOrder.html');
        return array('action_link' => $html);
    }

    public function backendMenu() {
        if (!$this->getSettings('status') && !wa('shop')->getUser()->getRights('shop', 'russianpochtaplugin')) {
            return;
        }
        $html = '<li ' . (waRequest::get('plugin') == $this->id ? 'class="selected"' : 'class="no-tab"') . '>
                        <a href="?plugin=russianpochta#/">Почта России</a>
                    </li>';
        return array('core_li' => $html);
    }

    public function rightsConfig(waRightConfig $right_config) {
        if (!$this->getSettings('status')) {
            return;
        }
        $right_config->addItem('russianpochtaplugin', 'Плагин "Почта России"');
    }

}
