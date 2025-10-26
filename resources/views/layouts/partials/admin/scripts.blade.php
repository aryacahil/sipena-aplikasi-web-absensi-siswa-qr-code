<!-- Libs JS -->
<script src="{{ asset('admin_assets/vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/feather-icons/dist/feather.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/prismjs/prism.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/dropzone/dist/min/dropzone.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/prismjs/plugins/toolbar/prism-toolbar.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/prismjs/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js') }}"></script>

<!-- SweetAlert2 dari Composer -->
<script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('admin_assets/js/main.js') }}"></script>
<script src="{{ asset('admin_assets/js/alpinejs.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/feather.js') }}"></script>
<script src="{{ asset('admin_assets/js/sidebarMenu.js') }}"></script>

<!-- Custom Initialization -->
<script>
// Initialize Feather Icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Initialize popovers
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
});
</script>

<!-- SweetAlert dari session -->
@include('sweetalert::alert')