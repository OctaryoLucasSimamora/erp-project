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

    <!-- PURCHASE MODULE -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePurchase"
            aria-expanded="true" aria-controls="collapsePurchase">
            <i class="fas fa-shopping-cart"></i>
            <span>Purchase</span>
        </a>

        <div id="collapsePurchase" class="collapse" aria-labelledby="headingPurchase" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                <h6 class="collapse-header">Purchase Menu:</h6>

                <a class="collapse-item" href="{{ route('purchase.vendor.index') }}">
                    <i class="fas fa-truck"></i> Vendor
                </a>

                <a class="collapse-item" href="{{ route('purchase.rfq.index') }}">
                    <i class="fas fa-file-alt"></i> RFQ (Request for Quotation)
                </a>

                <a class="collapse-item" href="{{ route('purchase.po.index') }}">
                    <i class="fas fa-file-invoice-dollar"></i> Purchase Order
                </a>

                <a class="collapse-item" href="{{ route('purchase.vendor-bill.index') }}">
                    <i class="fas fa-receipt"></i> Vendor Bill
                </a>

            </div>
        </div>
    </li>

    <!--  SALES MODULE   -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSales"
            aria-expanded="true" aria-controls="collapseSales">
            <i class="fas fa-chart-line"></i>
            <span>Sales</span>
        </a>

        <div id="collapseSales" class="collapse" aria-labelledby="headingSales" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                <h6 class="collapse-header">SALES MENU:</h6>

                <!-- Quotation -->
                <a class="collapse-item" href="{{ route('sales.quotation.index') }}">
                    <i class="fas fa-file-invoice mr-2"></i> Quotation
                </a>

                <!-- Sales Order -->
                <a class="collapse-item" href="{{ route('sales.order.index') }}">
                    <i class="fas fa-shopping-cart mr-2"></i> Sales Order
                </a>

                <!-- Invoice -->
                <a class="collapse-item" href="">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Invoice
                </a>

                <!-- Laporan -->
                <a class="collapse-item" href="">
                    <i class="fas fa-chart-bar mr-2"></i> Laporan Penjualan
                </a>

                <!-- Retur (Opsional) -->
                <a class="collapse-item" href="">
                    <i class="fas fa-undo-alt mr-2"></i> Retur Penjualan
                </a>

            </div>
        </div>
    </li>

    <!-- PURCHASE Employee -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseEmployee"
            aria-expanded="true" aria-controls="collapseEmployee">
            <i class="fas fa-users"></i>
            <span>Employee</span>
        </a>
        <div id="collapseEmployee" class="collapse" aria-labelledby="headingEmployee"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">EMPLOYEE MENU:</h6>

                <a class="collapse-item" href="{{ route('employee.department.index') }}">
                    <i class="fas fa-building"></i> Department
                </a>

                <a class="collapse-item" href="{{ route('employee.job_position.index') }}">
                    <i class="fas fa-briefcase"></i> Job Position
                </a>

                <a class="collapse-item" href="{{ route('employee.employee.index') }}">
                    <i class="fas fa-user-tie"></i> Employee
                </a>
            </div>
        </div>
    </li>
</ul>
