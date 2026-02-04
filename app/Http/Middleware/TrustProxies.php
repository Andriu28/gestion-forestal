<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TrustProxies extends Middleware
{
    /**
     * Los proxies que deben ser confiados.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*'; // ✅ Confía en todos los proxies (necesario para Railway)

    /**
     * Los headers que deben ser usados para detectar proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Para Railway, necesitamos confiar en todos los proxies
        $request->setTrustedProxies(
            ['127.0.0.1', $request->server->get('REMOTE_ADDR')], 
            $this->getTrustedHeaderNames()
        );

        return parent::handle($request, $next);
    }

    /**
     * Get the trusted headers for the request.
     *
     * @return int
     */
    protected function getTrustedHeaderNames()
    {
        return $this->headers;
    }
}