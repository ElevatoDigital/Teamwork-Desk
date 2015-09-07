<?php

namespace Teamwork\Desk;

class Setting extends Thing
{
    public function init()
    {
        $this->setPath('/settings.json');
    }
}
