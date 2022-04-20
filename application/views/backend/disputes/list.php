<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('user_name'); ?></th>
			<th><?php echo get_msg('user_email'); ?></th>
			<th><?php echo get_msg('user_phone'); ?></th>
			<th><?php echo get_msg('dispute_status'); ?></th>
			<th><?php echo get_msg('is_seller_dispute'); ?></th>
<!--			<th><?php echo get_msg('image'); ?></th>-->
			<th><?php echo get_msg('message'); ?></th>
            <th><span class="th-title"><?php echo get_msg('action')?></span></th>
		</tr>
		
	
	<?php $count = $this->uri->segment(4) or $count = 0; ?>

	<?php if ( !empty( $orders ) && count( $orders->result()) > 0 ): ?>

		<?php foreach($orders->result() as $val): ?>
			
			<tr>
				<td><?php echo ++$count;?></td>
				<td ><?php echo $val->name;?></td>
				<td ><?php echo $val->email;?></td>
				<td ><?php echo $val->phone;?></td>
				<td ><?php echo $val->status;?></td>
				<td ><?php echo ($val->is_seller_generate) ? 'yes' :'no';?></td>
<!--				<td ><?php 
                $dispute_imges = $this->db->from('core_images')->where('img_parent_id', $val->order_id)->get()->result_array();
                $path = '';
                foreach ($dispute_imges as $key => $value) {
                    $path = $path.'<br><img src="'.base_url('uploads/'.$value['img_path']).'" alt="Girl in a jacket" width="25" height="25">
';
                }
                echo $path;?></td>-->
				<td ><?php echo $val->message;?></td>

                <td>
                    <?php
                    $class = 'disabled';
                    if ($val->status == 'initiate'): 
                        $class = '';
                    endif ?>
                    <button class="btn btn-sm btn-success accept "  <?php echo $class;?> id='<?php echo $val->id;?>' order_id='<?php echo $val->order_id;?>'>
                    <?php echo get_msg( 'Accept' ); ?></button>
                    <button class="btn btn-sm btn-danger reject"  <?php echo $class;?> id='<?php echo $val->id;?>' order_id='<?php echo $val->order_id;?>'>
                    <?php echo get_msg( 'Reject' ); ?></button>
                </td>
				

			</tr>

		<?php endforeach; ?>

	<?php else: ?>
			
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

	<?php endif; ?>

</table>
</div>

