<?php

namespace Teamwork\Desk;

class User extends Thing
{
    public function init()
    {
        $this->setPath('/users.json');
    }
}
