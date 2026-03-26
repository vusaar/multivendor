<aside class="d-md-block glass-panel h-100">
    <div class="sidebar" id="side_bar_menu">

        <div class="sidebar-header">
          <div class="sidebar-brand"><span style="color:var(--midnight);font-weight:700;letter-spacing:-0.01em;">EYAMI ADMIN</span></div>
        </div>

        <ul class="sidebar-nav">

            <li class="nav-item">
              <a class="nav-link" href="#">
                <i class="nav-icon cil-bar-chart"></i> Dashboard
              </a>
            </li>


            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.products.index') }}">
                <i class="nav-icon cil-library"></i> Products
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.vendors.search-insights') }}">
                <i class="nav-icon cil-search"></i> Search Insights
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.brands.index') }}">
                <i class="nav-icon cil-tags"></i> Brands
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="#">
                <i class="nav-icon cil-cart"></i> Orders
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.vendors.index') }}">
                <i class="nav-icon cil-house"></i> Vendors
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.categories.index') }}">
                <i class="nav-icon cil-speedometer"></i> Categories
              </a>
            </li>

            @role('super.admin')
            <div class="sidebar-header border-bottom">
               <div class="sidebar-brand"><b><small>Role Management</small></b></div>
             </div>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.roles.index') }}">
                <i class="nav-icon cil-speedometer"></i> Roles
              </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.search-logs.index') }}">
                  <i class="nav-icon cil-search"></i> Search Analytics
                </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.permissions.index') }}">
                <i class="nav-icon cil-lock-unlocked"></i> Permissions
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="nav-icon cil-user"></i> Users
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.variation-attribute-values.index') }}">
                <i class="nav-icon cil-list"></i> Variation Attribute Values
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('admin.variation-attributes.index') }}">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fallback for CoreUI nav-group toggling
        var toggles = document.querySelectorAll('.nav-group-toggle');
        toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var group = this.closest('.nav-group');
                if (group) {
                    group.classList.toggle('show');
                }
            });
        });
    });
</script>
