<style>
    .table thead th{
        font-size: 14px;
    }
</style>
<div class="table-responsive animated fadeInRight">
	<table id="reported_item_table" class="table m-0 table-striped">
        <thead>
            <tr>
                <th><?php echo get_msg('no'); ?></th>
                <th><?php echo get_msg('Date'); ?></th>
                <th><?php echo get_msg('Item Name'); ?></th>
                <th><?php echo get_msg('Reported User'); ?></th>
                <th><?php echo get_msg('Item User'); ?></th>
                <th><?php echo get_msg('Reason'); ?></th>
                <th><?php echo get_msg('Report Status'); ?></th>
            </tr>
        </thead>
        <tbody>
            
        
	
	<?php $count = $this->uri->segment(4) or $count = 0; ?>

	<?php if ( !empty( $itemreport ) && count( $itemreport->result()) > 0 ): ?>

		<?php foreach($itemreport->result() as $val): ?>
			<?php
                $item_detail = $this->Item->get_one( $val->operation_id);
            ?>
			<tr>
				<td><?php echo ++$count;?></td>
				<td ><?php echo $val->added_date;?></td>
				<td ><?php echo $item_detail->title; ?></td>
				<td ><?php echo $this->User->get_one( $val->user_id)->user_name; ?></td>
				<td ><?php echo $this->User->get_one( $item_detail->added_user_id)->user_name; ?></td>
                <td ><?php echo (!empty($val->reason_id) && !is_null($val->reason_id)) ? $this->Reasons->get_one($val->reason_id)->name : $val->other_reason;?></td>
				<td ><?php echo $val->status;?></td>
			</tr>

		<?php endforeach; ?>

	<?php else: ?>
			
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

	<?php endif; ?>
        </tbody>
</table>
</div>

