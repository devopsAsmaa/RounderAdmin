<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\App;
/** طبقة مشتركة لتطبيق إعدادات وصلاحيات الطلبات عبر المجالات. */
class SetLocale {
 /**
 * تطبيق سياسة مشتركة قبل استكمال دورة التطبيق.
 * @param Request $request
 * @param Closure $next
 * @return mixed
 * @throws \Throwable
 */
 public function handle(Request $request, Closure $next) {
  $actor = $request->user('provider') ?? $request->user('web') ?? $request->user();
  $userLocale = $actor?->locale instanceof \BackedEnum ? $actor->locale->value : $actor?->locale;
  $locale = $request->session()->get('locale') ?? $userLocale ?? config('app.locale', 'ar');
  App::setLocale(in_array($locale, ['ar', 'en'], true) ? $locale : 'ar');
  return $next($request);
 }
}
