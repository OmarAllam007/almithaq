<?php

namespace App\Http\Middleware;

use App\Models\UserAcquisition;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    public const SESSION_KEY = 'acquisition';

    /**
     * First-touch attribution: the first campaign URL of a session wins and is
     * never overwritten. Only the known UTM keys are kept.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->session()->has(self::SESSION_KEY)) {
            $utm = $this->sanitize($request);

            if ($utm !== []) {
                $request->session()->put(self::SESSION_KEY, $utm);
            }
        }

        return $next($request);
    }

    /**
     * @return array<string, string>
     */
    protected function sanitize(Request $request): array
    {
        $utm = [];

        foreach (UserAcquisition::PARAMETERS as $parameter) {
            $value = $request->query($parameter);

            if (! is_string($value)) {
                continue;
            }

            $value = trim(preg_replace('/[^\pL\pN _.\-|]/u', '', $value) ?? '');

            if ($value !== '') {
                $utm[$parameter] = mb_substr($value, 0, 100);
            }
        }

        return $utm;
    }
}
