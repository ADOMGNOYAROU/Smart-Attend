@props([
    'toggles' => [
        ['active' => true, 'small' => false, 'showView' => true],
        ['active' => false, 'small' => false, 'showView' => false],
        ['active' => false, 'small' => false, 'showView' => false],
        ['active' => true, 'small' => true, 'showView' => false],
        ['active' => false, 'small' => false, 'showView' => false],
        ['active' => false, 'small' => false, 'showView' => false],
    ]
])

@push('styles')
    @include('components.toggle-style')
@endpush

<div class="toggle-row" style="display: flex; flex-direction: column; gap: 20px; max-width: 300px; margin: 0 auto;">
    @foreach($toggles as $index => $toggle)
        <div class="toggle-item">
            <x-toggle-switch 
                :id="'toggle-' . $index"
                :value="$toggle['active']"
                :small="$toggle['small']"
                :showViewButton="$toggle['showView']"
            />
        </div>
    @endforeach
</div>
