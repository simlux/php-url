<?php declare(strict_types=1);

namespace Simlux\Url;

use Simlux\String\StringBuffer;
use Simlux\Url\Exceptions\InvalidUrlException;

class Url
{
    const PROTOCOL_HTTP  = 'http';
    const PROTOCOL_HTTPS = 'https';

    const COMPONENT_PROTOCOL = 'scheme';
    const COMPONENT_HOST     = 'host';
    const COMPONENT_PORT     = 'port';
    const COMPONENT_USER     = 'user';
    const COMPONENT_PASS     = 'pass';
    const COMPONENT_PATH     = 'path';
    const COMPONENT_QUERY    = 'query';
    const COMPONENT_FRAGMENT = 'fragment';

    /** @var string */
    private $url;

    /** @var string */
    private $protocol;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $pass;

    /** @var string */
    private $path;

    /** @var string */
    private $query;

    /** @var string */
    private $fragment;

    /** @var array */
    private $params = [];


    /**
     * Url constructor.
     *
     * @param string|null $url
     *
     * @throws InvalidUrlException
     */
    public function __construct(string $url = null)
    {
        if (!is_null($url)) {
            $this->url = $this->sanitizeUrl($url);
            try {
                $this->parseUrl($this->url);
            } catch (InvalidUrlException $e) {
                throw $e;
            }
        }
    }

    /**
     * @param string|null $url
     *
     * @return Url
     */
    public static function create(string $url = null): Url
    {
        return new Url($url);
    }

    /**
     * @param string $url
     *
     * @throws InvalidUrlException
     */
    private function parseUrl(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException('Invalid url: ' . $url);
        }

        $components     = parse_url($url);
        $this->protocol = $this->getComponent(self::COMPONENT_PROTOCOL, $components);
        $this->host     = $this->getComponent(self::COMPONENT_HOST, $components);
        $this->port     = $this->getComponent(self::COMPONENT_PORT, $components);
        $this->user     = $this->getComponent(self::COMPONENT_USER, $components);
        $this->pass     = $this->getComponent(self::COMPONENT_PASS, $components);
        $this->path     = $this->getComponent(self::COMPONENT_PATH, $components);
        $this->query    = $this->getComponent(self::COMPONENT_QUERY, $components);
        $this->fragment = $this->getComponent(self::COMPONENT_FRAGMENT, $components);
        if ($this->query) {
            parse_str($this->query, $this->params);
        }
    }

    /**
     * @param string $component
     * @param array  $components
     *
     * @return mixed
     */
    private function getComponent(string $component, array $components)
    {
        if (isset($components[ $component ])) {
            return $components[ $component ];
        }
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    private function rebuild()
    {
        $this->rebuildQuery();
        $this->rebuildUrl();
    }

    private function rebuildUrl()
    {
        $buffer = StringBuffer::create($this->protocol)
            ->append('://')
            ->appendIf(!is_null($this->user) && !is_null($this->pass), sprintf('%s:%s@', $this->user, $this->pass))
            ->append($this->host)
            ->appendIf(!is_null($this->path), $this->path)
            ->appendIf(!is_null($this->query), '?' . $this->query)
            ->appendIf(!is_null($this->fragment), '#' . $this->fragment);

        $this->url = $buffer->toString();
    }

    private function rebuildQuery()
    {
        $this->query = http_build_query($this->params);
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasParam(string $key): bool
    {
        return isset($this->params[ $key ]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getParam(string $key): mixed
    {
        if ($this->hasParam($key)) {
            return $this->params[ $key ];
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function param(string $key, $value): Url
    {
        $this->params[ $key ] = $value;

        $this->rebuild();

        return $this;
    }

}