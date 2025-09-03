<?php
class Glint_Email_Automation_Cron {

    public function __construct() {
        add_action('init', array($this, 'schedule_events'));
        add_action('glint_email_automation_daily_cron', array($this, 'process_scheduled_emails'));
    }

    public function schedule_events() {
        if (!wp_next_scheduled('glint_email_automation_daily_cron')) {
            wp_schedule_event(time(), 'daily', 'glint_email_automation_daily_cron');
        }
    }

    public function process_scheduled_emails() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';
        
        // Get current date
        $current_date = current_time('mysql');
        
        // Find emails that need to be sent today
        $emails = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE status = 'scheduled' AND next_sending_date <= %s",
                $current_date
            )
        );
        
        foreach ($emails as $email) {
            $this->send_automated_email($email);
        }
    }

    private function send_automated_email($email) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';
        
        // Get automation settings
        $automation_id = $email->automation_id;
        $settings = get_post_meta($automation_id, '_glint_email_automation_settings', true);
        
        if (!$settings) {
            // Automation might have been deleted
            error_log('Automation not found for email ID: ' . $email->email_id);
            return;
        }
        
        // Get the automation post content (email body)
        $automation_post = get_post($automation_id);
        $email_body = $automation_post->post_content;
        
        // Build the full email content
        $email_content = $this->build_email_content($settings, $email_body);
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $settings['send_from_title'] . ' <' . $settings['send_from_email'] . '>'
        );
        
        if (!empty($settings['bcc'])) {
            $headers[] = 'Bcc: ' . $settings['bcc'];
        }
        
        // Send email
        $sent = wp_mail(
            $email->customer_email,
            $settings['email_title'],
            $email_content,
            $headers
        );
        
        if ($sent) {
            // Update email record
            $next_sending_date = date('Y-m-d H:i:s', strtotime("+{$settings['days_between']} days"));
            
            $wpdb->update(
                $table_name,
                array(
                    'total_email_sent' => $email->total_email_sent + 1,
                    'last_sent_date' => current_time('mysql'),
                    'next_sending_date' => $next_sending_date
                ),
                array('email_id' => $email->email_id),
                array('%d', '%s', '%s'),
                array('%d')
            );
            
            error_log('Email sent successfully to: ' . $email->customer_email);
        } else {
            error_log('Failed to send email to: ' . $email->customer_email);
        }
    }

    private function build_email_content($settings, $email_body) {
        $content = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . esc_html($settings['email_title']) . '</title>
        </head>
        <body>
            <div class="email-container">';
        
        // Add email head
        if (!empty($settings['email_head'])) {
            $content .= '<div class="email-head">' . wpautop(wp_kses_post($settings['email_head'])) . '</div>';
        }
        
        // Add email body
        $content .= '<div class="email-body">' . wpautop(wp_kses_post($email_body)) . '</div>';
        
        // Add email footer
        if (!empty($settings['email_footer'])) {
            $content .= '<div class="email-footer">' . wpautop(wp_kses_post($settings['email_footer'])) . '</div>';
        }
        
        $content .= '</div>
        </body>
        </html>';
        
        return $content;
    }
}