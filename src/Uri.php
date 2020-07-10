<?php

declare(strict_types=1);

namespace Arslanoov\Psr7;

use Arslanoov\Psr7\Exception\InvalidArgumentException;
use Arslanoov\Psr7\Exception\UnableToParseUriException;
use Arslanoov\Psr7\Filter\UriFilter;
use Arslanoov\Psr7\Builder\UriBuilder;
use Psr\Http\Message\UriInterface;
use function parse_url;
use function is_string;

final class Uri implements UriInterface
{
    private const PARTS_PATTERN = [
        'user' => '',
        'scheme' => '',
        'host' => '',
        'port' => null,
        'path' => '',
        'query' => '',
        'fragment' => '',
        'pass' => null
    ];

    private UriFilter $filter;
    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        $this->filter = new UriFilter();
        if ('' !== $uri) {
            $parts = parse_url($uri);
            if (false === $parts) {
                throw new UnableToParseUriException($uri);
            }

            $parts = $parts + self::PARTS_PATTERN;

            $this->userInfo = $parts['user'];
            $this->scheme = mb_strtolower($parts['scheme']);
            $this->host = mb_strtolower($parts['host']);
            $this->port = $this->filter->filterPort($this->scheme, $parts['port']);
            $this->path = $this->filter->filterPath($parts['path']);
            $this->query = $this->filter->filterQuery($parts['query']);
            $this->fragment = $this->filter->filterFragment($parts['fragment']);

            if (isset($parts['pass'])) {
                $this->userInfo .= ':' . $parts['pass'];
            }
        }
    }

    public function __toString(): string
    {
        return
            (new UriBuilder())
            ->withScheme($this->scheme)
            ->withAuthority($this->getAuthority())
            ->withPath($this->getAuthority(), $this->path)
            ->withQuery($this->query)
            ->withFragment($this->fragment)
            ->getUri()
        ;
    }

    // Get

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ('' === $this->host) {
            return '';
        }

        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if (null !== $this->port) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    // With

    public function withScheme($scheme): self
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string');
        }

        $scheme = mb_strtolower($scheme);

        $uri = clone $this;
        $uri->scheme = $scheme;
        $uri->port = $this->filter->filterPort($uri->scheme, $uri->port);

        return $uri;
    }

    public function withUserInfo($user, $password = null): self
    {
        $info = $user;
        if (null !== $password and '' !== $password) {
            $info .= ':' . $password;
        }

        $new = clone $this;
        $new->userInfo = $info;

        return $new;
    }

    public function withHost($host): self
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException('Host must be a string.');
        }

        $host = mb_strtolower($host);

        $uri = clone $this;
        $uri->host = $host;

        return $uri;
    }

    public function withPort($port): self
    {
        $port = $this->filter->filterPort($this->scheme, $port ? (int) $port : null);
        $uri = clone $this;
        $uri->port = $port;

        return $uri;
    }

    public function withPath($path): self
    {
        $path = $this->filter->filterPath($path);

        $uri = clone $this;
        $uri->path = $path;

        return $uri;
    }

    public function withQuery($query): self
    {
        $query = $this->filter->filterQuery($query);

        $uri = clone $this;
        $uri->query = $query;

        return $uri;
    }

    public function withFragment($fragment): self
    {
        $fragment = $this->filter->filterFragment($fragment);

        $uri = clone $this;
        $uri->fragment = $fragment;

        return $uri;
    }
}