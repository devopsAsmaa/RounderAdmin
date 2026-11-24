<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index(); // individuals, pricing, business, providers
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('question', 255);
            $table->text('answer');
            $table->timestamps();

            $table->unique(['faq_id', 'locale']);
        });

        // Seed initial 14 comprehensive FAQs
        $initialFaqs = [
            // 1. خدمات الأفراد والحجز
            [
                'category' => 'individuals',
                'sort_order' => 1,
                'q_ar' => 'كيف أطلب خدمة صيانة منزلية من راوندر؟',
                'a_ar' => 'يمكنك طلب الخدمة بخطوات بسيطة: اختر نوع الخدمة المطلوبة (تكييف، سباكة، كهرباء، نظافة، تركيب وغيرها)، حدد الموعد والوقت المناسب لك في مدينة الرياض، ثم أكّد طلبك وسيصلك فني معتمد في الموعد المحدد.',
                'q_en' => 'How do I book a home maintenance service with Rounder?',
                'a_en' => 'Booking is simple: choose your required service (AC, plumbing, electrical, cleaning, etc.), set your preferred date and time in Riyadh, confirm your booking, and a certified technician will arrive on time.',
            ],
            [
                'category' => 'individuals',
                'sort_order' => 2,
                'q_ar' => 'هل يمكنني تحديد وتخصيص موعد الزيارة المناسب لي؟',
                'a_ar' => 'نعم، تتيح لك راوندر اختيار التاريخ وساعة الزيارة بما يتوافق مع جدولك اليومي، مع إمكانية التنسيق المباشر والمتابعة مع الفني عبر التطبيق أو الرسائل.',
                'q_en' => 'Can I customize and select my preferred visit time?',
                'a_en' => 'Yes, Rounder allows you to choose the exact date and hour that fits your daily schedule, with real-time tracking and direct coordination with the technician.',
            ],
            [
                'category' => 'individuals',
                'sort_order' => 3,
                'q_ar' => 'ماذا لو تطلب الإصلاح توفير قطع غيار إضافية؟',
                'a_ar' => 'يقوم الفني بفحص ومعاينة العطل أولاً، وإبلاغك بقطع الغيار المطلوبة وتكلفتها بدقة، ولا يتم شراء أو تركيب أي قطعة إلا بعد موافقتك الصريحة المسبقة.',
                'q_en' => 'What if the repair requires additional spare parts?',
                'a_en' => 'The technician inspects the issue first and informs you of any required parts and their exact cost. No parts are purchased or installed without your prior explicit consent.',
            ],
            [
                'category' => 'individuals',
                'sort_order' => 4,
                'q_ar' => 'ما هو نطاق التغطية الجغرافية لراوندر؟',
                'a_ar' => 'تغطي راوندر حالياً كافة أحياء ومناطق مدينة الرياض (الشمال، الشرق، الغرب، والجنوب)، مع خطط مستمرة للتوسع في باقي مدن ومناطق المملكة قريباً.',
                'q_en' => 'What is the geographic coverage area of Rounder?',
                'a_en' => 'Rounder currently covers all neighborhoods across Riyadh city, with continuous expansion plans to cover other regions of Saudi Arabia soon.',
            ],

            // 2. الأسعار والدفع والضمان
            [
                'category' => 'pricing',
                'sort_order' => 5,
                'q_ar' => 'كيف يتم احتساب تكلفة الخدمات وأسعار الصيانة؟',
                'a_ar' => 'تعتمد راوندر تسعيراً واضحاً وشفافاً لكل خدمة وباقة قبل تأكيد الطلب دون أي رسوم خفية. في حال وجود أعمال إضافية أثناء الفحص يتم إخطارك بالتكلفة والموافقة عليها قبل البدء.',
                'q_en' => 'How are service costs and maintenance prices calculated?',
                'a_en' => 'Rounder relies on clear, upfront, and transparent pricing displayed before confirming your booking with no hidden fees. Additional work is quoted and approved prior to commencement.',
            ],
            [
                'category' => 'pricing',
                'sort_order' => 6,
                'q_ar' => 'ما هي طرق ووسائل الدفع المتاحة في المنصة؟',
                'a_ar' => 'نوفر خيارات دفع رقمية آمنة ومتوافقة مع المعايير السعودية تشمل: بطاقات مدى (Mada)، فيزا، ماستركارد، Apple Pay، بالإضافة إلى خيارات الدفع عند اكتمال الخدمة.',
                'q_en' => 'What payment methods are available on the platform?',
                'a_en' => 'We offer secure digital payment methods compliant with Saudi standards including Mada, Visa, Mastercard, and Apple Pay, as well as payment upon completion.',
            ],
            [
                'category' => 'pricing',
                'sort_order' => 7,
                'q_ar' => 'هل تتوفر ضمانات على أعمال الصيانة المنفذة؟',
                'a_ar' => 'نعم، تخضع كافة خدمات راوندر لضمان الجودة؛ حيث يتابع فريق الدعم حالة الطلب بعد الإنجاز للتأكد من زوال العطل وعمل الخدمة بكفاءة تامة.',
                'q_en' => 'Do you provide warranties on completed maintenance work?',
                'a_en' => 'Yes, all Rounder services are backed by a quality guarantee, and our support team follows up after completion to ensure your total satisfaction.',
            ],
            [
                'category' => 'pricing',
                'sort_order' => 8,
                'q_ar' => 'كيف يمكنني إلغاء أو إعادة جدولة موعد الخدمة؟',
                'a_ar' => 'يمكنك تعديل موعد الزيارة أو إلغاء الطلب من خلال حسابك في المنصة، أو بالتواصل المباشر مع مركز الدعم وخدمة العملاء قبل موعد الزيارة المحدد.',
                'q_en' => 'How can I reschedule or cancel a service appointment?',
                'a_en' => 'You can reschedule or cancel your booking through your account on the app or website, or by contacting our support team prior to the scheduled time.',
            ],

            // 3. حلول الأعمال والشركات
            [
                'category' => 'business',
                'sort_order' => 9,
                'q_ar' => 'ما هي الخدمات المخصصة لقطاع الأعمال والمنشآت؟',
                'a_ar' => 'نقدم حزم صيانة تشغيلية شاملة للمكاتب، المقرات، المعارض، والمجمعات التجارية تشمل: الصيانة الوقائية الدورية، الصيانة الطارئة، فرقة فنية مخصصة، وإدارة مركزية لجميع فروعك.',
                'q_en' => 'What services are customized for corporate and commercial businesses?',
                'a_en' => 'We provide comprehensive operational packages for offices, corporate branches, and retail complexes, covering preventive maintenance, emergency response, and dedicated technicians.',
            ],
            [
                'category' => 'business',
                'sort_order' => 10,
                'q_ar' => 'كيف يمكن لمنشأتنا طلب عرض سعر أو عقد صيانة سنوي؟',
                'a_ar' => 'يمكنك التواصل معنا عبر نموذج "تواصل معنا" واختيار تصنيف "حلول الأعمال والشركات"، أو الاتصال بالرقم الموحد، وسيقوم مستشار حلول الأعمال بدراسة احتياجاتكم وتقديم عرض متكامل.',
                'q_en' => 'How can our company request a customized proposal or annual maintenance contract?',
                'a_en' => 'Contact us via the Contact Us form selecting Business Solutions, or call our unified number. A dedicated enterprise consultant will review your needs and present a tailored proposal.',
            ],
            [
                'category' => 'business',
                'sort_order' => 11,
                'q_ar' => 'هل تصدرون فواتير ضريبية معتمدة لهيئة الزكاة والضريبة والجمارك (ZATCA)؟',
                'a_ar' => 'نعم، جميع تعاملات راوندر تصدر بفواتير ضريبية إلكترونية معتمدة ومتوافقة 100% مع متطلبات هيئة الزكاة والضريبة والجمارك لتوثيق مصاريف منشأتكم بكل سهولة.',
                'q_en' => 'Do you issue ZATCA-compliant electronic tax invoices?',
                'a_en' => 'Yes, all Rounder transactions are billed with official electronic tax invoices fully compliant with ZATCA regulations.',
            ],

            // 4. بوابة مزودي الخدمة والفنيين
            [
                'category' => 'providers',
                'sort_order' => 12,
                'q_ar' => 'كيف يمكنني التسجيل كمزود خدمة في منصة راوندر؟',
                'a_ar' => 'يمكنك التقديم عبر رابط "التسجيل كمزود خدمة" وتعبئة بيانات شركتك أو مؤسستك والخدمات التي تتخصص بها ورفع المستندات، لتتم مراجعتها واعتماد حسابك من الإدارة.',
                'q_en' => 'How can I register as a service provider with Rounder?',
                'a_en' => 'Apply via the Register as Provider link, complete your company and specialization details, and submit your official documents for admin review and approval.',
            ],
            [
                'category' => 'providers',
                'sort_order' => 13,
                'q_ar' => 'ما هي الوثائق المطلوبة لاعتماد المنشأة؟',
                'a_ar' => 'المستندات المطلوبة تشمل: السجل التجاري الساري، هوية الممثل النظامي، رخصة البلدية، وتوقيع العقد الإلكتروني الرسمي مع المنصة.',
                'q_en' => 'What documents are required to approve a provider account?',
                'a_en' => 'Required documents include a valid commercial registration (CR), national ID of the legal representative, municipal license, and acceptance of the digital platform contract.',
            ],
            [
                'category' => 'providers',
                'sort_order' => 14,
                'q_ar' => 'كيف يدير مزود الخدمة طلبات الصيانة والفنيين التابعين له؟',
                'a_ar' => 'توفر راوندر بوابة إلكترونية متطورة تمكنك من إضافة الفنيين، وتحديد تخصصاتهم، واستقبال طلبات العملاء وتوزيعها وإسنادها، ومتابعة التحصيلات والأرباح بدقة.',
                'q_en' => 'How does a provider manage bookings and assigned technicians?',
                'a_en' => 'Rounder offers an advanced portal allowing you to register technicians, manage sub-services, assign customer bookings, and monitor performance and earnings in real time.',
            ],
        ];

        $now = now();
        foreach ($initialFaqs as $faq) {
            $faqId = DB::table('faqs')->insertGetId([
                'category' => $faq['category'],
                'sort_order' => $faq['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('faq_translations')->insert([
                [
                    'faq_id' => $faqId,
                    'locale' => 'ar',
                    'question' => $faq['q_ar'],
                    'answer' => $faq['a_ar'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'faq_id' => $faqId,
                    'locale' => 'en',
                    'question' => $faq['q_en'],
                    'answer' => $faq['a_en'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('faqs');
    }
};

