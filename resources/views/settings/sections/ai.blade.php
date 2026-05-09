<div class="card card-flush py-4">
    <div class="card-header"><div class="card-title"><h2>AI Assistant Configuration</h2></div></div>
    <div class="card-body pt-0">
        <div class="mb-10 fv-row">
            <label class="form-label">AI Provider</label>
            <select name="ai_provider" class="form-select mb-2">
                <option value="">-- Tidak Aktif --</option>
                <option value="openai" {{ ($settings['ai_provider'] ?? '') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                <option value="gemini" {{ ($settings['ai_provider'] ?? '') == 'gemini' ? 'selected' : '' }}>Google Gemini</option>
            </select>
        </div>
        <div class="mb-10 fv-row">
            <label class="form-label">API Key</label>
            <input type="password" name="ai_api_key" class="form-control mb-2" value="{{ $settings['ai_api_key'] ?? '' }}" />
        </div>
        <div class="mb-10 fv-row">
            <label class="form-label">Model</label>
            <input type="text" name="ai_model" class="form-control mb-2" value="{{ $settings['ai_model'] ?? 'gpt-3.5-turbo' }}" />
        </div>
    </div>
</div>
