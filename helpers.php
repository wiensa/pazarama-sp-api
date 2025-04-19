<?php

if (! function_exists('pazarama_api')) {
    /**
     * Pazarama SP API örneğini döndürür
     *
     * @return \PazaramaApi\PazaramaSpApi\PazaramaSpApi
     */
    function pazarama_api()
    {
        return app('pazarama-sp-api');
    }
}

if (! function_exists('make')) {
    /**
     * @template TClass
     *
     * @param  class-string<TClass>  $abstract
     * @return TClass
     */
    function make(string $abstract, array $parameters = [])
    {
        return \Illuminate\Container\Container::getInstance()->make($abstract, $parameters);
    }
}
