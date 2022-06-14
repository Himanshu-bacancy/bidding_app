<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#topic-form').validate({
			rules:{
				name:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$topic->id; ?>"
				},
                topic_id:{
					indexCheck : ""
				}
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_subtopic_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_subtopic_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_subtopic_exist' ) ;?>."
				},
                topic_id:{
					indexCheck: "<?php echo get_msg( 'err_topic_select' ) ;?>"
				}
			}
		});
        jQuery.validator.addMethod("indexCheck",function( value, element ) {
			
			if(value == 0) {
				 return false;
			} else {
				 return true;
			};

			
	 	});
		// custom validation
		jQuery.validator.addMethod("blankCheck",function( value, element ) {
			
			   if(value == "") {
			    	return false;
			   } else {
			    	return true;
			   }
		})
	}

	<?php endif; ?>

    function runAfterJQ()
    {
        CKEDITOR.editorConfig = function( config ) {
            config.toolbarGroups = [
                { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
                { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
                { name: 'links', groups: [ 'links' ] },
                { name: 'insert', groups: [ 'insert' ] },
                { name: 'forms', groups: [ 'forms' ] },
                { name: 'tools', groups: [ 'tools' ] },
                { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
                { name: 'others', groups: [ 'others' ] },
                '/',
                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
                { name: 'styles', groups: [ 'styles' ] },
                { name: 'colors', groups: [ 'colors' ] },
                { name: 'about', groups: [ 'about' ] }
            ];

            config.removeButtons = 'BGColor,Styles,Format,Anchor,Image,Table,HorizontalRule,SpecialChar,Source,NumberedList,BulletedList,Indent,Outdent';
        };
        CKEDITOR.replace( 'content' );
    }

</script>