<?php

namespace Aroon\EgyptianNationalId;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Aroon\EgyptianNationalId\Rules\NationalIdRule;

class EgyptianNationalIdServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Validator::extend('national_id', static function ($attribute, $value, $parameters, $validator) {
            $rule = new NationalIdRule();
            return $rule->passes($attribute, $value);
        }, 'The :attribute is not a valid Egyptian National ID.');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }
}
