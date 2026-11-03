/**
 * Rounder UI controller — vanilla JavaScript + Bootstrap 5.3.
 * All requests are in-memory previews. No API calls, payments or persistence.
 */
(() => {
  'use strict';

  const config = window.ROUNDER_CONFIG;
  const select = (query, root = document) => root.querySelector(query);
  const selectAll = (query, root = document) => [...root.querySelectorAll(query)];
  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[character]);
  const icon = name => `<svg class="icon" aria-hidden="true"><use href="#i-${escapeHtml(name)}"></use></svg>`;
  const findService = id => config.services.find(service => service.id === id);

  const navigation = select('#main-nav');
  const navigationCollapse = bootstrap.Collapse.getOrCreateInstance(navigation, { toggle: false });
  const modalElement = select('#request-modal');
  const modalBody = select('#modal-content');
  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

  let opener = null;
  let booking = {};
  let actions = {};
  let submitHandler = null;

  // Bootstrap manages transitions, focus containment, Escape and backdrop dismissal.
  const focusViewTitle = () => select('#modal-title')?.focus({ preventScroll: true });
  modalElement.addEventListener('shown.bs.modal', focusViewTitle);
  modalElement.addEventListener('hidden.bs.modal', () => {
    if (opener?.isConnected) opener.focus({ preventScroll: true });
  });

  function renderView(html, nextActions = {}, onSubmit = null) {
    modalBody.innerHTML = html;
    actions = nextActions;
    submitHandler = onSubmit;
    modalBody.scrollTop = 0;
    if (modalElement.classList.contains('show')) focusViewTitle();
    else modal.show();
  }

  // One event listener per surface avoids duplicated listeners between steps.
  modalBody.addEventListener('click', event => {
    const button = event.target.closest('[data-action]');
    if (button && modalBody.contains(button)) actions[button.dataset.action]?.(button);
  });

  modalBody.addEventListener('submit', event => {
    event.preventDefault();
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    form.classList.add('was-validated');
    if (!form.checkValidity()) {
      select(':invalid', form)?.focus();
      return;
    }
    submitHandler?.(Object.fromEntries(new FormData(form)));
  });

  // Navigation and service filtering.
  selectAll('.navbar-nav .nav-link').forEach(link => {
    link.addEventListener('click', () => {
      selectAll('.navbar-nav .nav-link').forEach(item => item.classList.toggle('active', item === link));
      navigationCollapse.hide();
    });
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') navigationCollapse.hide();
  });

  selectAll('[data-filter]').forEach(button => {
    button.addEventListener('click', () => {
      let visibleCount = 0;
      selectAll('[data-filter]').forEach(item => {
        item.classList.toggle('active', item === button);
        item.setAttribute('aria-pressed', String(item === button));
      });
      selectAll('[data-service-item]').forEach(item => {
        const visible = button.dataset.filter === 'all' || item.dataset.category === button.dataset.filter;
        item.classList.toggle('d-none', !visible);
        if (visible) visibleCount += 1;
      });
      select('#filter-status').textContent = `الخدمات المعروضة: ${visibleCount}`;
    });
  });

  document.addEventListener('click', event => {
    const serviceButton = event.target.closest('[data-service]');
    if (serviceButton) {
      opener = serviceButton;
      showServiceDetails(serviceButton.dataset.service);
      return;
    }
    const journeyButton = event.target.closest('[data-modal]');
    if (!journeyButton) return;
    opener = journeyButton;
    navigationCollapse.hide();
    const routes = {
      booking: () => { booking = {}; showServiceSelection(); },
      business: () => showPartnerForm('business'),
      provider: () => showPartnerForm('provider'),
      help: showHelp
    };
    routes[journeyButton.dataset.modal]?.();
  });

  // Shared, escaped view fragments.
  function heading(eyebrow, title, description) {
    return `<span class="modal-eyebrow">${escapeHtml(eyebrow)}</span>
      <h2 class="journey-title" id="modal-title" tabindex="-1">${escapeHtml(title)}</h2>
      <p class="modal-description">${escapeHtml(description)}</p>`;
  }

  function demoNotice(message = 'هذه تجربة تصميم فقط. لا تُرسل أي بيانات ولا يتم إنشاء حجز فعلي.') {
    return `<p class="alert demo-notice">${escapeHtml(message)}</p>`;
  }

  function actionButtons(primary, secondary = 'رجوع', primaryAction = '', secondaryAction = 'back') {
    return `<div class="journey-actions">
      <button class="btn btn-primary" type="${primaryAction ? 'button' : 'submit'}"
        ${primaryAction ? `data-action="${primaryAction}"` : ''}>${escapeHtml(primary)} ${icon('arrow')}</button>
      <button class="btn btn-outline-primary" type="button" data-action="${secondaryAction}">${escapeHtml(secondary)}</button>
    </div>`;
  }

  function reviewList(entries) {
    return `<dl class="review-list">${entries.map(([label, value]) => `
      <div><dt>${escapeHtml(label)}</dt><dd>${escapeHtml(value)}</dd></div>`).join('')}
    </dl>`;
  }

  function inputField({ name, label, type = 'select', options = [], value = '', full = false,
    required = true, min, max, placeholder = '', error = 'يرجى اختيار قيمة صحيحة.' }) {
    const id = `field-${name}`;
    const validationId = `${id}-error`;
    const shared = `id="${id}" name="${name}" aria-describedby="${validationId}" ${required ? 'required' : ''}`;
    let control;
    if (type === 'select') {
      control = `<select class="form-select" ${shared}>${options.map(option => `
        <option value="${escapeHtml(option)}" ${value === option ? 'selected' : ''}>${escapeHtml(option)}</option>`).join('')}
      </select>`;
    } else if (type === 'textarea') {
      control = `<textarea class="form-control" ${shared} maxlength="400"
        placeholder="${escapeHtml(placeholder)}">${escapeHtml(value)}</textarea>`;
    } else {
      control = `<input class="form-control" ${shared} type="${escapeHtml(type)}" value="${escapeHtml(value)}"
        ${min !== undefined ? `min="${escapeHtml(min)}"` : ''}
        ${max !== undefined ? `max="${escapeHtml(max)}"` : ''}>`;
    }
    return `<div class="${full ? 'col-12' : 'col-12 col-sm-6'}">
      <label class="form-label" for="${id}">${escapeHtml(label)}</label>
      ${control}<div class="invalid-feedback" id="${validationId}">${escapeHtml(error)}</div>
    </div>`;
  }

  function todayInRiyadh() {
    const parts = new Intl.DateTimeFormat('en-GB', {
      timeZone: config.timeZone, year: 'numeric', month: '2-digit', day: '2-digit'
    }).formatToParts(new Date());
    const part = key => parts.find(item => item.type === key).value;
    return `${part('year')}-${part('month')}-${part('day')}`;
  }

  function formatDate(value) {
    return new Intl.DateTimeFormat(config.locale, { dateStyle: 'long', calendar: 'gregory' })
      .format(new Date(`${value}T12:00:00`));
  }

  // Home service journey: choose → schedule → review → local preview complete.
  function showServiceDetails(id) {
    const service = findService(id);
    if (!service) return;
    renderView(
      heading('خدمات راوندر', service.name, service.description) +
      `<div class="service-icon tone-teal mt-3">${icon(service.icon)}</div>
      <ul class="detail-list">${service.items.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul>
      <p class="modal-description">نطاق العمل النهائي والتكلفة يُحدّدان قبل تأكيد الطلب عند تشغيل المنصة.</p>` +
      demoNotice() + actionButtons('جرّب طلب الخدمة', 'إغلاق', 'start', 'close'),
      { start: () => { booking = { service: id }; showBookingDetails(); }, close: () => modal.hide() }
    );
  }

  function showServiceSelection() {
    renderView(
      heading('الخطوة ١ من ٣', 'إيش يحتاج بيتك؟', 'اختر خدمة واحدة للبدء في تجربة الحجز.') +
      `<div class="row row-cols-2 g-2 mt-3">${config.services.map(service => `
        <div class="col"><button type="button" class="modal-service ${booking.service === service.id ? 'selected' : ''}"
          data-action="select-service" data-value="${service.id}" aria-pressed="${booking.service === service.id}">
          ${icon(service.icon)} ${escapeHtml(service.name)}</button></div>`).join('')}
      </div>` + demoNotice() +
      `<div class="journey-actions"><button class="btn btn-primary" type="button" id="next-service"
        data-action="next" ${booking.service ? '' : 'disabled'}>حدّد الموعد ${icon('arrow')}</button></div>`,
      {
        'select-service': button => {
          booking.service = button.dataset.value;
          selectAll('[data-action="select-service"]', modalBody).forEach(item => {
            item.classList.toggle('selected', item === button);
            item.setAttribute('aria-pressed', String(item === button));
          });
          select('#next-service').disabled = false;
        },
        next: showBookingDetails
      }
    );
  }

  function showBookingDetails() {
    const service = findService(booking.service);
    if (!service) return showServiceSelection();
    const today = todayInRiyadh();
    const fields = [
      { name: 'city', label: 'المدينة', options: config.cities, value: booking.city },
      { name: 'property', label: 'نوع المكان', options: config.propertyTypes, value: booking.property },
      { name: 'date', label: 'اليوم المفضل', type: 'date', min: today, value: booking.date || today,
        error: 'اختر تاريخ اليوم أو يومًا لاحقًا.' },
      { name: 'time', label: 'الفترة المفضلة', options: config.timeSlots, value: booking.time },
      { name: 'details', label: 'تفاصيل إضافية (اختياري)', type: 'textarea', required: false, full: true,
        value: booking.details, placeholder: 'اكتب وصفًا مختصرًا لاحتياجك' }
    ];
    renderView(
      heading('الخطوة ٢ من ٣', `رتّب موعد ${service.name}`, 'اختر موعدًا تجريبيًا يناسبك. المدن توضيحية وليست تأكيدًا للتغطية.') +
      `<form id="booking-form" novalidate><div class="row g-3 mt-2">${fields.map(inputField).join('')}</div>` +
      demoNotice() + actionButtons('راجع التفاصيل') + '</form>',
      { back: () => {
        Object.assign(booking, Object.fromEntries(new FormData(select('#booking-form'))));
        showServiceSelection();
      } },
      data => { Object.assign(booking, data); showBookingReview(); }
    );
  }

  function showBookingReview() {
    const service = findService(booking.service);
    const entries = [
      ['الخدمة', service.name], ['المدينة', booking.city], ['نوع المكان', booking.property],
      ['التاريخ', formatDate(booking.date)], ['الفترة', booking.time]
    ];
    if (booking.details) entries.push(['ملاحظتك', booking.details]);
    renderView(
      heading('الخطوة ٣ من ٣', 'كل التفاصيل، قدّامك.', 'راجع اختياراتك قبل إكمال تجربة الحجز.') +
      reviewList(entries) + demoNotice() + actionButtons('أكمل التجربة', 'تعديل الموعد', 'complete', 'edit'),
      { complete: showBookingComplete, edit: showBookingDetails }
    );
  }

  function showBookingComplete() {
    const service = findService(booking.service);
    renderView(
      `<div class="success-icon">${icon('check')}</div>` +
      heading('اكتملت معاينة الرحلة', 'وهكذا، طلبك جاهز.', 'هذا هو شكل نهاية الحجز في راوندر. لم يتم إرسال طلب أو حجز موعد فعلي.') +
      reviewList([['الخدمة', service.name], ['المدينة', booking.city], ['الموعد', formatDate(booking.date)]]) +
      actionButtons('العودة إلى الموقع', 'جرّب خدمة أخرى', 'close', 'restart'),
      { close: () => modal.hide(), restart: () => { booking = {}; showServiceSelection(); } }
    );
  }

  // Business and provider forms share fields, validation and editable summaries.
  function partnerSchema(type) {
    if (type === 'business') {
      return {
        eyebrow: 'راوندر للأعمال', title: 'خلّنا نفهم احتياجك.',
        description: 'جرّب إعداد ملخص لخطة صيانة مناسبة لمنشأتك.',
        submit: 'معاينة ملخص الأعمال', summaryTitle: 'صورة أوضح لاحتياجك.',
        summaryDescription: 'ملخص توضيحي يمكن استخدامه كأساس لتخصيص عرض الخدمة.',
        notice: 'لم يُرسل طلب عرض سعر. يُضاف التواصل والتسعير عند ربط الموقع بالخدمات الفعلية.',
        fields: [
          { name: 'type', label: 'نوع المنشأة', options: config.businessTypes },
          { name: 'city', label: 'المدينة', options: config.cities },
          { name: 'count', label: 'عدد المواقع', type: 'number', min: 1, max: 500, value: '1',
            error: 'أدخل عددًا صحيحًا من ١ إلى ٥٠٠.' },
          { name: 'maintenance', label: 'نوع الصيانة', options: config.maintenanceTypes },
          { name: 'service', label: 'الخدمة الأساسية', full: true,
            options: ['خدمات متعددة', ...config.services.map(service => service.name)] }
        ]
      };
    }
    return {
      eyebrow: 'شركاء راوندر', title: 'خبرتك، بداية الفرصة.',
      description: 'استكشف نموذج الانضمام لمزودي الخدمات.',
      submit: 'معاينة ملف المزود', summaryTitle: 'مكان لمهارتك في راوندر.',
      summaryDescription: 'هذا ملخص توضيحي لملف مزود الخدمة.',
      notice: 'لم يتم تسجيل حساب أو إرسال بيانات. متطلبات التسجيل والاعتماد تُحدد قبل الإطلاق.',
      fields: [
        { name: 'type', label: 'نوع مزود الخدمة', options: config.providerTypes },
        { name: 'city', label: 'مدينة العمل', options: config.cities },
        { name: 'specialty', label: 'التخصص', options: config.services.map(service => service.name) },
        { name: 'experience', label: 'الخبرة', options: config.experienceOptions }
      ]
    };
  }

  function showPartnerForm(type, values = {}) {
    const schema = partnerSchema(type);
    const fields = schema.fields.map(field => inputField({ ...field, value: values[field.name] ?? field.value }));
    renderView(
      heading(schema.eyebrow, schema.title, schema.description) +
      `<form id="${type}-form" novalidate><div class="row g-3 mt-2">${fields.join('')}</div>` +
      demoNotice() + actionButtons(schema.submit, 'إلغاء', '', 'close') + '</form>',
      { close: () => modal.hide() },
      data => showPartnerSummary(type, schema, data)
    );
  }

  function showPartnerSummary(type, schema, values) {
    const entries = schema.fields.map(field => [field.label, values[field.name]]);
    renderView(
      heading(schema.eyebrow, schema.summaryTitle, schema.summaryDescription) +
      reviewList(entries) + demoNotice(schema.notice) +
      actionButtons('العودة إلى الموقع', 'تعديل البيانات', 'close', 'edit'),
      { close: () => modal.hide(), edit: () => showPartnerForm(type, values) }
    );
  }

  function showHelp() {
    renderView(
      heading('نساعدك تختار', 'إيش المشكلة اللي تواجهك؟', 'اختر الوصف الأقرب لاحتياجك، ونوصلك للخدمة المناسبة.') +
      `<div class="row row-cols-2 g-2 mt-3">${config.services.map(service => `
        <div class="col"><button type="button" class="modal-service" data-action="choose-help" data-value="${service.id}">
          ${icon(service.icon)} ${escapeHtml(service.helpLabel)}</button></div>`).join('')}
      </div>`,
      { 'choose-help': button => showServiceDetails(button.dataset.value) }
    );
  }
})();
