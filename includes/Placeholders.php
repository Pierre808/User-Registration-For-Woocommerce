<?php

class Placeholders {
    private $placeholders;

    public function __construct() {
        $this->placeholders = [
            'VERIFICATIONLINK' => [
                'val' => 'verificationlink',
                'description' => 'The inidividual verification-link'
            ]
        ];
    }

    public function getPlaceholders() {
        return $this->placeholders;
    }
}