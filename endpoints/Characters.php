<?php

class Characters extends Api {

    public function get() {

        /*
            $char = \models\Character::model_from_db(self::data[0]);
            $character_name = $char -> name;
        */

        // Example response using the API class.
        parent::response(
            array(
                'request_received_time' => now(),
            ),
            array(
                'info' => 'This is a GET request for the Characters endpoint.';
            )
        );
    }
}