<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"
        integrity="sha512-yFjZbTYRCJodnuyGlsKamNE/LlEaEAxSUDe5+u61mV8zzqJVFOH7TnULE2/PP/l5vKWpUNnF4VGVkXh3MjgLsg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>

<script>

  $(document).ready(function() {

    $('.paginate_button').addClass('btn')
    $('.table-container tr').on('click', function() {
      $('#' + $(this).data('display')).toggle();
    });

    $('#table-log').DataTable({
      'order': [],
      "scrollY":        "500px",
      'stateSave': true,
      'stateSaveCallback': function(settings, data) {
        window.localStorage.setItem('datatable', JSON.stringify(data));
      },
      'stateLoadCallback': function(settings) {
        var data = JSON.parse(window.localStorage.getItem('datatable'));
        if (data) data.start = 0;
        return data;
      },
      "drawCallback": function () {
        $('.paginate_button').addClass('btn')
        $('.paginate_button.current ').addClass('btn-primary')
        $('.select[name=table-log_length]').addClass('form-select')
      },

    });
    $('#delete-log, #delete-all-log').click(function() {
      return confirm('Are you sure?');
    });
  });
</script>

</body>
</html>