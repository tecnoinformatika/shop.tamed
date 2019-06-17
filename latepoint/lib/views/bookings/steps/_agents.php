<div class="step-agents-w latepoint-step-content">
  <ul class="os-agents">
    <?php $show_agent_bio = OsSettingsHelper::steps_show_agent_bio(); ?>
    <?php if(OsSettingsHelper::is_on('allow_any_agent')){ ?>
      <li>
        <a href="#" data-agent-id="<?php echo LATEPOINT_ANY_AGENT; ?>">
          <span class="agent-img-w" style="background-image: url(<?php echo LATEPOINT_IMAGES_URL . 'default-avatar.jpg'; ?>);"></span>
          <span class="agent-info">
            <span class="agent-name"><?php _e('Any Agent', 'latepoint'); ?></span>
          </span>
        </a>
      </li>
    <?php } ?>
    <?php foreach($agents as $agent){ ?>
      <li>
        <a href="#" data-agent-id="<?php echo $agent->id; ?>">
          <span class="agent-img-w" style="background-image: url(<?php echo $agent->avatar_url; ?>);"></span>
          <span class="agent-info">
            <span class="agent-name"><?php echo $agent->full_name; ?></span>
          </span>
        </a>
        <?php if($show_agent_bio){ ?>
          <div class="os-agent-details-btn" data-agent-id="<?php echo $agent->id; ?>"><?php _e('View Details', 'latepoint'); ?></div>
        <?php } ?>
      </li>
    <?php } ?>
  </ul>
</div>
<?php if($show_agent_bio){ ?>
  <?php foreach($agents as $agent){ ?>
    <div class="os-agent-bio-popup" id="osAgentBioPopup<?php echo $agent->id; ?>" data-agent-id="<?php echo $agent->id; ?>">
      <a href="#" class="os-agent-bio-close"><span><?php _e('Close Details', 'latepoint'); ?></span><i class="latepoint-icon latepoint-icon-common-01"></i></a>
      <div class="agent-bio-popup-head" style="background-image: url(<?php echo $agent->bio_image_url; ?>)">
        <h3><?php echo $agent->full_name; ?></h3>
        <div class="agent-bio-title"><?php echo $agent->title; ?></div>
      </div>
      <div class="agent-bio-popup-content">
        <img class="bio-curve" src="<?php echo LATEPOINT_IMAGES_URL.'white-curve.png'; ?>" alt="">
        <div class="agent-bio-popup-features">
          <?php foreach($agent->features_arr as $feature){ ?>
            <div class="agent-bio-popup-feature">
              <div class="agent-bio-popup-feature-value"><?php echo $feature['value']; ?></div>
              <div class="agent-bio-popup-feature-label"><?php echo $feature['label']; ?></div>
            </div>
          <?php } ?>
        </div>
        <div class="agent-bio-popup-content-i">
          <?php echo $agent->bio; ?>
        </div>
      </div>
    </div>
  <?php } ?>
<?php } ?>
<?php if(!isset($no_params)) include '_booking_params.php'; ?>