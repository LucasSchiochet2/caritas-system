@if (backpack_user()->canManageParish($entry))
    <a href="{{ backpack_url('user?parish_id='.$entry->getKey()) }}" class="btn btn-sm btn-link">
        <i class="la la-users"></i> Usuários
    </a>
@endif
