{{-- クライアント側の店舗タブ（read-only） --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'overview' ? 'active' : '' }}"
           href="{{ route('client.stores.show', $store) }}">
            <i class="bi bi-shop"></i> 基本情報
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'rankings' ? 'active' : '' }}"
           href="{{ route('client.stores.rankings', $store) }}">
            <i class="bi bi-bar-chart"></i> 順位履歴
        </a>
    </li>
</ul>
