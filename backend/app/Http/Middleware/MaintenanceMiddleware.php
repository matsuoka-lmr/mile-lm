<?php

namespace App\Http\Middleware;

use Closure;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MaintenanceMiddleware
{
    private function conevrt_addr_mask(string $ip, int $subnet) {
        $addr = inet_pton($ip);
        $len = 8 * strlen($addr);
        $mask = str_repeat('f', $subnet >> 2);
        switch ($subnet & 3) {
            case 1:
                $mask .= '8';
                break;
            case 2:
                $mask .= 'c';
                break;
            case 3:
                $mask .= 'e';
                break;
            default:
                break;
        }
        $mask = pack('H*', str_pad($mask, $len >> 2, '0'));
        $filt = $addr & $mask;
        return $filt;
    }

    private function isAdminIP(string $ip) {
        $addr_ranges = env('APP_ADMIN_IPS', '');
        foreach (explode(',', $addr_ranges) as $addr_range) {
            $ret_addr = explode("/", $addr_range);
            $chk_mask = $this->conevrt_addr_mask($ret_addr[0], (int)$ret_addr[1]);
            $ip_mask  = $this->conevrt_addr_mask($ip, (int)$ret_addr[1]);
            if ($chk_mask === $ip_mask) {
                return true;
            }
        }
        return false;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $mode = 0;
        try {
            if (Storage::exists('maintenance.txt')) $mode = intval(Storage::get('maintenance.txt'));
            else Storage::put('maintenance.txt', '0');
        } catch(Exception $e) {
            Storage::put('maintenance.txt', '0');
        }
        
        if ($mode > 0 && !$this->isAdminIP($request->ip())) {
            return response()->json(['cause' => $mode], 503);
        }
        return $next($request);
    }
}
