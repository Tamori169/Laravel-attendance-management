<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\RedirectIfWrongLoginType;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ログインリクエストを実装 //
        $this->app->bind(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            \App\Http\Requests\LoginRequest::class
        );

        // 会員登録後のリダイレクト先をメール認証誘導画面に変更
        $this->app->instance(
            \Laravel\Fortify\Http\Responses\RegisterResponse::class,
            new class implements \Laravel\Fortify\Contracts\RegisterResponse {
                public function toResponse($request)
                {
                    return redirect('/email/verify');
                }
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.staff.register');
        });

        Fortify::loginView(function () {
            return view('auth.staff.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.staff.verify-email');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // ユーザー権限に応じてログイン後の遷移先を指定
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $user = $request->user();

                    if ($user->role->name === 'admin') {
                        return redirect('/admin/attendance/list');
                    }

                    return redirect('/attendance');
                }
            };
        });

        // ユーザー権限に応じてログアウト後の遷移先を指定
        $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    if ($request->input('login_type') === 'admin') {
                        return redirect('/admin/login');
                    }

                    return redirect('/login');
                }
            };
        });

        Fortify::authenticateThrough(function ($request) {
            return array_filter([
                config('fortify.limiters.login')
                    ? null
                    : EnsureLoginIsNotThrottled::class,

                AttemptToAuthenticate::class,

                RedirectIfWrongLoginType::class,

                PrepareAuthenticatedSession::class,
            ]);
        });
    }
}
