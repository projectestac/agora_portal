<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('menu.clientmenu', static function ($view) {
            $user = Auth::user();

            if ($user) {
                $clients = Client::whereIn('id', static function ($query) use ($user) {
                    $query->select('client_id')
                        ->from('managers')
                        ->where('user_id', $user->id);
                })->get();

                $currentClient = session('currentClient');

                $view->with(compact('clients', 'currentClient'));
            }
        });
    }
}
