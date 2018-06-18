<?php

namespace Distilleries\Contentful\Http\Middleware;

use Closure;

class UsePreview
{
    /**
     * Run the preview switch context.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        use_contentful_preview();

        return $next($request);
    }
}
