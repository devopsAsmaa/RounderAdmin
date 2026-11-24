<?php

namespace App\Http\Middleware;

use App\Modules\Identity\Models\User;
use Closure;
use Illuminate\Http\Request;

/** طبقة التحقق من أن المستخدم الحالي هو فني معتمد. */
class EnsureTechnician
{
    /**
     * التحقق من صفة الفني قبل استكمال معالجة الطلب.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        abort_unless($user instanceof User && (bool) $user->is_technician, 403, __('Access denied. Technicians only.'));

        return $next($request);
    }
}
