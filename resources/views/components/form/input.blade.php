@props(['name', 'label', 'type' => 'text', 'value' => '', 'step' => null])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
           @if ($step !== null) step="{{ $step }}" @endif
           value="{{ old($name, $value) }}"
           {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
