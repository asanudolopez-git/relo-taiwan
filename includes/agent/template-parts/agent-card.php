<?php
/**
 * Template part for displaying agent card
 */

// Get the agent ID from the arguments
$agent_id = isset($args['agent_id']) ? $args['agent_id'] : null;

if (!$agent_id) {
    return;
}

// Get agent data
$agent = get_post($agent_id);
if (!$agent || $agent->post_type !== 'agent') {
    return;
}

// Get agent meta data
$phone = get_post_meta($agent_id, 'phone', true);
$office_phone = get_post_meta($agent_id, 'office_phone', true);
$line = get_post_meta($agent_id, 'line', true);
$languages = get_post_meta($agent_id, 'languages', true);
$email = get_post_meta($agent_id, 'email', true);
?>

<div class="inquiry-card">
    <h2 class="inquiry-title">Make An Inquiry</h2>
    <p class="inquiry-subtitle">For more details or to arrange a viewing, please contact us</p>

    <div class="agent-section">
        <div class="agent-photo">
            <?php if (has_post_thumbnail($agent_id)) : ?>
                <?php echo get_the_post_thumbnail($agent_id, 'thumbnail'); ?>
            <?php else : ?>
                <?php $agent_photo = get_post_meta($agent_id, 'agent_photo', true); ?>
                <?php if ($agent_photo) : ?>
                    <img src="<?php echo esc_url($agent_photo); ?>" alt="<?php echo esc_attr($agent->post_title); ?>">
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="agent-header">
            <span class="agent-label">Agent</span>
            <h3 class="agent-name"><?php echo esc_html($agent->post_title); ?></h3>
            <?php if ($languages) : ?>
                <div class="agent-languages">
                    <?php echo esc_html($languages); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="contact-list">
        <?php if ($phone) : ?>
            <a href="tel:<?php echo esc_attr($phone); ?>" class="contact-item">
                <span class="contact-icon mobile-icon"></span>
                <span class="contact-label">Phone</span>
                <span class="contact-value"><?php echo esc_html($phone); ?></span>
            </a>
        <?php endif; ?>

        <?php if ($office_phone) : ?>
            <a href="tel:<?php echo esc_attr($office_phone); ?>" class="contact-item">
                <span class="contact-icon phone-icon"></span>
                <span class="contact-label">住宅二部</span>
                <span class="contact-value"><?php echo esc_html($office_phone); ?></span>
            </a>
        <?php endif; ?>

        <?php if ($email) : ?>
            <a href="mailto:<?php echo esc_attr($email); ?>" class="contact-item">
                <span class="contact-icon email-icon"></span>
                <span class="contact-label">Email</span>
                <span class="contact-value"><?php echo esc_html($email); ?></span>
            </a>
        <?php endif; ?>

        <?php if ($line) : ?>
            <a href="#" class="contact-item line-item">
                <span class="contact-icon line-icon"></span>
                <span class="contact-value">Add My LINE</span>
            </a>
        <?php endif; ?>
    </div>

    <?php if ($agent->post_content) : ?>
        <div class="agent-description">
            <?php echo wp_kses_post($agent->post_content); ?>
        </div>
    <?php endif; ?>
</div>
