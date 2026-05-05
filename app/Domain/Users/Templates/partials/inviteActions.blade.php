{{-- Partial rendered by InviteActions::getLink() --}}
{{-- Displays a copyable invite link for an admin --}}

@isset($inviteLink)
    <div id="invite-link-box-{{ $userId }}" class="inviteLinkBox" style="display:flex; align-items:center; gap:8px; padding:6px 0;">
        <input
            type="text"
            id="invite-link-input-{{ $userId }}"
            class="inviteLinkInput"
            value="{{ $inviteLink }}"
            readonly
            style="flex:1; font-size:12px; padding:4px 8px;"
            onclick="this.select()"
        />
        <button
            type="button"
            class="btn btn-secondary btn-xs"
            onclick="
                navigator.clipboard.writeText('{{ $inviteLink }}')
                    .then(function() {
                        var btn = this;
                        var original = btn.innerHTML;
                        btn.innerHTML = '<i class=\'fa fa-check\'></i>';
                        setTimeout(function() { btn.innerHTML = original; }, 2000);
                    }.bind(this))
                    .catch(function() {
                        document.getElementById('invite-link-input-{{ $userId }}').select();
                        document.execCommand('copy');
                    });
            "
            title="{{ __('label.copyinviteLink') }}"
        >
            <i class="fa fa-copy"></i>
        </button>
    </div>
@endisset
