<div class="animated fadeInRight">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo get_msg('Change status ') ?>
        </h3>
    </div>
    <div class='row my-3'>

        <div class='col-9'>
            <?php
            $attributes = array('class' => 'form-inline');
            echo form_open($module_site_url . '/changestatus', $attributes);
            ?>
            <input type="hidden" name="record_id" id="record_id" value="<?php echo $detail->id; ?>">
            <input type="hidden" name="item_id" id="item_id" value="<?php echo $detail->operation_id; ?>">
            <div class="form-group ml-3 mr-3">

                <?php
                $options = array();
                $options[] = get_msg('change status');
                $options['open'] = get_msg('open');
                $options['in-process'] = get_msg('in-process');
                $options['rejected'] = get_msg('rejected');
                $options['re-listed'] = get_msg('re-listed');
                echo form_dropdown(
                        'changestatus',
                        $options,
                        set_value('id', show_data(@$detail->status), false),
                        'class="form-control form-control-sm mr-3" id="changestatus"'
                );
                ?>

            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-sm btn-primary mr-3">
                    <?php echo get_msg('save') ?>
                </button>

                <a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
                    <?php echo get_msg('btn_cancel') ?>
                </a>
            </div>
            
            <div class="form-group ml-5">
                <a href="<?php echo site_url('admin').'/items/edit/'.$detail->operation_id; ?>" target="_blank">Item Detail</a>
            </div>

            <?php echo form_close(); ?>

        </div>	
    </div>

</div>