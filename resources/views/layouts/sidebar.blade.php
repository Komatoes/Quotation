{{-- Include Font Awesome in your head (if not yet included) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="app-brand" 
     style="padding: 50px 0 50px 0px; border-bottom: 1px solid rgba(0, 0, 0, 0.1); text-align: center;">
    <a href="{{ url('/') }}" 
       class="app-brand-link d-flex align-items-center justify-content-center"
       style="width: 100%;">
        <img 
            src="{{ asset('Image/LOGO.png') }}" 
            alt="Company Logo"
            style="max-width: 220px; height: auto; object-fit: contain; display: block; margin: 0 auto;"
            onerror="this.onerror=null; this.alt='Logo not found';"
        >
    </a>
</div>

<ul class="menu-inner py-1">

    <!-- Quotations -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="fa-solid fa-file-lines menu-icon"></i>
            <div>Quotations</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item">
                <a href="javascript:void(0);" 
                   class="menu-link" 
                   id="{{ isset($mobile) && $mobile ? 'btn-add-quotation-mobile' : 'btn-add-quotation' }}">
                    <i class="fa-solid fa-circle-plus sub-icon"></i>
                    Create Quotation
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ url('/quotations/drafts') }}" class="menu-link">
                    <i class="fa-solid fa-pencil sub-icon"></i>
                    Drafts
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ url('/quotations/archives') }}" class="menu-link">
                    <i class="fa-solid fa-box-archive sub-icon"></i>
                    Archives
                </a>
            </li>
        </ul>
    </li>

    <!-- Projects -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="fa-solid fa-briefcase menu-icon"></i>
            <div>Projects</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item">
                <a href="{{ url('/projects/current') }}" class="menu-link">
                    <i class="fa-solid fa-building sub-icon"></i>
                    Current Projects
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ url('/projects/drafts') }}" class="menu-link">
                    <i class="fa-solid fa-pencil sub-icon"></i>
                    Drafts
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ url('/projects/archives') }}" class="menu-link">
                    <i class="fa-solid fa-box-archive sub-icon"></i>
                    Archives
                </a>
            </li>
        </ul>
    </li>

    <!-- Materials -->
    <li class="menu-item">
        <a href="{{ url('/materials') }}" class="menu-link">
            <i class="fa-solid fa-list-check menu-icon"></i>
            <div>Material List</div>
        </a>
    </li>
</ul>

<style>
.menu-icon, .sub-icon {
    width: 22px;
    text-align: center;
    margin-right: 10px;
    color: #ffffff;
    transition: color 0.2s ease, transform 0.2s ease;
}

.menu-link:hover .menu-icon,
.menu-link:hover .sub-icon {
    color: #42955c !important;
    transform: scale(1.1);
}

.menu-item.active > .menu-link .menu-icon {
    color: #42955c !important;
}
</style>
