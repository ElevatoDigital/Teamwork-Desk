<?php

namespace Teamwork\Desk\Http;

class Response {
    private $response;
    private $responseCode;
    private $responseMessage;

    private $headers;

    private $data;

    public function __construct($input) {
        $lines = explode("\r\n", $input);

        $firstLine = array_shift($lines);
        $this->setResponse($firstLine);

        $firstLineParts = explode(' ', $firstLine, 3);
        $this->setResponseCode($firstLineParts[1]);
        $this->setResponseMessage($firstLineParts[2]);

        do {
            $line = array_shift($lines);

            if (strlen($line) == 0) break;

            $headerPieces = explode(': ', $line, 2);

            $this->setHeader($headerPieces[0], $headerPieces[1]);
        } while (strlen($line) != 0);

        $this->setData(implode("\n", $lines));
    }

    public function getResponse() {
        return $this->response;
    }

    public function setResponse($response) {
        $this->response = $response;
    }

    public function getResponseCode() {
        return $this->responseCode;
    }

    public function setResponseCode($responseCode) {
        $this->responseCode = $responseCode;
    }

    public function getResponseMessage() {
        return $this->responseMessage;
    }

    public function setResponseMessage($responseMessage) {
        $this->responseMessage = $responseMessage;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader($searchKey) {
        foreach ((array)$this->getHeaders() as $key => $value) {
            if ($key == $searchKey) {
                return $value;
            }
        }
        
        return null;
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    public function setHeader($key, $value) {
        if ($key == 'Set-Cookie') {
            if (!isset($this->headers[$key])) {
                $this->headers[$key] = array();
            }

            $this->headers[$key][] = $value;
        } else {
            $this->headers[$key] = $value;
        }
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function createRequest() {
        $http = new Request();
       
        $cookies = $this->getHeader('Set-Cookie');
        
        foreach ((array)$cookies as $cookie) {
            $cookie = explode(';', $cookie);
            $cookie = $cookie[0];

            $cookieParts = explode('=', $cookie, 2);

            $http->setCookie($cookieParts[0], $cookieParts[1]);
        }

        return $http;
    }
}
