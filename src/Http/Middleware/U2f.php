<?php

namespace Certly\U2f\Http\Middleware;

use Auth;
use Certly\U2f\Models\U2fKey;
use Certly\U2f\U2f as LaravelU2f;
use Closure;
use Illuminate\Config\Repository as Config;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class U2f
 */
class U2f
{
    /**
     * @var LaravelU2f
     */
    protected $u2f;

    /**
     * @var Config
     */
    protected $config;
    public function __construct(LaravelU2f $u2f, Config $config)
    {
        $this->u2f = $u2f;
        $this->config = $config;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->config->get('u2f.enable')) {
            return $next($request);
        }
        if (!$this->u2f->check()) {
            if (Auth::guest()) {
                throw new HttpException(401, 'You need to log in before an u2f authentication');
            }
            if (U2fKey::where('user_id', '=', $request->user()->id)->count() === 0 && $this->config->get('u2f.byPassUserWithoutKey')) {
                return $next($request);
            }
            Auth::logout();
            abort(403, 'Unauthorized U2F action.');
        }
        return $next($request);
    }
}