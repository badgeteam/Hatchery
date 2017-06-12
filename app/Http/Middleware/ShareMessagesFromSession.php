<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\MessageBag;

class ShareMessagesFromSession
{
    /**
     * The view factory implementation.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param  \Illuminate\Contracts\View\Factory  $view
     */
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->view->share(
            'successes', $request->session()->has('successes') ? new MessageBag($request->session()->get('successes')) : new MessageBag
        );

        $this->view->share(
            'info', $request->session()->has('info') ? new MessageBag($request->session()->get('info')) : new MessageBag
        );

        $this->view->share(
            'warnings', $request->session()->has('warnings') ? new MessageBag($request->session()->get('warnings')) : new MessageBag
        );

        return $next($request);
    }
}
