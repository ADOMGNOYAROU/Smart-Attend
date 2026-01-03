@props([
    'id' => 'toggle-' . uniqid(),
    'name' => 'toggle',
    'value' => false,
    'small' => false,
    'showViewButton' => false
])

<div class="toggle-container {{ $small ? 'small' : '' }}">
    <div class="toggle-switch">
        <input 
            type="checkbox" 
            id="{{ $id }}" 
            name="{{ $name }}" 
            {{ $value ? 'checked' : '' }}
            class="toggle-input"
        >
        <label for="{{ $id }}" class="toggle-label">
            <span class="toggle-handle"></span>
        </label>
    </div>
    
    @if($showViewButton)
        <button type="button" class="view-button">Voir</button>
    @endif
</div>
