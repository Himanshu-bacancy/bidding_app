<div class="col-12 col-md-12 main teamps-sidebar-push">

    <?php
    // load breadcrumb
    show_breadcrumb($action_title);
    // show flash message
    flash_msg();
    ?>

    <?php
    $attributes = array('id' => 'noti-form');
    echo form_open($module_site_url . '/notisubmit', $attributes);
    ?>
    <section class="content animated fadeInRight">

        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><?php echo get_msg('Send Notification') ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <input type='hidden' id= 'userids' name= 'userids' value='<?php echo $ids; ?>'>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo get_msg('title'); ?></label>
                            <?php
                            echo form_input(array(
                                'name' => 'title',
                                'value' => set_value('title'),
                                'class' => 'form-control form-control-sm',
                                'placeholder' => get_msg('title'),
                                'id' => 'title'
                            ));
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6"  style="padding-left: 50px;">
                        <div class="form-group">
                            <label>
                                <?php echo get_msg('noti_des_label') ?> 
                                <a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('noti_message_tooltips') ?>">
                                    <span class='glyphicon glyphicon-info-sign menu-icon'>
                                </a>
                            </label>

                            <textarea class="form-control" name="description" placeholder="<?php echo get_msg('noti_des_label') ?>" rows="5"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" name="submit" class="btn btn-sm btn-primary">
                    <?php echo get_msg('btn_save') ?>
                </button>

                <a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
                    <?php echo get_msg('btn_cancel') ?>
                </a>
            </div>
        </div>
    </section>

    <?php echo form_close(); ?>

</div>
<script>
    function jqvalidate() {
        $('#noti-form').validate({
            rules: {
                title: {
                    blankCheck: ""
                },
                description: {
                    blankCheck: ""
                }
            },
            messages: {
                title: {
                    blankCheck: "<?php echo get_msg('notification title not empty'); ?>"
                },
                description: {
                    blankCheck: "<?php echo get_msg('notification description not empty'); ?>"
                }
            }
        });
        // custom validation
        jQuery.validator.addMethod("blankCheck", function (value, element) {

            if (value == "") {
                return false;
            } else {
                return true;
            }
        });
    }
</script>