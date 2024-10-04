<?php

namespace Atom\Core\Http\Controllers;

use App\Events\UserLogin;
use Atom\Core\Http\Requests\LoginRequest;
use Atom\Core\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Atom\Core\Models\WebsiteArticle;


class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $articles = WebsiteArticle::latest('id')
        ->take(3)
        ->has('user')
        ->with('user:id,username,look')
        ->get();

        return view('login', [
        'articles' => $articles
        ]);
        return view('login');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = User::firstWhere('username', $request->get('username'));

        if (! $user || ! Hash::check($request->get('password'), $user->password)) {
            return redirect()
                ->route('login', ['error' => 1])
                ->withInput()
                ->withErrors(['username' => 'The provided credentials are incorrect.']);
        }

        if (app()->isDownForMaintenance() && $user->rank < 4) {
            return redirect()
                ->route('login', ['error' => 1])
                ->withInput();
        }

        $secret = @file_get_contents(storage_path('framework/down'));

        $user->update(['last_login' => time(), 'ip_current' => $request->ip()]);

        UserLogin::dispatch($user);

        Auth::login($user);

        return app()->isDownForMaintenance()
            ? redirect(url(json_decode($secret)->secret))
            : redirect()->route('users.me');
    }
}
