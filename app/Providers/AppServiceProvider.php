<?php

namespace App\Providers;

use App\Models\CorporateQuote;
use App\Models\IndividualQuote;
use App\Observers\CorporateQuoteObserver;
use App\Observers\IndividualQuoteObserver;
use Illuminate\Support\ServiceProvider;

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
        IndividualQuote::observe(IndividualQuoteObserver::class);
        CorporateQuote::observe(CorporateQuoteObserver::class);
    }
}
