<!--Revenue CHART -->
<div class="card-header">
  <h3 class="card-title">
         <?php echo $panel_title; ?>
  </h3>

<!--  <div class="card-tools">
    <button type="button" class="btn btn-tool" data-widget="collapse"><i class="fa fa-minus"></i>
    </button>
    <button type="button" class="btn btn-tool" data-widget="remove"><i class="fa fa-times"></i>
    </button>
  </div>-->
</div>

<div class="box-body chart-responsive">
  <div class="chart" id="transaction-line-chart" style="height: 230px;"></div>
</div>

<?php 
    foreach ($data as $key => $value) {
        $jan_count = ($value->record_month == 1) ? $value->record_count : 0;
        $feb_count = ($value->record_month == 2) ? $value->record_count : 0;
        $mar_count = ($value->record_month == 3) ? $value->record_count : 0;
        $apr_count = ($value->record_month == 4) ? $value->record_count : 0;
        $may_count = ($value->record_month == 5) ? $value->record_count : 0;
        $jun_count = ($value->record_month == 6) ? $value->record_count : 0;
        $jul_count = ($value->record_month == 7) ? $value->record_count : 0;
        $aug_count = ($value->record_month == 8) ? $value->record_count : 0;
        $sep_count = ($value->record_month == 9) ? $value->record_count : 0;
        $oct_count = ($value->record_month == 10) ? $value->record_count : 0;
        $nov_count = ($value->record_month == 11) ? $value->record_count : 0;
        $dec_count = ($value->record_month == 12) ? $value->record_count : 0;
    }
?>

<script>
  $(function () {
    "use strict";

    //Line CHART
    var monthNames = ["", "Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
    ];
    Morris.Line({
        element: 'transaction-line-chart',
        data: [
           {y: 1, a: <?php echo ($jan_count) ?? 0; ?>},
            {y: 2, a: <?php echo ($feb_count) ?? 0; ?>},
            {y: 3, a: <?php echo ($mar_count) ?? 0; ?>},
            {y: 4, a: <?php echo ($apr_count) ?? 0; ?>},
            {y: 5, a: <?php echo ($may_count) ?? 0; ?>},
            {y: 6, a: <?php echo ($jun_count) ?? 0; ?>},
            {y: 7, a: <?php echo ($jul_count) ?? 0; ?>},
            {y: 8, a: <?php echo ($aug_count) ?? 0; ?>},
            {y: 9, a: <?php echo ($sep_count) ?? 0; ?>},
            {y: 10, a: <?php echo ($oct_count) ?? 0; ?>},
            {y: 11, a: <?php echo ($nov_count) ?? 0; ?>},
            {y: 12, a: <?php echo ($dec_count) ?? 0; ?>}
        ],
        xkey: 'y',
        parseTime: false,
        ykeys: ['a'],
        xLabelFormat: function (x) {
            var index = parseInt(x.src.y);
            return monthNames[index];
        },
        xLabels: "month",
        labels: ['Transaction'],
        lineColors: ['#00ffb2'],
        lineWidth        : 2,
        hideHover: 'auto'

    });

  });
</script>