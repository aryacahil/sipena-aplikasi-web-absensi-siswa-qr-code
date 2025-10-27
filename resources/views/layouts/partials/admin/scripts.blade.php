<!-- Libs JS -->
<script src="{{ asset('admin_assets/vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>

<!-- Other Vendor Libraries -->
<script src="{{ asset('admin_assets/vendor/prismjs/prism.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/dropzone/dist/min/dropzone.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/prismjs/plugins/toolbar/prism-toolbar.min.js') }}"></script>
<script src="{{ asset('admin_assets/vendor/prismjs/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js') }}"></script>

<!-- SweetAlert2 -->
<script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('admin_assets/js/main.js') }}"></script>
<script src="{{ asset('admin_assets/js/alpinejs.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/sidebarMenu.js') }}"></script>

<!-- Custom Initialization -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inisialisasi popover
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>

<!-- SweetAlert dari session -->
@include('sweetalert::alert')
