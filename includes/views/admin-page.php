<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Scheduled Email Automations', 'glint-wc-email-automation'); ?></h1>
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="glint-email-automation">
                <select name="status">
                    <option value="all" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'all'); ?>>
                        <?php _e('All Statuses', 'glint-wc-email-automation'); ?>
                    </option>
                    <option value="scheduled" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'scheduled'); ?>>
                        <?php _e('Scheduled', 'glint-wc-email-automation'); ?>
                    </option>
                    <option value="sent" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'sent'); ?>>
                        <?php _e('Sent', 'glint-wc-email-automation'); ?>
                    </option>
                </select>
                <input type="submit" class="button" value="<?php _e('Filter', 'glint-wc-email-automation'); ?>">
            </form>
        </div>
        
        <div class="tablenav-pages">
            <?php
            $total_pages = ceil($total_items / $per_page);
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $current_page
            ));
            ?>
        </div>
    </div>
    
    <form method="post">
        <?php wp_nonce_field('bulk-emails'); ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </td>
                    <th><?php _e('ID', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Automation', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Customer', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Email', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Purchase Date', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Next Send Date', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Total Sent', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Status', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Actions', 'glint-wc-email-automation'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)) : ?>
                    <tr>
                        <td colspan="10"><?php _e('No scheduled emails found.', 'glint-wc-email-automation'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($items as $item) : 
                        $automation = get_post($item->automation_id);
                    ?>
                        <tr id="email-row-<?php echo $item->email_id; ?>">
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="email_ids[]" value="<?php echo $item->email_id; ?>">
                            </th>
                            <td><?php echo $item->email_id; ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($item->automation_id); ?>">
                                    <?php echo $automation ? $automation->post_title : __('Automation Deleted', 'glint-wc-email-automation'); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($item->customer_name); ?></td>
                            <td><?php echo esc_html($item->customer_email); ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($item->purchase_date)); ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($item->next_sending_date)); ?></td>
                            <td><?php echo $item->total_email_sent; ?></td>
                            <td>
                                <span class="status-label status-<?php echo $item->status; ?>">
                                    <?php echo ucfirst($item->status); ?>
                                </span>
                            </td>
                            <td>
                                <button class="button button-small delete-email-record" 
                                        data-email-id="<?php echo $item->email_id; ?>"
                                        data-nonce="<?php echo wp_create_nonce('delete_email_record_' . $item->email_id); ?>">
                                    <?php _e('Delete', 'glint-wc-email-automation'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-2">
                    </td>
                    <th><?php _e('ID', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Automation', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Customer', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Email', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Purchase Date', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Next Send Date', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Total Sent', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Status', 'glint-wc-email-automation'); ?></th>
                    <th><?php _e('Actions', 'glint-wc-email-automation'); ?></th>
                </tr>
            </tfoot>
        </table>
        
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <select name="action">
                    <option value="-1"><?php _e('Bulk Actions', 'glint-wc-email-automation'); ?></option>
                    <option value="delete"><?php _e('Delete', 'glint-wc-email-automation'); ?></option>
                    <option value="mark_sent"><?php _e('Mark as Sent', 'glint-wc-email-automation'); ?></option>
                    <option value="mark_scheduled"><?php _e('Mark as Scheduled', 'glint-wc-email-automation'); ?></option>
                </select>
                <input type="submit" class="button action" value="<?php _e('Apply', 'glint-wc-email-automation'); ?>">
            </div>
            
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                ?>
            </div>
        </div>
    </form>
</div>