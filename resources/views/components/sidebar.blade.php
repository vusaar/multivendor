<aside class="d-none d-md-block bg-white border-end p-0 h-100">
    <div class="sidebar border-end" id="side_bar_menu">

        <div class="sidebar-header border-bottom">
          <div class="sidebar-brand">Menu</div>
        </div>

        <ul class="sidebar-nav">

            <li class="nav-item">
              <a class="nav-link active" href="#">
                <i class="nav-icon cil-bar-chart"></i> Dashboard
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="#">
                <i class="nav-icon cil-library"></i> Products
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="#">
                <i class="nav-icon cil-cart"></i> Orders
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="{{ route('admin.vendors.index') }}">
                <i class="nav-icon cil-house"></i> Vendors
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="#">
                <i class="nav-icon cil-speedometer"></i> Categories
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="{{ route('admin.roles.index') }}">
                <i class="nav-icon cil-speedometer"></i> Roles
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="{{ route('admin.permissions.index') }}">
                <i class="nav-icon cil-lock-unlocked"></i> Permissions
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="{{ route('admin.users.index') }}">
                <i class="nav-icon cil-user"></i> Users
              </a>
            </li>

           
        </ul>
        <div class="sidebar-footer border-top d-flex">
           <button class="sidebar-toggler" type="button"></button>
        </div>

     </div>
</aside>
<!-- Mobile sidebar (optional, for offcanvas) -->
<div class="d-md-none">
    <!-- You can implement a Bootstrap offcanvas here for mobile if needed -->
</div>
