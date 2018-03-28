<?php

return array(
    'name' => 'Почта России',
    'description' => 'Взаимодействие c онлайн-сервисом «Отправка» Почты России',
    'vendor' => 985310,
    'version' => '1.0.0',
    'img' => 'img/russianpochta.png',
    'shop_settings' => true,
    'frontend' => true,
    'handlers' => array(
        'backend_order' => 'backendOrder',
        'backend_menu' => 'backendMenu',
        'rights.config' => 'rightsConfig',
    ),
);
//EOF
