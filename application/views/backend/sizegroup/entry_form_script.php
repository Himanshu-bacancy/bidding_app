<script>
	<?php if ($this->config->item('client_side_validation') == true) : ?>

		function jqvalidate() {
			$('#sizegroup-form').validate({
				rules: {
					name: {
						blankCheck: "",
						minlength: 3,
						remote: "<?php echo $module_site_url . '/ajx_exists/' . @$sizegroup->id; ?>"
					}
				},
				messages: {
					name: {
						blankCheck: "<?php echo get_msg('err_sizegroup_name'); ?>",
						minlength: "<?php echo get_msg('err_sizegroup_len'); ?>",
						remote: "<?php echo get_msg('err_sizegroup_exist'); ?>."
					}
				}
			});
			// custom validation
			jQuery.validator.addMethod("blankCheck", function(value, element) {
				if (value == "") {
					return false;
				} else {
					return true;
				}
			});
		}

	<?php endif; ?>
</script>