<?php

namespace App\Providers;
use App\Models\Company;
use App\Policies\CompanyPolicy;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
         Company::class => CompanyPolicy::class,
        \App\Models\Company::class => \App\Policies\CompanyPolicy::class, // ✅ policy شما اینجا ثبت می‌شود
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
