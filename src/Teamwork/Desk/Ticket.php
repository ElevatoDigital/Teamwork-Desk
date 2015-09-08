<?php

namespace Teamwork\Desk;

class Ticket extends Thing
{
    public function init()
    {
        $this->defaults = [
            'inboxId'              => null,
            'source'               => 'API',
            'type'                 => 'Request',
            'customerEmail'        => null,
            'customerFirstName'    => '',
            'customerLastName'     => '',
            'customerPhoneNumber'  => '',
            'customerMobileNumber' => '',
            'subject'              => null,
            'message'              => null,
            'status'               => 'active',
            'assignedTo'           => '',
            'notifyCustomer'       => 'false',
        ];
    }

    public function adjustOptions($options)
    {
        $defaults = [
#            'assignedTo[]' => '76609',
            'sortBy'       => 'updatedAt',
            'sortDir'      => 'ASC',
            'pageSize'     => '100',
            'startRow'     => '1'
        ];

        return $this->handleDefaults($options, $defaults);
    }

    public function getAll(Inbox $inbox)
    {
        $this->setPath('/inboxes/' . $inbox->id . '/tickets/Active.json');

        return parent::getAll();
    }

    public function getMultipleUrl()
    {
        return $this->getPath();
    }

    protected function formatList($data) {
        $data = [$data['tickets']];

        if (0 === count($data)) {
            return [];
        }

        $data = array_pop($data);

        $class = get_class($this);
        $formattedData = [];
        foreach ((array)$data as $datum) {
            $datum['customer'] = new Customer($datum['customer']);
            $formattedData[] = new $class($datum);
        }

        return $formattedData;
    }

    public function create($data = [])
    {
        $this->setPath('/tickets.json');
        $this->data = $this->handleDefaults($data, $this->data);

        parent::create();
    }
}
