            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        // Base URL for AJAX requests
        var base_url = '<?php echo base_url(); ?>';

        // Initialize DataTables
        $(document).ready(function() {
            if ($('.data-table').length > 0) {
                $('.data-table').DataTable({
                    "order": [[0, "desc"]],
                    "pageLength": 25
                });
            }
        });

        // Show success message
        function showSuccess(message) {
            showAlert(message, 'success');
        }

        // Show error message
        function showError(message) {
            showAlert(message, 'danger');
        }

        // Show alert
        function showAlert(message, type) {
            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>';

            $('.alert-container').html(alertHtml);

            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>
</body>
</html>
