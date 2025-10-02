@props([
  'name',
  'label',
  'options' => [],
  'value' => null,
  'placeholder' => '— bitte wählen —',
  'disabled' => false,
])

<div class="mb-4">
    <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}"
            @class(['w-full rounded-lg border-gray-300', 'bg-gray-100 cursor-not-allowed' => $disabled])
            @if($disabled) disabled @endif>
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $opt)
            <option value="{{ $opt }}" @selected(old($name, $value) === $opt)>{{ $opt }}</option>
        @endforeach
    </select>
    @error($name)
      <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
