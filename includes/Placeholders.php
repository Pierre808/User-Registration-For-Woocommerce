<?php

class Placeholders {
    private $placeholders;

    public function __construct() {
        $this->placeholders = [
            'VERIFICATIONLINK' => [
                'val' => 'verificationlink',
                'description' => 'The inidividual verificationlink'
            ]
        ];
    }

    public function getPlaceholders() {
        return $this->placeholders;
    }
}