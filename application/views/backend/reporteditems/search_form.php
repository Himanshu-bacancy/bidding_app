<div class='row my-3'>

    <div class='col-9'>
        <?php
        $attributes = array('class' => 'form-inline');
        echo form_open($module_site_url . '/search', $attributes);
        ?>

        <div class="form-group mr-3">

            <?php
            echo form_input(array(
                'name' => 'searchterm',
                'value' => set_value('searchterm'),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('btn_search')
            ));
            ?>

        </div>

        <div class="form-group">

            <?php
            $options[0] = 'Select status';
            $options['open'] = 'Open';
            $options['in-progress'] = 'In-progress';
            $options['rejected'] = 'Rejected';
            $options['re-liseted'] = 'Re-Listed';
            echo form_dropdown(
                    'status_dd',
                    $options,
                    set_value('status_dd', show_data(@$status_dd), false),
                    'class="form-control form-control-sm mr-3" id="status_dd"'
            );
            ?>

        </div>

        <div class="form-group col-4">
            <div class="input-group" style="width: 100%;">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
                <input type="text" class="form-control float-right mr-3" readonly name="reservation" id="reservation" value="<?php echo @$reservation; ?>">
            </div>
            <!-- /.input group -->
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-sm btn-primary">
<?php echo get_msg('btn_search') ?>
            </button>
        </div>

        <div class="row">
            <div class="form-group ml-3">
                <a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
<?php echo get_msg('btn_reset'); ?>
                </a>
            </div>
        </div>

<?php echo form_close(); ?>

    </div>	

</div>