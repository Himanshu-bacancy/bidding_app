<div class="table-responsive animated fadeInRight">
  <table class="table m-0 table-striped">
    <tr>
      <th><?php echo get_msg('no'); ?></th>
      <th><?php echo get_msg('item_name'); ?></th>
      <th><?php echo get_msg('start_date_label'); ?></th>
      <th><?php echo get_msg('end_date_label'); ?></th>
      <th><?php echo get_msg('amount_label'); ?></th>
      <th><?php echo get_msg('payment_method_label'); ?></th>
      <th><?php echo get_msg('status_label'); ?></th>
      <th><?php echo get_msg('lbl_view'); ?></th>
    </tr>
    
  
  <?php $count = $this->uri->segment(4) or $count = 0; ?>

  <?php if ( !empty( $transactions) && count( $transactions->result()) > 0 ): ?>

      <?php foreach($transactions->result() as $trans): ?>
        
        <tr>
          <td><?php echo ++$count;?></td>
          <td><?php echo $this->Item->get_one( $trans->item_id )->title;?></td>
          <td><?php echo $trans->start_date;?></td>
          <td><?php echo $trans->end_date; ?></td>
          <td><?php echo $trans->amount.$this->Paid_config->get_one('pconfig1')->currency_symbol; ?></td>
          <td><?php echo $trans->payment_method; ?></td>
          <td>
            <?php 
              $today_date = date('Y-m-d h:i:s');
              if ($today_date >= $trans->start_date && $today_date <= $trans->end_date) {
            ?>
                <button class="btn btn-sm btn-warning">
                <?php echo get_msg('progress_label'); ?></button>
            <?php   
              } elseif ($today_date > $trans->start_date && $today_date > $trans->end_date) {
            ?>
              <button class="btn btn-sm btn-success">
                <?php echo get_msg('finished_label'); ?></button>
            <?php
              } else {
            ?>
              <button class="btn btn-sm btn-default">
                <?php echo get_msg('not_yet_start_label'); ?></button>
            <?php
              }
            ?>
          </td>

          <?php if ( $this->ps_auth->has_access( EDIT )): ?>
      
            <td>
              <a href='<?php echo $module_site_url .'/edit/'. $trans->id; ?>'>
                <i class='fa fa-eye'></i>
              </a>
            </td>
          
          <?php endif; ?>
          
        </tr>

      <?php endforeach; ?>

  <?php else: ?>
      
    <?php $this->load->view( $template_path .'/partials/no_data' ); ?>

  <?php endif; ?>

</table>
</div>