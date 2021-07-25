<?php

if (! function_exists('cdn_asset')) {

    /**
     * Generate a cdn asset path.
     *
     * @param string $path
     *
     * @return string
     */
    function cdn_asset($path)
    {
      if (is_null($path)) return '';

      $base = config('app.cdn') ?: config('app.url');
      $file = ltrim($path, '/');

      return "{$base}/{$file}";
    }
}

if (! function_exists('front_url')) {

  /**
   * Generate a front path.
   *
   * @param string $path
   *
   * @return string
   */
  function front_url($path)
  {
    if (is_null($path)) return '';

    $base = config('app.front') ?: config('app.url');
    $file = ltrim($path, '/');

    return "{$base}/{$file}";
  }
}
if (! function_exists('plural')) {
    function plural($endings, $number)
    {
        $cases = [2, 0, 1, 1, 1, 2];
        $n = $number;
        return sprintf($endings[ ($n % 100 > 4 && $n % 100 < 20) ? 2 : $cases[min($n % 10, 5)] ], $n);
    }
}
