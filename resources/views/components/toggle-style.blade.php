@once
@push('styles')
<style>
    .toggle-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }

    .toggle-switch.small {
        width: 40px;
        height: 20px;
    }

    .toggle-input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-label {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e0e0e0;
        border-radius: 15px;
        cursor: pointer;
        transition: .4s;
    }

    .toggle-label:before {
        content: "";
        position: absolute;
        height: 26px;
        width: 26px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        border-radius: 50%;
        transition: .4s;
    }

    .toggle-input:checked + .toggle-label {
        background-color: #2196F3;
    }

    .toggle-input:checked + .toggle-label:before {
        transform: translateX(30px);
    }

    .toggle-switch.small .toggle-label:before {
        height: 16px;
        width: 16px;
    }

    .toggle-switch.small .toggle-input:checked + .toggle-label:before {
        transform: translateX(20px);
    }

    .view-button {
        padding: 2px 8px;
        font-size: 12px;
        background: #333;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .view-button:hover {
        background: #555;
    }
</style>
@endpush
@endonce
