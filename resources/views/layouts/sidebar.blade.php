<div class="app-brand mb-3">
    <a href="{{ url('/') }}" class="app-brand-link">
        <span class="app-brand-text fw-bold">My App</span>
    </a>
</div>

<ul class="menu-inner py-1">
    <!-- Quotations -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon ti ti-file-text"></i>
            <div>Quotations</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item">
                <a href="javascript:void(0);" 
                   class="menu-link" 
                   id="{{ isset($mobile) && $mobile ? 'btn-add-quotation-mobile' : 'btn-add-quotation' }}">
                    Create Quotation
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ url('/quotations/drafts') }}" class="menu-link">Drafts</a>
            </li>
            <li class="menu-item">
                <a href="{{ url('/quotations/archives') }}" class="menu-link">Archives</a>
            </li>
        </ul>
    </li>

    <!-- Projects -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon ti ti-briefcase"></i>
            <div>Projects</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item"><a href="{{ url('/projects/current') }}" class="menu-link">Current Projects</a></li>
            <li class="menu-item"><a href="{{ url('/projects/drafts') }}" class="menu-link">Drafts</a></li>
            <li class="menu-item"><a href="{{ url('/projects/archives') }}" class="menu-link">Archives</a></li>
        </ul>
    </li>

    <!-- Materials -->
    <li class="menu-item">
        <a href="{{ url('/materials') }}" class="menu-link">
            <i class="menu-icon ti ti-list"></i>
            <div>Material List</div>
        </a>
    </li>
</ul>

@if(isset($mobile) && $mobile)
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Dropdown toggle for mobile inside Bootstrap offcanvas
    document.querySelectorAll("#mobileSidebar .menu-toggle").forEach(toggle => {
        toggle.addEventListener("click", function () {
            const parent = this.closest(".menu-item");
            parent.classList.toggle("open");
        });
    });
});
</script>

@if(isset($mobile) && $mobile)
<style>
/* --- Make mobile sidebar stacked (vertical) --- */
#mobileSidebar .menu-inner {
    display: block !important;
}

#mobileSidebar .menu-item {
    display: block;
    width: 100%;
}

#mobileSidebar .menu-link {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    width: 100%;
    border-radius: 6px;
    color: #333;
}

#mobileSidebar .menu-icon {
    margin-right: 10px;
    font-size: 18px;
}

#mobileSidebar .menu-item.open > .menu-sub {
    margin-top: 4px;
}

#mobileSidebar .menu-sub .menu-link {
    padding-left: 35px;
    font-size: 0.95rem;
}

#mobileSidebar .menu-item.open > .menu-link {
    background-color: #f0f0f0;
    font-weight: 600;
}

/* optional spacing and scroll */
#mobileSidebar .menu-inner {
    padding-bottom: 2rem;
}
</style>
@endif

@endif
