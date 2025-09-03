<div class="glint-automation-settings">
    <p>
        <label>Triggered By:</label>
        <select name="triggered_by" id="triggered_by">
            <option value="product" <?php selected($settings['triggered_by'], 'product'); ?>>Product</option>
            <option value="category" <?php selected($settings['triggered_by'], 'category'); ?>>Category</option>
        </select>
    </p>

    <p id="product_trigger_field" style="display: <?php echo $settings['triggered_by'] === 'product' ? 'block' : 'none'; ?>">
        <label>Products:</label>
        <select name="product_trigger[]" multiple class="glint-select2" style="width: 100%;">
            <?php 
            if (!empty($settings['product_trigger'])) :
                foreach ($settings['product_trigger'] as $product_id) : 
                    $product = wc_get_product($product_id);
                    if ($product) : ?>
                        <option value="<?php echo $product_id; ?>" selected><?php echo $product->get_name(); ?></option>
                    <?php endif;
                endforeach;
            endif; 
            ?>
        </select>
    </p>

    <p id="category_trigger_field" style="display: <?php echo $settings['triggered_by'] === 'category' ? 'block' : 'none'; ?>">
        <label>Categories:</label>
        <select name="category_trigger[]" multiple class="glint-select2" style="width: 100%;">
            <?php 
            if (!empty($settings['category_trigger'])) :
                foreach ($settings['category_trigger'] as $category_id) : 
                    $category = get_term($category_id, 'product_cat');
                    if ($category && !is_wp_error($category)) : ?>
                        <option value="<?php echo $category_id; ?>" selected><?php echo $category->name; ?></option>
                    <?php endif;
                endforeach;
            endif; 
            ?>
        </select>
    </p>

    <!-- Other fields remain the same -->
    <p><label>From Name:</label><input type="text" name="send_from_title" value="<?php echo esc_attr($settings['send_from_title']); ?>"></p>
    <p><label>From Email:</label><input type="email" name="send_from_email" value="<?php echo esc_attr($settings['send_from_email']); ?>"></p>
    <p><label>BCC:</label><input type="text" name="bcc" value="<?php echo esc_attr($settings['bcc']); ?>"></p>
    <p><label>Days After Purchase:</label><input type="number" name="days_after" value="<?php echo esc_attr($settings['days_after']); ?>"></p>
    <p><label>Days Between Attempts:</label><input type="number" name="days_between" value="<?php echo esc_attr($settings['days_between']); ?>"></p>
    <p><label>Email Title:</label><input type="text" name="email_title" value="<?php echo esc_attr($settings['email_title']); ?>" style="width: 100%;"></p>
    <p><label>Email Head:</label><textarea name="email_head" rows="5" style="width: 100%;"><?php echo esc_textarea($settings['email_head']); ?></textarea></p>
    <p><label>Email Footer:</label><textarea name="email_footer" rows="5" style="width: 100%;"><?php echo esc_textarea($settings['email_footer']); ?></textarea></p>
</div>