<?php

namespace App\Modules\Support\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Settings\Models\CompanySetting;
use App\Modules\Support\Enums\Operation;
use App\Modules\Support\Enums\SupportCategory;
use App\Modules\Support\Enums\SupportStatus;
use App\Modules\Support\Events\SupportTicketsChanged;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Requests\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContactWebController extends Controller
{
    /**
     * عرض صفحة تواصل معنا.
     */
    public function index(Request $request): View
    {
        $settings = CompanySetting::current();
        $categories = SupportCategory::cases();

        return view('pages.contact', compact('settings', 'categories'));
    }

    /**
     * استقبال وإرسال رسالة التواصل وحفظها كتذكرة دعم.
     */
    public function submit(ContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = DB::transaction(function () use ($request, $validated) {
            // التحقق من هوية المستخدم أو إنشاء حساب زائر تلقائياً
            $user = $request->user();

            if (!$user) {
                $user = User::where('phone', $validated['phone'])
                    ->orWhere('email', $validated['email'])
                    ->first();

                if (!$user) {
                    $residentRoleId = Role::where('code', 'resident')->value('id');
                    $user = User::create([
                        'role_id' => $residentRoleId,
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'email' => $validated['email'],
                        'locale' => app()->getLocale(),
                        'password' => Hash::make(Str::random(24)),
                    ]);
                }
            }

            // حفظ المرفق إن وجد
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('support/attachments', 'public');
            }

            // توليد رمز تذكرة فريد
            $reference = 'TKT-' . strtoupper(Str::random(8));

            $newTicket = SupportTicket::create([
                'reference' => $reference,
                'user_id' => $user->id,
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'attachment_path' => $attachmentPath,
                'status' => SupportStatus::Open->value,
            ]);

            DB::afterCommit(fn () => event(new SupportTicketsChanged(Operation::TicketCreated, $newTicket->id, $user->id)));

            return $newTicket;
        });

        return redirect()->route('contact')->with([
            'status' => 'تم استلام رسالتكم بنجاح، وسيتواصل معكم فريق الدعم في أقرب وقت.',
            'ticket_reference' => $ticket->reference,
        ]);
    }
}

