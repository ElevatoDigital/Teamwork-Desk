<?php

namespace Teamwork\Desk;

class Thing extends Http\Request
{
    const VERSION  = 1;
    const PROTOCOL = 'https';

    protected $data;
    protected $defaults;
    protected static $authCookies;

    public function __construct($id = null)
    {
        if (is_array($id)) {
            $this->import($id);
        } elseif (null !== $id) {
            $this->data = [];
            $this->data['id'] = $id;
        }

        $this->init();
    }

    public function __get($name)
    {
        return isset($this->data[$name])
            ? $this->data[$name]
            : null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function import($data)
    {
        $this->data = $data;
    }

    public function init() {}

    public function send()
    {
        $this->setHeader('User-Agent', 'Delta Systems Desk API Wrapper');

        $this->getAuthentication();

        $this->scanUrl();

        return parent::send();
    }

    public function getBaseUrl()
    {
        if (!defined('TEAMWORK_DESK_DOMAIN')) {
            throw new \Exception('TEAMWORK_DESK_DOMAIN not specified');
        }

        return self::PROTOCOL . '://' . TEAMWORK_DESK_DOMAIN . '/desk/v'
            . self::VERSION;
    }

    public function getUrl()
    {
        return $this->getBaseUrl() . $this->getPath();
    }

    protected function getSimpleClassName()
    {
        $class = get_class($this);
        $classPieces = explode('\\', $class);

        $simpleClassName = array_pop($classPieces);
        $simpleClassName = strtolower($simpleClassName);

        $lastLetter = substr($simpleClassName, -1);
        if ('s' === $lastLetter || 'x' === $lastLetter
            || 'ch' === substr($simpleClassName, -2)) {
            $simpleClassName .= 'es';
        } elseif ('y' === $lastLetter) {
            $simpleClassName = substr(
                $simpleClassName,
                0,
                strlen($simpleClassName) - 1
            ) . 'ies';
        } else {
            $simpleClassName .= 's';
        }

        return $simpleClassName;
    }

    protected function getMultipleUrl()
    {
        return '/' . $this->getSimpleClassName() . '.json';
    }

    protected function getSingleUrl()
    {
        return '/' . $this->getSimpleClassName() . '/' . $this->data['id']
            . '.json';
    }

    public function getAuthentication()
    {
        $this->setHeader('Authorization', 'Basic ' . base64_encode(TEAMWORK_DESK_KEY . ':xxx'));
    }

    public function adjustOptions($options) {}

    public function getAll($options = [])
    {
        $options = $this->adjustOptions($options);

        if (count($options)) {
            $this->setPath(
                $this->getMultipleUrl() . '?'
                    . str_replace(
                        ['%5B', '%5D'],
                        ['[', ']'],
                        http_build_query($options)
                    )
            );
        } else {
            $this->setPath($this->getMultipleUrl());
        }

        $this->setVerb('GET');

        $response = $this->send();

        $rawData = $response->getData();
        $data    = json_decode($rawData, true);

        return $this->formatList($data);
    }

    public function get($id)
    {
        $this->setPath($this->getSingleUrl());
        $this->setVerb('GET');

        $response = $this->send();

        $rawData = $response->getData();
        $data    = json_decode($rawData, true);

        return $this->formatSingle($data);
    }

    public function getByName($name, $caseMatters = true)
    {
        $all = $this->getAll();

        foreach ($all as $one) {
            if ($caseMatters && $name === $one->name) {
                return $one;
            } elseif (!$caseMatters && strtolower($name) === strtolower($one->name)) {
                return $one;
            }
        }

        return false;
    }

    protected function formatList($data)
    {
        if (0 === count($data)) {
            return [];
        }

        $data = array_pop($data);

        $class = get_class($this);
        $formattedData = [];
        foreach ((array)$data as $datum) {
            $formattedData[] = new $class($datum);
        }

        return $formattedData;
    }

    protected function formatSingle($data)
    {
        if (0 === count($data)) {
            return null;
        }

        $data = array_pop($data);

        $class = get_class($this);
        $formattedData = new $class($data);

        return $formattedData;
    }

    public function create()
    {
        $data = $this->handleDefaults($this->data, $this->defaults);

        foreach ($this->data as $key => $datum) {
            if (null === $datum) {
                throw new \Exception($key . ' is required in '
                    . $this->getSimpleClassName());
            }
        }

        $this->setPath($this->getMultipleUrl());
        $this->setVerb('POST');
        $this->setData($data);
        $this->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

        $response = $this->send();

        $rawData = $response->getData();
        $data    = json_decode($rawData, true);

        return $data;
    }

    public function update()
    {
        $data = $this->handleDefaults($this->data, $this->defaults);

        foreach ($this->data as $key => $datum) {
            if (null === $datum) {
                throw new Exception($key . ' is required in '
                    . $this->getSimpleClassName());
            }
        }

        $this->setVerb('PUT');
        $this->setData($data);

        $response = $this->send();

        $rawData    = $response->getData();
        $this->data = json_decode($rawData, true);

        return $this;
    }

    protected function handleDefaults($data, $defaults)
    {
        foreach ($defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
