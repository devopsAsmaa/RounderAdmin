{{-- زر مستقل لا يرسل DELETE قبل تأكيد المستخدم داخل النافذة المشتركة. --}}
<form method="post" action="{{ $action }}" class="d-inline delete-record-form">
@csrf @method('DELETE')
<button type="button" data-confirm-delete class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
    <i class="fa-solid fa-trash-can me-1"></i>{{ __('Delete') }}
</button>
</form>
