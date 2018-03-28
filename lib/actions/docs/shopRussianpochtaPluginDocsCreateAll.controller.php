<?php

class shopRussianpochtaPluginDocsCreateAllController extends waController {

    public function execute() {
        $batch_name = waRequest::get('batch_name');
        $russianpochta = new shopRussianpochta();
        $filename = $russianpochta->getAllDocs($batch_name);
        waFiles::readFile($filename, 'doc_' . $batch_name . '.zip');
    }

}
