<header class=" header">
    <div class="container-fluid" style="justify-content:normal !important;">
        <!-- humburger menu -->
         <span style="padding:8px !important;margin:5px;" class="d-lg-none d-sm-block d-xs-block">
           <button type="button" class="btn" onclick='toggle_side_menu()'><i class="cil-menu" ></i> </button>
         </span>

        <!-- Brand/logo -->
        <a class="header-brand" href="{{ route('dashboard') }}">
            <span style="color:var(--primary); font-size:1.5rem; margin-right:5px;">●</span>
            <span class="fw-bold" style="font-size:1.25rem;">EYAMI</span>
        </a>

        


        <!-- Navigation links -->

      <ul class="header-nav" style="margin-left: auto;">
    
    
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="headerDropdownMenuLink" role="button" data-coreui-toggle="dropdown" aria-expanded="false">
           <span class="me-2">{{ Auth::user()->name }}</span>
       </a>

      <div class="dropdown-menu" aria-labelledby="headerDropdownMenuLink">
           <a class="dropdown-item" href="#">
              <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
              </x-dropdown-link>
           </a>
         <a class="dropdown-item" href="#">     
                <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                </form>
         </a>
      </div>
    </li>
  </ul>

        <!-- end nav links -->

        
    </div>
</header>
