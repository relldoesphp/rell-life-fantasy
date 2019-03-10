<?php

namespace Player\Model;

class Player
{
    public $id;
    public $firstName;
    public $lastName;

    public function exchangeArray(array $data)
    {
        $this->id     = !empty($data['id']) ? $data['id'] : null;
        $this->firstName = !empty($data['firstName']) ? $data['firstName'] : null;
        $this->lastName  = !empty($data['lastName']) ? $data['lastName'] : null;
    }
}
