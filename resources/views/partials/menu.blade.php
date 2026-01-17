@php
  $menu = config('tenant.menu', []);
@endphp

<aside class="main-sidebar">
  <section class="sidebar">
    <ul class="sidebar-menu" data-widget="tree">
      @foreach($menu as $mod)
        <li class="header">{{ $mod['nome'] }}</li>

        {{-- telas sem grupo --}}
        @foreach($mod['telas_sem_grupo'] as $t)
          <li>
            <a href="{{ url($t['slug']) }}">
              <i data-feather="{{ $t['icone'] ?? 'circle' }}"></i>
              <span>{{ $t['nome_tela'] }}</span>
            </a>
          </li>
        @endforeach

        {{-- grupos --}}
        @foreach($mod['grupos'] as $g)
          <li class="treeview">
            <a href="#">
              <i data-feather="{{ $g['icone'] ?? 'folder' }}"></i>
              <span>{{ $g['nome'] }}</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-right pull-right"></i>
              </span>
            </a>

            <ul class="treeview-menu">
              @foreach($g['telas'] as $t)
                <li>
                  <a href="{{ url($t['slug']) }}">
                    <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                    {{ $t['nome_tela'] }}
                  </a>
                </li>
              @endforeach
            </ul>
          </li>
        @endforeach
      @endforeach
    </ul>
  </section>
</aside>
