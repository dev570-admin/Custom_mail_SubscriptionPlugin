<?php
/*
Plugin Name: Email Subscription Plugin
*/


require_once(plugin_dir_path(__FILE__) . 'PHPMailer/src/PHPMailer.php');
require_once(plugin_dir_path(__FILE__) . 'PHPMailer/src/SMTP.php');
require_once(plugin_dir_path(__FILE__) . 'PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Shortcode for the subscription form
function subscription_form_shortcode() {
    ob_start();
    // Run this code to delete all subscribed emails
    //delete_option('subscribed_emails');

    ?>
    <form method="post" action="">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <input type="submit" value="Subscribe">
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('subscription_form', 'subscription_form_shortcode');

// Handle form submissions and send email notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = sanitize_email($_POST['email']);

    // Save the email to your database or mailing list
    // Example: Save to the options table

    $subscribed_emails = get_option('subscribed_emails', array());

    // Check if the email is already in the list
    if (!in_array($email, $subscribed_emails, true)) {
        $subscribed_emails[] = $email;
        update_option('subscribed_emails', $subscribed_emails);
    } else {
        // Handle case where the email already exists (optional)
        // You may want to display an error message or take other actions
        // For now, let's just log a message
        //error_log('You are already our subscriber: ' . $email);
        echo '<script>alert("You are already our subscriber ")</script>';
    }
}


// Send email notification to subscribers when a new post is published
function send_post_notification($ID, $post) {
    $subscribed_emails = get_option('subscribed_emails', array());

    if (!empty($subscribed_emails)) {
        //$subject = 'New Post Notification';
        //$post_url = urldecode(get_permalink($ID));
        //$message = 'Check out our new post: ' . get_the_title($ID) . '<br><a href="http://localhost/wp/' . urlencode(get_the_title($ID)) . '">Read More</a>';

        $subject = 'New Post Notification';
        $post_title = get_the_title($ID);
        $post_url = urldecode(get_permalink($ID));
        $url_friendly_title = sanitize_title($post_title);

        $message = 'Check out our new post: ' . $post_title . '<br><a href="http://localhost/wp/' . $url_friendly_title . '">Read More</a>';
        
        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        foreach ($subscribed_emails as $subscriber_email) {
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = getenv('mridhasurajit570@gmail.com');// Replace with your Gmail username
                $mail->Password = 'cxvs wmwo gdcy mveq'; // Replace with your Gmail password
                //$mail->Password = getenv('GMAIL_PASSWORD'); // Retrieve the environment variable

                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Set the recipient, subject, and message
                $mail->setFrom('noreply@gmail.com', 'Webcraft'); // Replace with your email and name
                $mail->addAddress($subscriber_email);
                $mail->Subject = $subject;
                $mail->isHTML(true); // Set email format to HTML
                $mail->Body = $message;
                $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set to DEBUG_OFF for production

                // Send the email
                $mail->send();
            } catch (Exception $e) {
                error_log('Error sending email: ' . $mail->ErrorInfo);
            } finally {
                // Clear recipients
                $mail->clearAddresses();
            }
        }
    }
}



add_action('publish_post', 'send_post_notification', 10, 2);

// // Display the list of subscribed emails
// function subscription_list_shortcode() {
//     $subscribed_emails = get_option('subscribed_emails', array());

//     ob_start();
//     if (!empty($subscribed_emails)) {
//         echo '<ul>';
//         foreach ($subscribed_emails as $email) {
//             echo '<li>' . esc_html($email) . '</li>';
//         }
//         echo '</ul>';
//     } else {
//         echo 'No subscriptions yet.';
//     }
//     return ob_get_clean();
// }

// add_shortcode('subscription_list', 'subscription_list_shortcode');

// Add a menu item to the admin menu
function subscription_menu() {
    add_menu_page(
        'Subscribers',
        'Subscribers',
        'manage_options',
        'subscribers',
        'subscription_admin_page'
    );
}
add_action('admin_menu', 'subscription_menu');

// Create the admin page content
function subscription_admin_page() {
    ?>
    <div class="wrap">
        <h2>Subscribers</h2>
        <?php
        $subscribed_emails = get_option('subscribed_emails', array());

        if (!empty($subscribed_emails)) {
            echo '<ul>';
            foreach ($subscribed_emails as $email) {
                echo '<li>' . esc_html($email) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No subscriptions yet.';
        }
        ?>
    </div>
    <?php
}

