<div class='row my-3'>
    <?php
    if ($this->router->fetch_class() == 'orders' && in_array($this->router->fetch_method(), ['index', 'search'])) {
        ?>
        <div class='col-12'>
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
            <div class="form-group" style="padding-right: 3px;">

                <?php
                $options = array();
                $options[] = get_msg('select_order_filter');
                $options[] = get_msg('Return Order');
                $options[] = get_msg('Seller Dispute Order');
                $options[] = get_msg('Cancel Order');
                $options[] = get_msg('Delivered Order');

                echo form_dropdown(
                        'is_return',
                        $options,
                        set_value('is_return', show_data(@$orders->is_return), false),
                        'class="form-control form-control-sm mr-3" id="is_return"'
                );
                ?>

            </div>
            <div class="form-group" style="padding-right: 3px;">
                <?php
                $pay_options = array();
                $pay_options[] = get_msg('select_payment_filter');
                $pay_options['pending'] = get_msg('pending');
                $pay_options['initiate'] = get_msg('initiate');
                $pay_options['succeeded'] = get_msg('succeeded');
                $pay_options['fail'] = get_msg('fail');
                echo form_dropdown(
                        'pay_filter',
                        $pay_options,
                        set_value('pay_filter', show_data(@$pay_filter), false),
                        'class="form-control form-control-sm mr-3" id="pay_filter"'
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
<?php } ?>
</div>