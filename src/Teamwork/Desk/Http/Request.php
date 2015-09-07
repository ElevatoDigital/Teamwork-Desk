<?php

namespace Teamwork\Desk\Http;

class Request {
    private $verb;

    private $url;
    private $protocol;
    private $hostname;
    private $port;
    private $path;

    private $headers;
    private $cookies;

    private $data;

    public function __construct(
        $url = null,
        $headers = null,
        $data = null,
        $verb = 'GET'
    ) {
        if ($url !== null) {
            $this->setUrl($url);
        }

        $this->setHeaders(array());
        if ($headers !== null) {
            foreach ($headers as $key => $value) {
                $this->setHeader($key, $value);
            }
        }
        $this->setHeader('User-Agent', 'Http_Request');
        $this->setHeader('Connection', 'close');

        if ($data !== null) {
            $this->setData($data);
        } else {
            $this->setData('');
        }

        if ($verb !== null) {
            $this->setVerb($verb);
        }
    }

    protected function scanUrl() {
        $url = $this->getUrl();

        $pos = strpos($url,'://');
        if ($pos === false) {
            $this->setProtocol('http');
        } else {
            $this->setProtocol(strtolower(substr($url, 0, $pos)));
            $url = substr($url, $pos + 3);
        }

        $pos = strpos($url, '/');
        if ($pos === false) {
            $host = $url;
            $this->setPath('/');
        } else {
            $host = substr($url, 0, $pos);

            $path = substr($url, $pos);
            if ($path === '') {
                $path = '/';
            }

            $this->setPath($path);
        }

        if(strpos($host, ':') !== false) {
            list($host, $port) = explode(':',$host);

            $this->setHostname($host);
            $this->setPort($port);
        } else {
            $this->setHostname($host);
            $this->setPort(($this->getProtocol() == 'https') ? 443 : 80);
        }
    }

    public function send() {
        $request = '';

        $request .= $this->getVerb() . ' ' .
                    $this->getPath() . ' ' .
                    "HTTP/1.0\r\n";

        $request .= 'Host: ' . $this->getHostname() . "\r\n";

        foreach ($this->getHeaders() as $key => $value) {
            $request .= $key . ': ' . $value . "\r\n";
        }

        $data = $this->getData();
        if (strlen($data) == 0) {
            $request .= "\r\n";
        } else {
            $request .= 'Content-Length: ' . strlen($data) . "\r\n" .
                        "\r\n" .
                        $data;
        }

        $socketHost = ($this->getProtocol() == 'https' ? 'ssl://' : '') .
            $this->getHostname();
        $fsocket = fsockopen($socketHost, $this->getPort());

        fwrite($fsocket, $request);

        $response = stream_get_contents($fsocket);

        fclose($fsocket);

        return new Response($response);
    }

    public function getVerb() {
        return $this->verb;
    }

    public function setVerb($verb) {
        $this->verb = $verb;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
        $this->scanUrl();
    }

    public function getProtocol() {
        return $this->protocol;
    }

    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    public function getHostname() {
        return $this->hostname;
    }

    public function setHostname($hostname) {
        $this->hostname = $hostname;
    }

    public function getPort() {
        return $this->port;
    }

    public function setPort($port) {
        $this->port = $port;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader($searchKey) {
        foreach ($this->headers as $key => $value) {
            if ($key == $searchKey) {
                return $value;
            }
        }
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
    }


    public function getCookies($formatted = false) {
        if ($formatted) {
            $output = '';

            foreach ($this->cookies as $key => $value) {
                if (strlen($output) != 0) {
                    $output .= '; ';
                }
                
                $output .= $key . '=' . $value;
            }

            return $output;
        } else {
            return $this->cookies;
        }
    }

    public function getCookie($searchKey) {
        foreach ($this->cookies as $key => $value) {
            if ($key == $searchKey) {
                return $value;
            }
        }
    }

    public function setCookies($cookies) {
        $this->cookies = $cookies;

        $this->setHeader('Cookie', $this->getCookies(true));
    }

    public function setCookie($key, $value) {
        $this->cookies[$key] = $value;

        $this->setHeader('Cookie', $this->getCookies(true));
    }


    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        if (is_array($data)) {
            $data = http_build_query($data);
        }

        $this->data = $data;
    }
}
