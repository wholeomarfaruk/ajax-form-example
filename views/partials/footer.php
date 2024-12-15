<!-- footer.php -->
</div>
 <!-- Toast Container -->
 <div id="toast-container"></div>
<!-- Content End    -->
<!-- Bottom Navigation -->
<!-- JS and other assets -->
<script src="<?php echo BaseDir::getProjectLink('lib/jquery/jquery-3.7.1.min.js');?>"></script>

<script src="<?php echo BaseDir::getProjectLink('lib/bootstrap/bootstrap.bundle.min.js');?>"></script>
<?php if (isset($load_lottie_cdn) && $load_lottie_cdn): ?>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<?php endif?>
<!-- Owl Carousel cdn  -->
<?php if (isset($load_owlcarousel_cdn) && $load_owlcarousel_cdn): ?>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

<?php endif; ?>
<!-- Datatable cdn  -->
<?php if (isset($load_datatable_cdn) && $load_datatable_cdn): ?>
    <script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.5.0/js/dataTables.rowReorder.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.5.0/js/rowReorder.dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.dataTables.js"></script>

<?php endif; ?>

<!-- Chartjs cdn  -->
<?php if (isset($load_chartjs_cdn) && $load_chartjs_cdn): ?>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Chart.js Date Adapter (date-fns) -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0"></script>
<?php endif; ?>

<script src="<?php echo BaseDir::getProjectLink('assets/js/script.js'); ?>"></script>


</body>

</html>