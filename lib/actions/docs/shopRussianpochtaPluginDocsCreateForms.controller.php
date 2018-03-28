<?php

class shopRussianpochtaPluginDocsCreateFormsController extends waController {

    public function execute() {
        $id = waRequest::get('id');
        $russianpochta = new shopRussianpochta();
        $filename = $russianpochta->getDocForms($id);
        waFiles::readFile($filename, 'doc_' . $id . '.pdf');
    }

}
