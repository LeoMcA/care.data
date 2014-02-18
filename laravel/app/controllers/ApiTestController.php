<?php

class ApiTestController extends BaseController {

    public function showTest() {
        return View::make('api');
    }
}
