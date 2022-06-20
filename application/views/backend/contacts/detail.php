<div class="table-responsive animated fadeInRight">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo get_msg('contact_info_label') ?>
        </h3>
    </div>

    <div class="card-body p-0">
        <table class="table m-0 table-striped">
            <tr>
                <th><?php echo get_msg('contact_name') ?></th>
                <td><?php echo $contact->contact_name; ?></td>
            </tr>
            <tr>
                <th><?php echo get_msg('contact_email') ?></th>
                <td><?php echo $contact->contact_email; ?></td>
            </tr>
            <tr>
                <th><?php echo get_msg('contact_phone') ?></th>
                <td><?php echo $contact->contact_phone; ?></td>
            </tr>
            <tr>
                <th><?php echo get_msg('about_contact_label') ?></th>
                <td><?php echo $contact->contact_message; ?></td>
            </tr>
        </table>
    </div>
    <div class='row my-3'>

        <div class='col-9'>
            <?php
            $attributes = array('class' => 'form-inline');
            echo form_open($module_site_url . '/changestatus', $attributes);
            ?>
            <input type="hidden" name="record_id" id="record_id" value="<?php echo $contact->contact_id; ?>">
            <div class="form-group ml-3 mr-3">

                <?php
                    $options=array();
                    $options[]=get_msg('change status');
                    $options['open']=get_msg('open');
                    $options['in-process']=get_msg('in-process');
                    $options['closed']=get_msg('closed');
                    $status = @$contact->status;
                    if(@$contact->status == 'read') {
                        $status = 'open';
                    }
                    echo form_dropdown(
                        'changestatus',
                        $options,
                        set_value( 'id', show_data($status), false ),
                        'class="form-control form-control-sm mr-3" id="changestatus"'
                    );
                ?>

            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-sm btn-primary">
<?php echo get_msg('save') ?>
                </button>
            </div>

<?php echo form_close(); ?>

        </div>	
    </div>

    <div class="card-footer text-center">
        <a class="btn btn-primary" href="<?php echo $module_site_url ?>" class="btn"><?php echo get_msg('back_button') ?></a>
    </div>
</div>