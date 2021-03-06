<?php
	$attributes = array('id' => 'search-form', 'enctype' => 'multipart/form-data');
	echo form_open( $module_site_url .'/search', $attributes);
?>

<div class='row my-3'>
	<div class="col-12">
		<div class='form-inline'>
			<div class="form-group" style="padding-top: 3px;padding-right: 2px;">

				<?php echo form_input(array(
					'name' => 'searchterm',
					'value' => set_value( 'searchterm', $searchterm ),
					'class' => 'form-control form-control-sm mr-3',
					'placeholder' => get_msg( 'btn_search' )
				)); ?>

		  	</div>

		  	<div class="form-group" style="padding-top: 3px;padding-right: 2px;">

				<?php
					$options=array();
					$options[0]=get_msg('Prd_search_cat');
					
					$categories = $this->Category->get_all( );
					foreach($categories->result() as $cat) {
						
							$options[$cat->cat_id]=$cat->cat_name;
					}
					
					echo form_dropdown(
						'cat_id',
						$options,
						set_value( 'cat_id', show_data( $cat_id ), false ),
						'class="form-control form-control-sm mr-3" id="cat_id"'
					);
				?> 

		  	</div>

	  		<div class="form-group" style="padding-top: 3px;">

				<?php
					if($selected_cat_id != "") {

						$options=array();
						$options[0]=get_msg('Prd_search_subcat');
						$conds['cat_id'] = $selected_cat_id;
						$sub_cat = $this->Subcategory->get_all_by($conds);
						foreach($sub_cat->result() as $subcat) {
							$options[$subcat->id]=$subcat->name;
						}
						echo form_dropdown(
							'sub_cat_id',
							$options,
							set_value( 'sub_cat_id', show_data( $sub_cat_id ), false ),
							'class="form-control form-control-sm mr-3" id="sub_cat_id"'
						);

					} else {

						$conds['cat_id'] = $selected_cat_id;
						$options=array();
						$options[0]=get_msg('Prd_search_subcat');

						echo form_dropdown(
							'sub_cat_id',
							$options,
							set_value( 'sub_cat_id', show_data( $sub_cat_id ), false ),
							'class="form-control form-control-sm mr-3" id="sub_cat_id"'
						);
					}
				?>

		  	</div>

		  	<div class="form-group" style="padding-top: 3px;padding-right: 2px;">

				<?php
					$options=array();
					$options[0]=get_msg('itm_select_type');
					
					$itemtypes = $this->Itemtype->get_all( );
					foreach($itemtypes->result() as $type) {
						
						$options[$type->id]=get_msg($type->name);
					}
					
					echo form_dropdown(
						'item_type_id',
						$options,
						set_value( 'item_type_id', show_data( $item_type_id ), false ),
						'class="form-control form-control-sm mr-3" id="item_type_id"'
					);
				?> 

		  	</div>

<!--		  	<div class="form-group" style="padding-top: 3px;padding-right: 2px;">

				<?php
					$options=array();
					$options[0]=get_msg('itm_select_price');
					
					$pricetypes = $this->Pricetype->get_all( );
					foreach($pricetypes->result() as $price) {
						
						$options[$price->id]=$price->name;
					}
					
					echo form_dropdown(
						'item_price_type_id',
						$options,
						set_value( 'item_price_type_id', show_data( $item_price_type_id ), false ),
						'class="form-control form-control-sm mr-3" id="item_price_type_id"'
					);
				?> 

		  	</div>-->

<!--		  	<div class="form-group mr-3" style="padding-top: 3px;padding-right: 2px;">

				<?php
					$options=array();
					$options[0]=get_msg('itm_select_currency');
					
					$currencies = $this->Currency->get_all( );
					foreach($currencies->result() as $currency) {
						
						$options[$currency->id]=$currency->currency_short_form;
					}
					
					echo form_dropdown(
						'item_currency_id',
						$options,
						set_value( 'item_currency_id', show_data( $item_currency_id ), false ),
						'class="form-control form-control-sm mr-3" id="item_currency_id"'
					);
				?> 

		  	</div>-->

<!--		  	<div class="form-group" style="padding-top: 3px;padding-right: 2px;">

				<?php
					$options=array();
					$options[0]=get_msg('itm_select_location');
					
					$locations = $this->Itemlocation->get_all( );
					foreach($locations->result() as $location) {
						
						$options[$location->id]=$location->name;
					}
					
					echo form_dropdown(
						'item_location_id',
						$options,
						set_value( 'item_location_id', show_data( $item_location_id ), false ),
						'class="form-control form-control-sm mr-3" id="item_location_id"'
					);
				?> 

		  	</div>-->
            <?php
                $state_options[0]=get_msg('select_state');
                foreach($addresses->result() as $address) {
                    $state_options[strtolower(trim($address->state))] = ucfirst(trim($address->state));
                }
                $city_options2[0]=get_msg('select_city_filter');
                if(isset($search_state)) {
                    $city_arr = $this->db->select('DISTINCT(city)')->from('bs_addresses')->like('state', $search_state)->get();
                    foreach($city_arr->result() as $city) {
                        $city_options2[strtolower(trim($city->city))] = ucfirst(trim($city->city));
                    }
                } 
            ?>
            <div class="form-group" style="padding-right: 3px;">

                <?php
                    echo form_dropdown(
                        'state_dd',
                        $state_options,
                        set_value( 'state_dd', show_data(@$search_state), false ),
                        'class="form-control form-control-sm mr-3" id="state_dd"'
                    );
                ?>

            </div>
            <div class="form-group" style="padding-right: 3px;">

                <?php
//                    $options2 = array_unique($options2);
                    echo form_dropdown(
                        'city_dd',
                        $city_options2,
                        set_value( 'city_dd', show_data(@$search_city), false ),
                        'class="form-control form-control-sm mr-3" id="city_dd"'
                    );
                ?>

            </div>
            <div class="form-group" style="padding-right: 3px;">

                <?php
                    $user_options2[0]=get_msg('select_owner');
                    foreach($item_owners->result() as $owner) {
                        $user_options2[$owner->user_id] = ucfirst(trim($owner->user_name));
                    }
                    echo form_dropdown(
                        'user_dd',
                        $user_options2,
                        set_value( 'user_dd', show_data(@$search_user), false ),
                        'class="form-control form-control-sm mr-3" id="user_dd"'
                    );
                ?>

            </div>

		  	<div class="form-group" style="padding-top: 3px;padding-right: 5px;">
			  	<button type="submit" value="submit" name="submit" class="btn btn-sm btn-primary">
			  		<?php echo get_msg( 'btn_search' )?>
			  	</button>
		  	</div>
		
			<div class="form-group" style="padding-top: 3px;">
			  	<a href='<?php echo $module_site_url .'/index';?>' class='btn btn-sm btn-primary'>
					<?php echo get_msg( 'btn_reset' )?>
				</a>
		  	</div>

		</div>
	</div>

</div>

<div class="row my-3">	
	<!-- end form-inline -->
	<div class="col-9"></div>

	<div class='col-3'>
		<div class="form-group">
			<a href='<?php echo $module_site_url .'/add';?>' class='btn btn-sm btn-primary pull-right'>
				<span class='fa fa-plus'></span> 
				<?php echo get_msg( 'prd_add' )?>
			</a>
		</div>
	</div>
</div>
<?php echo form_close(); ?>

<script>
	
<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>
	function jqvalidate() {
		$('#cat_id').on('change', function() {

			var catId = $(this).val();
			
			$.ajax({
				url: '<?php echo $module_site_url . '/get_all_sub_categories/';?>' + catId,
				method: 'GET',
				dataType: 'JSON',
				success:function(data){
					$('#sub_cat_id').html("");
					$.each(data, function(i, obj){
					    $('#sub_cat_id').append('<option value="'+ obj.id +'">' + obj.name + '</option>');
					});
					$('#name').val($('#name').val() + " ").blur();
				}
			});
		});
        $('#state_dd').on('change', function() {
			var state_dd = $(this).val();
			
			$.ajax({
				url: '<?php echo $module_site_url . '/get_all_city/';?>' + state_dd,
				method: 'GET',
				dataType: 'JSON',
				success:function(data){
					$('#city_dd').html("");
					$.each(data, function(i, obj){
					    $('#city_dd').append('<option value="'+ $.trim(obj.city.toLowerCase()) +'">' + $.trim(obj.city) + '</option>');
					});
					$('#name').val($('#name').val() + " ").blur();
				}
			});
		});
}
	<?php endif; ?>
</script>