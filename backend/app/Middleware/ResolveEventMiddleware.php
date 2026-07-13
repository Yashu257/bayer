<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use App\Repositories\EventRepository;
use Core\Session\Session;

/**
 * ResolveEventMiddleware — resolves {slug} from the URI and binds the Event
 * model to the request. Must run before any middleware that needs the event.
 */
class ResolveEventMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $slug = $request->getAttribute('slug');

        if (empty($slug)) {
            return Response::redirect('/404');
        }

        $event = (new EventRepository())->findBySlug((string) $slug);

        if ($event === null) {
            if ($request->isJson() || $request->isAjax()) {
                return Response::error('Event not found.', 404);
            }
            return Response::redirect('/404');
        }

        if (!$event->isOpen()) {
            if ($request->isJson() || $request->isAjax()) {
                return Response::error('Event is not available.', 403);
            }
            Session::flash('error', 'This event is not currently available.');
            return Response::redirect('/events');
        }

        // Bind for downstream controllers and middleware
        $request->setAttribute('event', $event);

        return $next($request);
    }
}
