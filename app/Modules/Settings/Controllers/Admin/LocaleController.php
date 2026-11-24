<?php
namespace App\Modules\Settings\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\UpdateLocaleRequest;
use App\Modules\Settings\Services\LocaleService;
/** يدير جلسة اللغة ويفوض حفظ الحساب للخدمة. */
class LocaleController extends Controller
{
    /**
     * @param LocaleService $service
     * @return void
     */
    public function __construct(private readonly LocaleService $service) {}
    /**
     * @param UpdateLocaleRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function update(UpdateLocaleRequest $request): \Illuminate\Http\RedirectResponse
    {
        $actor = $request->user('provider') ?? $request->user('web') ?? $request->user();
        $locale = $this->service->update($request->validated(), $actor);
        $request->session()->put('locale', $locale);
        return back();
    }
}
