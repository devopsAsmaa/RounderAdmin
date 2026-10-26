<?php

namespace App\Modules\Providers\Requests;

use App\Core\Enums\SupportedLocale;
use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;

use App\Core\Support\SaudiPhone;

/** تعديل بيانات الشركة والحساب؛ تغيير كلمة المرور له مسار إدارة الحسابات المخصص. */
class AdminProviderUpdateRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('phone') || $this->has('mobile')) {
            $raw = (string) ($this->input('phone') ?? $this->input('mobile') ?? '');
            $this->merge([
                'phone' => SaudiPhone::normalize($raw),
            ]);
        }
    }

    /**
     * يتحدد استثناء التفرد من السجل المرتبط بالمسار، وليس من user_id مرسل من العميل.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('provider')->user_id;
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX, Rule::unique('users', 'phone')->ignore($userId)],
            'locale' => ['required', Rule::enum(SupportedLocale::class)],
            'company_name' => ['required', 'string', 'min:2', 'max:160'],
            'is_approved' => ['required', 'boolean'],
            'contract_id'=>['nullable','integer'],
            'service_commission_percent'=>['required_with:contract_id','numeric','between:0,100','decimal:0,2'],
            'emergency_commission_percent'=>['required_with:contract_id','numeric','between:0,100','decimal:0,2'],
            'starts_on'=>['required_with:contract_id','date'],
            'ends_on'=>['nullable','date','after_or_equal:starts_on'],
            'terms'=>['nullable','string','max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
