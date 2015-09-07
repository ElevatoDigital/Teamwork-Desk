<?php

namespace Teamwork\Desk;

class Inbox extends Thing
{
    public function init()
    {

    }

    public function getTickets()
    {
        $ticketClass = new Ticket();

        $tickets = $ticketClass->getAll($this);

        return $tickets;
    }
}
