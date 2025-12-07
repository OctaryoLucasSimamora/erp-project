<!-- minimal sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
        <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-laugh-wink"></i></div>
        <div class="sidebar-brand-text mx-3">ERP</div>
    </a>
    <hr class="sidebar-divider my-0">
    <li class="nav-item active">
        <a class="nav-link" href="{{ url('/') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Modules</div>
    
    <!-- MANUFACTURING MODULE -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseManufacturing"
            aria-expanded="true" aria-controls="collapseManufacturing">
            <i class="fas fa-industry"></i>
            <span>Manufacturing</span>
        </a>

        <div id="collapseManufacturing" class="collapse" aria-labelledby="headingManufacturing"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                <h6 class="collapse-header">Manufacturing Menu:</h6>

                <!-- TAMBAHKAN MENU MANUFACTURING ORDER -->
                <a class="collapse-item" href="{{ route('manufacturing.index') }}">
                    <i class="fas fa-clipboard-list"></i> Manufacturing Order
                </a>

                <a class="collapse-item" href="{{ route('products.index') }}">
                    <i class="fas fa-box"></i> Produk
                </a>

                <a class="collapse-item" href="{{ route('raw-materials.index') }}">
                    <i class="fas fa-flask"></i> Raw Materials
                </a>

                <a class="collapse-item" href="{{ route('bom.index') }}">
                    <i class="fas fa-layer-group"></i> Bill of Materials (BoM)
                </a>

            </div>
        </div>
    </li>
    
    <!-- Optional: Tambahkan menu lain jika perlu -->
</ul>