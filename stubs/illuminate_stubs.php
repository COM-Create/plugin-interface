<?php

/**
 * Illuminate Facade Stubs for PHPStan analysis of PlentyONE plugins.
 * These classes are provided by Laravel (which Plenty uses internally)
 * but are not part of the official plugin-interface.
 */

namespace Illuminate\Support\Facades {

    class Cache
    {
        /**
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function get(string $key, $default = null) { return $default; }

        /**
         * @param string $key
         * @param mixed $value
         * @param \DateTimeInterface|\DateInterval|int|null $ttl
         * @return bool
         */
        public static function put(string $key, $value, $ttl = null): bool { return true; }

        /**
         * @param string $key
         * @return bool
         */
        public static function forget(string $key): bool { return true; }

        /**
         * @param string $key
         * @return bool
         */
        public static function has(string $key): bool { return false; }

        /**
         * @param string $key
         * @param \DateTimeInterface|\DateInterval|int|null $ttl
         * @param callable $callback
         * @return mixed
         */
        public static function remember(string $key, $ttl, callable $callback) { return null; }

        /** @return bool */
        public static function flush(): bool { return true; }
    }

    class Http
    {
        /**
         * @param array<string, string> $headers
         * @return \Illuminate\Http\Client\PendingRequest
         */
        public static function withHeaders(array $headers): \Illuminate\Http\Client\PendingRequest
        {
            return new \Illuminate\Http\Client\PendingRequest();
        }

        /**
         * @param string $url
         * @param array<string, mixed> $query
         * @return \Illuminate\Http\Client\Response
         * @throws \Illuminate\Http\Client\ConnectionException
         */
        public static function get(string $url, array $query = []): \Illuminate\Http\Client\Response
        {
            return new \Illuminate\Http\Client\Response();
        }

        /**
         * @param string $url
         * @param array<string, mixed> $data
         * @return \Illuminate\Http\Client\Response
         * @throws \Illuminate\Http\Client\ConnectionException
         */
        public static function post(string $url, array $data = []): \Illuminate\Http\Client\Response
        {
            return new \Illuminate\Http\Client\Response();
        }

        /**
         * @return \Illuminate\Http\Client\PendingRequest
         */
        public static function withoutVerifying(): \Illuminate\Http\Client\PendingRequest
        {
            return new \Illuminate\Http\Client\PendingRequest();
        }

        /**
         * @return \Illuminate\Http\Client\PendingRequest
         */
        public static function asJson(): \Illuminate\Http\Client\PendingRequest
        {
            return new \Illuminate\Http\Client\PendingRequest();
        }

        /**
         * @param int $seconds
         * @return \Illuminate\Http\Client\PendingRequest
         */
        public static function timeout(int $seconds): \Illuminate\Http\Client\PendingRequest
        {
            return new \Illuminate\Http\Client\PendingRequest();
        }
    }
}

namespace Illuminate\Http\Client {

    /**
     * Thrown when a connection to the remote server cannot be established.
     */
    class ConnectionException extends \RuntimeException {}

    /**
     * Thrown when Response::throw() or PendingRequest::throw() is called
     * and the response indicates an HTTP error (4xx/5xx).
     */
    class RequestException extends \RuntimeException
    {
        /** @var Response */
        public $response;

        public function __construct(Response $response)
        {
            $this->response = $response;
            parent::__construct('HTTP request returned status code ' . $response->status());
        }
    }

    class PendingRequest
    {
        /**
         * @param array<string, string> $headers
         * @return self
         */
        public function withHeaders(array $headers): self { return $this; }

        /** @return self */
        public function withoutVerifying(): self { return $this; }

        /** @return self */
        public function asJson(): self { return $this; }

        /**
         * @return self
         * @throws RequestException
         */
        public function throw(): self { throw new RequestException(new Response()); }

        /**
         * @param int $seconds
         * @return self
         */
        public function timeout(int $seconds): self { return $this; }

        /**
         * @param string $url
         * @param array<string, mixed> $query
         * @return Response
         * @throws ConnectionException
         */
        public function get(string $url, array $query = []): Response { return new Response(); }

        /**
         * @param string $url
         * @param array<string, mixed> $data
         * @return Response
         * @throws ConnectionException
         */
        public function post(string $url, array $data = []): Response { return new Response(); }

        /**
         * @param string $url
         * @param array<string, mixed> $data
         * @return Response
         * @throws ConnectionException
         */
        public function put(string $url, array $data = []): Response { return new Response(); }

        /**
         * @param string $url
         * @param array<string, mixed> $data
         * @return Response
         * @throws ConnectionException
         */
        public function patch(string $url, array $data = []): Response { return new Response(); }

        /**
         * @param string $url
         * @param array<string, mixed> $data
         * @return Response
         * @throws ConnectionException
         */
        public function delete(string $url, array $data = []): Response { return new Response(); }
    }

    class Response
    {
        /** @return string */
        public function body(): string { return ''; }

        /**
         * @param string|null $key
         * @param mixed $default
         * @return mixed
         */
        public function json(?string $key = null, $default = null) { return null; }

        /** @return int */
        public function status(): int { return 200; }

        /** @return bool */
        public function ok(): bool { return true; }

        /** @return bool */
        public function successful(): bool { return true; }

        /** @return bool */
        public function failed(): bool { return false; }

        /** @return bool */
        public function serverError(): bool { return false; }

        /** @return bool */
        public function clientError(): bool { return false; }

        /**
         * @param string $header
         * @return string
         */
        public function header(string $header): string { return ''; }

        /**
         * @return array<string, array<int, string>>
         */
        public function headers(): array { return []; }

        /**
         * @return self
         * @throws RequestException
         */
        public function throw(): self { throw new RequestException($this); }

        /**
         * @return array<mixed>
         */
        public function collect(): array { return []; }

        /** @return object|null */
        public function object(): ?object { return null; }
    }
}

namespace Illuminate\Routing {
    class Router {}
}
