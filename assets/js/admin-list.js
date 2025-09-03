jQuery(document).ready(function($) {
    // Handle delete button clicks
    $(document).on('click', '.delete-email-record', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var emailId = button.data('email-id');
        var nonce = button.data('nonce');
        
        if (!confirm('Are you sure you want to delete this email record? This action cannot be undone.')) {
            return false;
        }
        
        // Show loading indicator
        button.text('Deleting...').prop('disabled', true);
        
        $.ajax({
            type: 'POST',
            url: glint_email_automation.ajax_url,
            data: {
                action: 'glint_delete_email_record',
                email_id: emailId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('email record is deleted');
                    // Fade out and remove the row
                    $('#email-row-' + emailId).fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error: ' + response.data);
                    button.text('Delete').prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred while trying to delete the record.');
                button.text('Delete').prop('disabled', false);
            }
        });
    });
});