<?php $this->load->view( $template_path .'/partials/nav' ); ?>
<div class="container-fluid">
  <div class="content-wrapper"  style="background-color: #ffff;padding-left: 30px;">
  		<div class="row">
			<div class="col-12 col-md-3 sidebar teamps-sidebar-open">
				<?php $this->load->view( $template_path .'/partials/sidebar' ); ?>
			</div>
            <div class="col-12 col-md-12 main teamps-sidebar-push">
				<?php 
                    // load breadcrumb
					show_breadcrumb($action_title);
					// show flash message
					flash_msg();
                    $attributes = array('id' => 'sizegroup-option-form', 'enctype' => 'multipart/form-data');
                    if(empty($sizegroup_option->id)){
                        $formAction = "admin/sizegroupoption/add/".$sizegroup->id;
                    } else {
                        $formAction = "admin/sizegroupoption/edit/".$sizegroup->id.'/'.$sizegroup_option->id;
                    }
                    echo form_open($formAction, $attributes);
				?>
                <input type="hidden" value="<?php echo $sizegroup->id; ?>" name="sizegroup_id">
                <?php if(!empty($sizegroup_option->id)){ ?>
                    <input type="hidden" value="<?php echo $sizegroup_option->id; ?>" name="sizegroupoption_id">
                <?php } ?>
                <section class="content animated fadeInRight">
                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Size Group : <?php echo $sizegroup->name; ?></h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php 
                                        if(empty($sizegroup_option->id)){
                                        ?>
                                            <a href="javascript:void(0);" class="add-row btn btn-primary" style="float:right;">
                                                <i style="font-size: 18px;" class="fa fa-plus"></i> Add Option
                                            </a>
                                            <br>
                                            <br>
                                        <?php 
                                        }
                                        ?>
                                        <table class="table table-hover" id="sizeGroupOptions">
                                            <tbody>
                                                <tr id="row_0">
                                                    <td>
                                                    <?php 
                                                        echo form_input( array(
                                                            'name' => 'data[0][optionname]',
                                                            'value' => set_value( 'name', show_data( @$sizegroup_option->title ), false ),
                                                            'class' => 'form-control',
                                                            'placeholder' => 'Option Name',
                                                            'id' => 'optionname_0'
                                                        )); 
                                                    ?>
                                                    </td>
                                                    <td>
                                                    <?php 
                                                        echo form_input( array(
                                                            'name' => 'data[0][optiondesc]',
                                                            'value' => set_value( 'description', show_data( @$sizegroup_option->description ), false ),
                                                            'class' => 'form-control',
                                                            'placeholder' => 'Option Description',
                                                            'id' => 'optiondesc_0'
                                                        )); 
                                                    ?>    
                                                    </td>
                                                    <?php 
                                                    if(empty($sizegroup_option->id)){
                                                    ?>
                                                        <td>
                                                            <i style="font-size: 18px;" class="fa fa-trash-o text-danger" onclick="remove(0)"></i>
                                                        </td>
                                                    <?php 
                                                    }
                                                    ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <!-- /.row -->
                                </div>
                            <!-- /.card-body -->
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary submit_form">
                                    <?php echo get_msg('btn_save')?>
                                </button>
                                <a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
                                    <?php echo get_msg('btn_cancel')?>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            <?php 
                echo form_close(); 
                show_ads(); 
            ?>
			</div>
		</div>
	</div>
</div>
<script>
    let lineNo = 1;
    jQuery(document).ready(function () {
        jQuery(".add-row").on('click', function () {
            markup = '<tr id="row_'+ lineNo +'"><td><input type="text" name="data['+ lineNo +'][optionname]" id="optionname_'+ lineNo +'" placeholder="Option Name" class="form-control"></td><td><input type="text" name="data['+ lineNo +'][optiondesc]" id="optiondesc_'+ lineNo +'" placeholder="Option Description" class="form-control"></td><td><i style="font-size: 18px;" class="fa fa-trash-o text-danger"  onclick="remove('+ lineNo +')"></i></td></tr>';
            tableBody = $("table tbody");
            tableBody.append(markup);
            lineNo++;
        });
        jQuery('.submit_form').on('click', function(){
            var isValid = true;
            
            jQuery("#sizeGroupOptions tr").each(function () {    
                var str = $(this).attr('id');
                var i = str.replace("row_", "");
                
                if (jQuery('#optionname_'+i).val() == "") {
                    isValid = false;
                    jQuery('#optionname_'+i).addClass('error-border');
                }
                i++;
            });
            if(isValid==true){
                jQuery('#sizegroup-option-form').submit();
            }
        });
    }); 
    function remove(id){
        $('#row_'+id).remove();
    }
</script>