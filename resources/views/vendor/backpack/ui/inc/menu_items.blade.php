{{-- This file is used for menu items by any Backpack v7 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('bazaar-item') }}"><i class="la la-archive nav-icon"></i> Estoque do bazar</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('bazaar-customer') }}"><i class="la la-address-card nav-icon"></i> Clientes do bazar</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('parish') }}"><i class="la la-church nav-icon"></i> Paróquias</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="la la-users nav-icon"></i> Usuários</a></li>

<x-backpack::menu-item title="Families" icon="la la-question" :link="backpack_url('family')" />
<x-backpack::menu-item title="Cashboxes" icon="la la-question" :link="backpack_url('cashbox')" />
<x-backpack::menu-item title="Logs cashboxes" icon="la la-question" :link="backpack_url('logs-cashbox')" />
<x-backpack::menu-item title="Home visits" icon="la la-question" :link="backpack_url('home-visit')" />