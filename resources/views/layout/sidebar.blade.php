<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
      <!-- <img src="{{asset('dist/img/AdminLTELogo.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
      <span class="brand-text font-weight-light">Billiard</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          @if(Auth::user()->role == "billiard")
          <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('bl.index')}}" class="nav-link">
                  <i class="nav-icon material-symbols-outlined">table_restaurant</i>
                  <p>
                    Billiard
                  </p>
                </a>
              </li>
            </ul>
          </li>
          @endif
          <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('produk.index')}}" class="nav-link">
                  <i class="nav-icon fas fa-shopping-cart"></i>
                  <p>
                    Shopping
                  </p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('pr.stok')}}" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                    Produk Stok
                  </p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('rekap.order')}}" class="nav-link">
                  <i class="nav-icon fas fa-file"></i>
                  <p>
                    Rekap Produk
                  </p>
                </a>
              </li>
            </ul>
          </li>
          @if(Auth::user()->role == "billiard")
          <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{url('rekaptable')}}" class="nav-link">
                  <i class="nav-icon fas fa-file""></i>
                  <p>
                    Rekap Table
                  </p>
                </a>
              </li>
            </ul>
          </li>
          @endif
            <!-- <li class="nav-item menu-open">
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{route('rekap.bulan')}}" class="nav-link">
                    <i class="nav-icon fas fa-file"></i>
                    <p>
                      Rekap Bulanan
                    </p>
                  </a>
                </li>
              </ul>
            </li> -->
            <li class="nav-item menu-open">
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{route('bl.rekap')}}" class="nav-link">
                    <i class="nav-icon fas fa-file"></i>
                    <p>
                      Rekap Seluruh
                    </p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- <li class="nav-item menu-open">
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{route('member.index')}}" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <p>
                      Members
                    </p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item menu-open">
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{route('paket.index')}}" class="nav-link">
                    <i class="nav-icon fas fa-box"></i>
                    <p>
                      Paket
                    </p>
                  </a>
                </li>
              </ul>
            </li> -->
            <li class="nav-item menu-open">
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{route('harga.index')}}" class="nav-link">
                    <i class="nav-icon fas fa-dollar-sign"></i>
                    <p>
                      Harga
                    </p>
                  </a>
                </li>
              </ul>
            </li>
          <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
              <li class="nav-item">
              <form action="{{ route('logout') }}" method="POST" class="d-flex" role="search">
              @csrf
              @method('DELETE')
                <a href="{{route('bl.rekap')}}" class="nav-link">
                  <p>
                    <button class="btn btn-danger" type="submit">Logout</button>
                  </p>
                </a>
                </form>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>