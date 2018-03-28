<?php

class shopRussianpochta {

    private $russianpochta_url = 'https://otpravka-api.pochta.ru/';
    private $application_authentication_token;
    private $user_authentication_key;
    private $logging;
    private $log_file = 'russianpochta.log';

    public function __construct() {
        $plugin = wa()->getPlugin('russianpochta');
        if (!$plugin->getSettings('access_token')) {
            throw new waException('Укажите «Токен авторизации» в настройках плагина');
        }
        if (!$plugin->getSettings('access_login')) {
            throw new waException('Укажите «Логин» в настройках плагина');
        }
        if (!$plugin->getSettings('access_password')) {
            throw new waException('Укажите «Пароль» в настройках плагина');
        }
        $this->application_authentication_token = $plugin->getSettings('access_token');
        $this->user_authentication_key = base64_encode($plugin->getSettings('access_login') . ':' . $plugin->getSettings('access_password'));
        $this->logging = $plugin->getSettings('logging');
    }

    /*
     * https://otpravka.pochta.ru/specification#/orders-creating_order
     * Создание заказа
     * Создает новый заказ. Автоматически рассчитывает и проставляет плату за пересылку.
     */

    public function createOrder($data) {
        $new_orders = array(
            array(
                "address-type-to" => ifset($data["address-type-to"], "DEFAULT"),
                "area-to" => ifset($data["area-to"], ""),
                "brand-name" => ifset($data["brand-name"], ""),
                "building-to" => ifset($data["building-to"], ""),
                "comment" => ifset($data["comment"], ""),
                "corpus-to" => ifset($data["corpus-to"], ""),
                "dimension" => array(
                    "height" => (int) ifset($data['dimension']["height"], 0),
                    "length" => (int) ifset($data['dimension']["length"], 0),
                    "width" => (int) ifset($data['dimension']["width"], 0)
                ),
                "fragile" => (bool) ifset($data["fragile"], false),
                "given-name" => ifset($data["given-name"], ""),
                "hotel-to" => ifset($data["hotel-to"], ""),
                "house-to" => ifset($data["house-to"], ""),
                "index-to" => (int) ifset($data["index-to"], 0),
                "insr-value" => (int) ifset($data["insr-value"], 0),
                "letter-to" => ifset($data["letter-to"], ""),
                "location-to" => ifset($data["location-to"], ""),
                "mail-category" => ifset($data["mail-category"], "ORDINARY"),
                "mail-direct" => (int) ifset($data["mail-direct"], 0),
                "mail-type" => ifset($data["mail-type"], "ONLINE_PARCEL"),
                "mass" => (int) ifset($data["mass"], 0),
                "middle-name" => ifset($data["middle-name"], ""),
                "num-address-type-to" => ifset($data["num-address-type-to"], ""),
                "order-num" => ifset($data["order-num"], ""),
                "payment" => (int) ifset($data["payment"], 0),
                "place-to" => ifset($data["place-to"], ""),
                "postoffice-code" => ifset($data["postoffice-code"], ""),
                "rcp-pays-shipping" => (bool) ifset($data["rcp-pays-shipping"], false),
                "region-to" => ifset($data["region-to"], ""),
                "room-to" => ifset($data["room-to"], ""),
                "slash-to" => ifset($data["slash-to"], ""),
                "sms-notice-recipient" => (int) ifset($data["sms-notice-recipient"], 0),
                "street-to" => ifset($data["street-to"], ""),
                "surname" => ifset($data["surname"], ""),
                "tel-address" => ifset($data["tel-address"], 0),
            )
        );
        $this->log('Создание заказа');
        return $this->sendRequest("/1.0/user/backlog", 'PUT', $new_orders);
    }

    /*
     * https://otpravka.pochta.ru/specification#/orders-search_order
     * Поиск заказа
     * Ищет заказы по назначенному магазином идентификатору
     */

    public function searchOrder($query) {
        $this->log('Поиск заказа');
        return $this->sendRequest("/1.0/backlog/search?query=$query");
    }

    /*
     * https://otpravka.pochta.ru/specification#/orders-search_order_byid
     * Поиск заказа по идентификатору
     */

    public function getOrder($id) {
        $this->log('Поиск заказа по идентификатору');
        return $this->sendRequest("/1.0/backlog/$id");
    }

    /*
     * https://otpravka.pochta.ru/specification#/orders-editing_order
     * Редактирование заказа
     */

    public function editOrder($id, $data) {
        $edit_order = array(
            "address-type-to" => ifset($data["address-type-to"], "DEFAULT"),
            "area-to" => ifset($data["area-to"], ""),
            "brand-name" => ifset($data["brand-name"], ""),
            "building-to" => ifset($data["building-to"], ""),
            "corpus-to" => ifset($data["corpus-to"], ""),
            "dimension" => array(
                "height" => (int) ifset($data['dimension']["height"], 0),
                "length" => (int) ifset($data['dimension']["length"], 0),
                "width" => (int) ifset($data['dimension']["width"], 0),
            ),
            "fragile" => (bool) ifset($data["fragile"], false),
            "given-name" => ifset($data["given-name"], ""),
            "hotel-to" => ifset($data["hotel-to"], ""),
            "house-to" => ifset($data["house-to"], ""),
            "index-to" => (int) ifset($data["index-to"], 0),
            "insr-value" => (int) ifset($data["insr-value"], 0),
            "letter-to" => ifset($data["letter-to"], ""),
            "location-to" => ifset($data["location-to"], ""),
            "mail-category" => ifset($data["mail-category"], "ORDINARY"),
            "mail-type" => ifset($data["mail-type"], "ONLINE_PARCEL"),
            "mass" => (int) ifset($data["mass"], 0),
            "middle-name" => ifset($data["middle-name"], ""),
            "num-address-type-to" => ifset($data["num-address-type-to"], ""),
            "order-num" => ifset($data["order-num"], ""),
            "payment" => (int) ifset($data["payment"], 0),
            "place-to" => ifset($data["place-to"], ""),
            "postoffice-code" => ifset($data["postoffice-code"], ""),
            "rcp-pays-shipping" => (bool) ifset($data["rcp-pays-shipping"], false),
            "region-to" => ifset($data["region-to"], ""),
            "room-to" => ifset($data["room-to"], ""),
            "slash-to" => ifset($data["slash-to"], ""),
            "street-to" => ifset($data["street-to"], ""),
            "surname" => ifset($data["surname"], ""),
            "tel-address" => ifset($data["tel-address"], 0),
        );
        $this->log('Редактирование заказа');
        return $this->sendRequest("/1.0/backlog/$id", 'PUT', $edit_order);
    }

    /*
     * https://otpravka.pochta.ru/specification#/orders-delete_new_order
     * Удаление заказа
     */

    public function deleteOrders(array $order_ids) {
        $this->log('Удаление заказа');
        return $this->sendRequest("/1.0/backlog/", 'DELETE', $order_ids);
    }

    /*
     * https://otpravka.pochta.ru/specification#/orders-shipment_to_backlog
     * Возврат заказов в "Новые"
     */

    public function revertOrderFromBatch(array $order_ids) {
        $this->log('Возврат заказов в "Новые"');
        return $this->sendRequest("/1.0/user/backlog", 'POST', $order_ids);
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-create_batch_from_N_orders
     * Создание партии из N заказов
     */

    public function сreateBatch(array $order_ids) {
        $this->log('Создание партии');
        return $this->sendRequest("/1.0/user/shipment", 'POST', $order_ids);
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-sending_date
     * Изменение дня отправки в почтовое отделение
     * Изменяет (устанавливает) новый день отправки в почтовое отделение.
     */

    public function setBatchSendingDate($batch_name, $year, $month, $day) {
        $this->log('Изменение дня отправки в почтовое отделение');
        return $this->sendRequest("/1.0/batch/$batch_name/sending/$year/$month/$day", 'POST');
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-move_orders_to_batch
     * Перенос заказов в партию
     * Переносит подготовленные заказы в указанную партию
     */

    public function moveOrdersToBatch($batch_name, array $order_ids) {
        $this->log('Перенос заказов в партию');
        return $this->sendRequest("/1.0/batch/$batch_name/shipment", 'POST', $order_ids);
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-find_batch
     * Поиск партии по наименованию
     */

    public function getBatch($batch_name) {
        $this->log('Поиск партии по наименованию');
        return $this->sendRequest("/1.0/batch/$batch_name");
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-find_orders_with_barcode
     * Поиск заказов с ШПИ
     */

    public function searchBatch($query) {
        $this->log('Поиск заказов с ШПИ');
        return $this->sendRequest("/1.0/shipment/search?query=$query");
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-add_orders_to_batch
     * Добавление заказов в партию
     */

    public function addOrdersToBatch($batch_name, $data) {
        $new_orders = array(
            array(
                "address-type-to" => ifset($data["address-type-to"], "DEFAULT"),
                "area-to" => ifset($data["area-to"], ""),
                "brand-name" => ifset($data["brand-name"], ""),
                "building-to" => ifset($data["building-to"], ""),
                "comment" => ifset($data["comment"], ""),
                "corpus-to" => ifset($data["corpus-to"], ""),
                "dimension" => array(
                    "height" => (int) ifset($data['dimension']["height"], 0),
                    "length" => (int) ifset($data['dimension']["length"], 0),
                    "width" => (int) ifset($data['dimension']["width"], 0)
                ),
                "fragile" => (bool) ifset($data["fragile"], false),
                "given-name" => ifset($data["given-name"], ""),
                "hotel-to" => ifset($data["hotel-to"], ""),
                "house-to" => ifset($data["house-to"], ""),
                "index-to" => (int) ifset($data["index-to"], 0),
                "insr-value" => (int) ifset($data["insr-value"], 0),
                "letter-to" => ifset($data["letter-to"], ""),
                "location-to" => ifset($data["location-to"], ""),
                "mail-category" => ifset($data["mail-category"], "ORDINARY"),
                "mail-direct" => (int) ifset($data["mail-direct"], 0),
                "mail-type" => ifset($data["mail-type"], "ONLINE_PARCEL"),
                "mass" => (int) ifset($data["mass"], 0),
                "middle-name" => ifset($data["middle-name"], ""),
                "num-address-type-to" => ifset($data["num-address-type-to"], ""),
                "order-num" => ifset($data["order-num"], ""),
                "payment" => (int) ifset($data["payment"], 0),
                "place-to" => ifset($data["place-to"], ""),
                "postoffice-code" => ifset($data["postoffice-code"], ""),
                "rcp-pays-shipping" => (bool) ifset($data["rcp-pays-shipping"], false),
                "region-to" => ifset($data["region-to"], ""),
                "room-to" => ifset($data["room-to"], ""),
                "slash-to" => ifset($data["slash-to"], ""),
                "sms-notice-recipient" => (int) ifset($data["sms-notice-recipient"], 0),
                "street-to" => ifset($data["street-to"], ""),
                "surname" => ifset($data["surname"], ""),
                "tel-address" => ifset($data["tel-address"], 0),
            )
        );
        $this->log('Добавление заказов в партию');
        return $this->sendRequest("/1.0/batch/$batch_name/shipment", 'PUT', $new_orders);
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-delete_order_from_batch
     * Удаление заказов из партии
     */

    public function deleteOrdersFromBatch(array $order_ids) {
        $this->log('Удаление заказов из партии');
        return $this->sendRequest("/1.0/shipment", 'DELETE', $order_ids);
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-get_info_about_orders_in_batch
     * Запрос данных о заказах в партии
     */

    public function getOrdersInBatch($batch_name) {
        $this->log('Запрос данных о заказах в партии');
        return $this->sendRequest("/1.0/batch/$batch_name/shipment");
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-search_all_batches
     * Поиск всех партий
     */

    public function getBatches($mailType = null, $size = null, $page = null) {
        $params = array();
        if ($mailType) {
            $params[] = "mailType=$mailType";
        }
        if ($size) {
            $params[] = "size=$size";
        }
        if ($page) {
            $params[] = "page=$page";
        }
        $url = "/1.0/batch" . ($params ? '?' . implode('&', $params) : '');
        $this->log('Поиск всех партий');
        return $this->sendRequest($url);
    }

    /*
     * https://otpravka.pochta.ru/specification#/batches-find_order_by_id
     * Поиск заказа в партии по внутреннему id
     */

    public function getOrderInBatch($id) {
        $this->log('Поиск заказа в партии по внутреннему id');
        return $this->sendRequest("/1.0/shipment/$id");
    }

    /*
     * https://otpravka.pochta.ru/specification#/documents-create_all_docs
     * Генерация пакета документации
     * Генерирует и возвращает zip архив с 4-мя файлами: 
     * Export.xls , Export.csv - список с основными данными по заявкам в составе партии
     * F103.pdf - форма ф103 по заявкам в составе партии
     * В зависимости от типа и категории отправлений, формируется комбинация из сопроводительных документов в формате pdf ( формы: f7, f112, f22)
     */

    public function getAllDocs($batch_name) {
        $this->log('Генерация пакета документации');
        $data = $this->sendRequest("/1.0/forms/$batch_name/zip-all", null, null, false);
        $filename = wa()->getTempPath('plugins/russianpochta/download/' . time() . '.zip');
        $f = @fopen($filename, 'wb');
        if (!$f) {
            $this->log('Ошибка создания файла для записи: ' . $filename);
            throw new waException('Ошибка создания файла для записи: ' . $filename);
        }
        fwrite($f, $data);
        fclose($f);
        return $filename;
    }

    /*
     * https://otpravka.pochta.ru/specification#/documents-create_f7_f22
     * Генерация печатной формы Ф7п
     */

    public function getDocF7($id) {
        $this->log('Генерация печатной формы Ф7п');
        return $this->sendRequest("/1.0/forms/$id/f7pdf");
    }

    /*
     * https://otpravka.pochta.ru/specification#/documents-create_f112
     * Генерация печатной формы Ф112ЭК
     */

    public function getDocF112($id) {
        $this->log('Генерация печатной формы Ф112ЭК');
        return $this->sendRequest("/1.0/forms/$id/f112pdf");
    }

    /*
     * https://otpravka.pochta.ru/specification#/documents-create_forms
     * Генерация печатных форм для заказа
     */

    public function getDocForms($id, $sending_date = null) {
        $url = "/1.0/forms/$id/forms";
        if ($sending_date) {
            $url .= "?sending-date=$sending_date";
        }
        $this->log('Генерация печатных форм для заказа');
        $data = $this->sendRequest($url, null, null, false);
        $filename = wa()->getTempPath('plugins/russianpochta/download/' . time() . '.pdf');
        $f = @fopen($filename, 'wb');
        if (!$f) {
            $this->log('Ошибка создания файла для записи: ' . $filename);
            throw new waException('Ошибка создания файла для записи: ' . $filename);
        }
        fwrite($f, $data);
        fclose($f);
        return $filename;
    }

    /*
     * https://otpravka.pochta.ru/specification#/documents-create_f103
     * Генерация печатной формы Ф103
     */

    public function getDocF103($batch_name) {
        $this->log('Генерация печатной формы Ф103');
        return $this->sendRequest("/1.0/forms/$batch_name/f103pdf");
    }

    /*
     * https://otpravka.pochta.ru/specification#/documents-checkin
     * Подготовка и отправка электронной формы Ф103
     */

    public function prepareSendFormF103($batch_name, $sendEmail) {
        if ($sendEmail) {
            $sendEmail = 'true';
        } else {
            $sendEmail = 'false';
        }
        $this->log('Подготовка и отправка электронной формы Ф103');
        return $this->sendRequest("/1.0/batch/$batch_name/checkin?sendEmail=$sendEmail", 'POST');
    }

    /*
     * https://otpravka.pochta.ru/specification#/archive-search_batches
     * Запрос данных о партиях в архиве
     */

    public function getBatchesInArchive() {
        $this->log('Запрос данных о партиях в архиве');
        return $this->sendRequest("/1.0/archive");
    }

    /*
     * https://otpravka.pochta.ru/specification#/archive-batch_to_archive
     * Перевод партии в архив
     */

    public function moveBatchesToArchive($batches_names) {
        $this->log('Перевод партии в архив');
        return $this->sendRequest("/1.0/archive", 'PUT', $batches_names);
    }

    /*
     * https://otpravka.pochta.ru/specification#/archive-revert_batch
     * Возврат партии из архива
     */

    public function revertBatchFromArchive($batches_names) {
        $this->log('Возврат партии из архива');
        return $this->sendRequest("/1.0/archive/revert", 'POST', $batches_names);
    }

    /*
     * https://otpravka.pochta.ru/specification#/settings-shipping_points
     * Текущие точки сдачи
     */

    public function getShippingPoints() {
        $this->log('Текущие точки сдачи');
        return $this->sendRequest('/1.0/user-shipping-points');
    }

    /*
     * https://otpravka.pochta.ru/specification#/settings-user_settings
     * Текущие настройки пользователя
     */

    public function getUserSettings() {
        $this->log('Текущие настройки пользователя');
        return $this->sendRequest('/1.0/settings');
    }

    /*
     * https://otpravka.pochta.ru/specification#/nogroup-normalization_adress
     * Нормализация адреса
     */

    public function normalizationAdress($data) {
        $this->log('Нормализация адреса');
        return $this->sendRequest('/1.0/clean/address', 'POST', $data);
    }

    /*
     * https://otpravka.pochta.ru/specification#/nogroup-normalization_fio
     * Нормализация ФИО
     */

    public function normalizationFio($data) {
        $this->log('Нормализация ФИО');
        return $this->sendRequest('/1.0/clean/physical', 'POST', $data);
    }

    /*
     * https://otpravka.pochta.ru/specification#/nogroup-normalization_phone
     * Нормализация телефона
     */

    public function normalizationPhone($data) {
        $this->log('Нормализация телефона');
        return $this->sendRequest('/1.0/clean/phone', 'POST', $data);
    }

    /*
     * https://otpravka.pochta.ru/specification#/nogroup-rate_calculate
     * Рассчет стоимости пересылки
     */

    public function rateCalculate($data) {
        $this->log('Рассчет стоимости пересылки');
        return $this->sendRequest('/1.0/tariff', 'POST', $data);
    }

    private function sendRequest($url, $type = null, $request = null, $json_decode = true) {
        if (!$type) {
            $type = 'GET';
        }
        $cache_id = md5('shopRussianpochta::sendRequest' . $url . $type . serialize($request) . $json_decode);
        $cache_time = wa()->getConfig()->isDebug() ? 0 : 7200;
        $cache = new waSerializeCache($cache_id, $cache_time, 'shop/plugins/russianpochta');
        if ($cache && $cache->isCached() && 0) {

            $result = $cache->get();
            $log_text = "Результат получен из кеша\r\nОтвет на запрос:\r\n";
            $log_text .= var_export($result, true) . "\r\n";
            $this->log($log_text);
            return $result;
        } else {

            if (!extension_loaded('curl') || !function_exists('curl_init')) {
                $this->log('PHP расширение cURL не доступно');
                throw new waException('PHP расширение cURL не доступно');
            }
            if (!($ch = curl_init())) {
                $this->log('curl init error');
                throw new waException('curl init error');
            }
            if (curl_errno($ch) != 0) {
                $this->log('Ошибка инициализации curl: ' . curl_errno($ch));
                throw new waException('Ошибка инициализации curl: ' . curl_errno($ch));
            }



            $url = rtrim($this->russianpochta_url, '/') . '/' . ltrim($url, '/');

            $headers = array(
                "Content-Type: application/json;charset=UTF-8",
                "Authorization: AccessToken " . $this->application_authentication_token,
                "X-User-Authorization: Basic " . $this->user_authentication_key,
            );

            $log_text = "Параметры запроса:\r\n";
            $log_text .= "\tURL: $url\r\n";
            $log_text .= "\tТип: $type\r\n";
            $log_text .= "Заголовки запроса:\r\n";
            $log_text .= var_export($headers, true) . "\r\n";
            $log_text .= "Тело запроса:\r\n";
            $log_text .= var_export($request, true) . "\r\n";
            $this->log($log_text);

            @curl_setopt($ch, CURLOPT_URL, $url);
            @curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //@curl_setopt($ch, CURLOPT_SSLVERSION, 3);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));

            $response = @curl_exec($ch);

            $app_error = null;
            if (curl_errno($ch) != 0) {
                $app_error = 'Ошибка curl: ' . curl_error($ch);
            }
            $info = curl_getinfo($ch);
            $log_text = "Информация выполнения запроса:\r\n";
            $log_text .= var_export($info, true) . "\r\n";
            $this->log($log_text);

            curl_close($ch);

            if ($app_error) {
                $this->log($app_error);
                throw new waException($app_error);
            }
            if (empty($response)) {
                $this->log('Пустой ответ от сервера');
                throw new waException('Пустой ответ от сервера');
            }

            if ($json_decode) {
                $json = json_decode($response, true);
                if (!is_array($json)) {
                    $this->log('Ошибка декодирования объекта JSON: ' . $response);
                    throw new waException('Ошибка декодирования объекта JSON: ' . $response);
                }

                $log_text = "Ответ на запрос:\r\n";
                $log_text .= var_export($json, true) . "\r\n";
                $this->log($log_text);

                if ($cache) {
                    $cache->set($json);
                }
                return $json;
            } else {
                $log_text = "Ответ на запрос:\r\n";
                $log_text .= var_export($response, true) . "\r\n";
                $this->log($log_text);

                if ($cache) {
                    $cache->set($response);
                }

                return $response;
            }
        }
    }

    private function log($data) {
        if ($this->logging) {
            waLog::log($data, $this->log_file);
        }
    }

}
