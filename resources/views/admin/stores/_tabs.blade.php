{{-- 店舗内の各機能タブ。$store と $active（'overview'|'gbp'|'keywords'|'competitors'）を渡す --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'overview' ? 'active' : '' }}"
           href="{{ route('admin.stores.show', $store) }}">
            <i class="bi bi-shop"></i> 基本情報
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'gbp' ? 'active' : '' }}"
           href="{{ route('admin.stores.gbp-basic.edit', $store) }}">
            <i class="bi bi-google"></i> GBP 基本情報
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'keywords' ? 'active' : '' }}"
           href="{{ route('admin.stores.keywords.index', $store) }}">
            <i class="bi bi-search"></i> 計測キーワード
            <span class="badge text-bg-light ms-1">{{ $store->keywords()->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'competitors' ? 'active' : '' }}"
           href="{{ route('admin.stores.competitors.index', $store) }}">
            <i class="bi bi-people"></i> 競合
            <span class="badge text-bg-light ms-1">{{ $store->competitors()->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'rankings' ? 'active' : '' }}"
           href="{{ route('admin.stores.rankings.index', $store) }}">
            <i class="bi bi-bar-chart"></i> 順位履歴
        </a>
    </li>
</ul>
