<div class="col-md-6 col-lg-4 mb-3">
    <div class="color-item-box shadow-sm">
        <div class="color-info">
            <div class="d-flex align-items-center mb-1">
                <label class="x-small fw-bold text-muted mb-0 mr-1">{{ $label }}</label>
                <i class="fas fa-info-circle help-icon" data-toggle="tooltip" title="{{ $hint }}"></i>
            </div>
            <code class="color-code x-small text-primary" style="font-family: monospace;">{{ $settings->$field ?? '#000000' }}</code>
        </div>
        <div class="color-preview-pill" style="background-color: {{ $settings->$field ?? '#000000' }}">
            <input type="color" name="{{ $field }}" value="{{ $settings->$field ?? '#000000' }}" class="color-input-hidden" data-field="{{ $field }}">
        </div>
    </div>
</div>
