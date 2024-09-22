<?php 


namespace Socialsquared\Office365mailer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Socialsquared\Office365mailer\Transport\Office365Transport;


class Office365ServiceProvider extends ServiceProvider 
{
    public function boot(): void 
    {
        Mail::extend('office365', function ($app) {
            return new Office365Transport();
        });
    }
}