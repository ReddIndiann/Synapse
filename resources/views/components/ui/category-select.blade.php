@props([
    'name' => 'category',
    'value' => null,
    'categories' => null,
    'filterByType' => false,
    'typeFieldId' => 'type',
    'required' => true,
    'label' => 'Category',
])

@php
    use App\Support\AccountingCategories;

    $uid = 'cat_' . uniqid();
    $current = old($name, $value);
    $presetCategories = $categories ?? AccountingCategories::all();
    $isOther = $current && !in_array($current, $presetCategories, true);
    $selectedPreset = $isOther ? AccountingCategories::OTHER_VALUE : $current;
@endphp

<div {{ $attributes->merge(['class' => 'category-select-field']) }}
     id="{{ $uid }}_wrap"
     data-filter-by-type="{{ $filterByType ? '1' : '0' }}"
     data-type-field="{{ $typeFieldId }}"
     data-income='@json(AccountingCategories::INCOME)'
     data-expense='@json(AccountingCategories::EXPENSE)'
     data-other-value="{{ AccountingCategories::OTHER_VALUE }}">
    <x-input-label :for="$uid . '_select'" :value="$label" />

    <select id="{{ $uid }}_select" class="auth-input mt-1">
        <option value="" disabled {{ $selectedPreset ? '' : 'selected' }}>Select category...</option>
        @foreach ($presetCategories as $cat)
            <option value="{{ $cat }}" @selected($selectedPreset === $cat)>{{ $cat }}</option>
        @endforeach
        <option value="{{ AccountingCategories::OTHER_VALUE }}" @selected($isOther)>Other (type custom)</option>
    </select>

    <input
        type="text"
        id="{{ $uid }}_custom"
        class="auth-input mt-2 {{ $isOther ? '' : 'hidden' }}"
        placeholder="Enter custom category"
        value="{{ $isOther ? $current : '' }}"
        maxlength="100"
    />

    <input type="hidden" name="{{ $name }}" id="{{ $uid }}_value" value="{{ $current }}" @if($required) required @endif />

    <x-input-error :messages="$errors->get($name)" class="mt-1" />
</div>

<script>
(function () {
    const wrap = document.getElementById(@json($uid . '_wrap'));
    if (!wrap || wrap.dataset.initialized) return;
    wrap.dataset.initialized = '1';

    const select = document.getElementById(@json($uid . '_select'));
    const custom = document.getElementById(@json($uid . '_custom'));
    const hidden = document.getElementById(@json($uid . '_value'));
    const otherValue = wrap.dataset.otherValue;
    const filterByType = wrap.dataset.filterByType === '1';
    const typeFieldId = wrap.dataset.typeField;
    const income = JSON.parse(wrap.dataset.income || '[]');
    const expense = JSON.parse(wrap.dataset.expense || '[]');

    function rebuildOptions(list, keepValue) {
        const currentVal = keepValue ?? hidden.value;
        const isCustom = currentVal && !list.includes(currentVal);

        select.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select category...';
        placeholder.disabled = true;
        if (!currentVal) placeholder.selected = true;
        select.appendChild(placeholder);

        list.forEach(function (cat) {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            if (cat === currentVal) opt.selected = true;
            select.appendChild(opt);
        });

        const otherOpt = document.createElement('option');
        otherOpt.value = otherValue;
        otherOpt.textContent = 'Other (type custom)';
        if (isCustom) otherOpt.selected = true;
        select.appendChild(otherOpt);

        custom.classList.toggle('hidden', select.value !== otherValue);
        if (isCustom) custom.value = currentVal;
    }

    function syncHidden() {
        if (select.value === otherValue) {
            hidden.value = custom.value.trim();
            custom.required = true;
        } else {
            hidden.value = select.value;
            custom.required = false;
        }
    }

    function onTypeChange() {
        if (!filterByType) return;
        const typeEl = document.getElementById(typeFieldId);
        if (!typeEl) return;
        const list = typeEl.value === 'income' ? income : expense;
        rebuildOptions(list, hidden.value);
        syncHidden();
    }

    select.addEventListener('change', function () {
        const showCustom = select.value === otherValue;
        custom.classList.toggle('hidden', !showCustom);
        if (showCustom) {
            custom.focus();
        } else {
            custom.value = '';
        }
        syncHidden();
    });

    custom.addEventListener('input', syncHidden);

    const form = wrap.closest('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            syncHidden();
            if (!hidden.value.trim()) {
                e.preventDefault();
                if (select.value === otherValue) {
                    custom.focus();
                } else {
                    select.focus();
                }
            }
        });
    }

    if (filterByType) {
        const typeEl = document.getElementById(typeFieldId);
        if (typeEl) {
            typeEl.addEventListener('change', onTypeChange);
            onTypeChange();
        }
    } else {
        syncHidden();
    }
})();
</script>
