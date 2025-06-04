<aside class="d-md-block bg-white border-end p-0 h-100">
    <div class="sidebar border-end" id="side_bar_menu">

        <div class="sidebar-header border-bottom">
          <div class="sidebar-brand"><b><small>Product Management</small></b></div>
        </div>

        <ul class="sidebar-nav">

            <li class="nav-item">
              <a class="nav-link active" href="#">
                <i class="nav-icon cil-bar-chart"></i> Dashboard
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="{{ route('admin.products.index') }}">
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
              <a class="nav-link active" href="{{ route('admin.categories.index') }}">
                <i class="nav-icon cil-speedometer"></i> Categories
              </a>
            </li>

            @role('super.admin')
            <div class="sidebar-header border-bottom">
               <div class="sidebar-brand"><b><small>Role Management</small></b></div>
             </div>

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

            <li class="nav-item">
              <a class="nav-link active" href="{{ route('admin.variation-attribute-values.index') }}">
                <i class="nav-icon cil-list"></i> Variation Attribute Values
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="{{ route('admin.variation-attributes.index') }}">
                <i class="nav-icon cil-list"></i> Variation Attributes
              </a>
            </li>

            @endrole
           
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
