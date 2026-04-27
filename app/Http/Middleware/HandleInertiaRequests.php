<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'notifications' => $request->user() ? [
                    'list' => $request->user()->notifications()->take(10)->get()->map(function($n) {
                        return [
                            'id' => $n->id,
                            'data' => $n->data,
                            'read_at' => $n->read_at,
                            'created_at_label' => $n->created_at->diffForHumans(),
                        ];
                    }),
                    'unread_count' => $request->user()->unreadNotifications()->count(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}
