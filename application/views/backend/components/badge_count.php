<!-- small box -->
<div class="small-box <?php echo $color; ?>">
    <div class="inner">
        <h3 style="color: white;">
            <?php echo $total_count; ?>
        </h3>

        <p style="color:white;font-size: 16px;"><?php echo $label; ?></p>
    </div>
    <div class="icon">
        <i class="<?php echo $icon; ?>"></i>
    </div>
    <?php 
        if(!$hide_url) {
            echo 
    '<a href="'.$url.'" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>';
        }
    ?>
</div>