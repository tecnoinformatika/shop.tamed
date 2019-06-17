<div class="step-locations-w latepoint-step-content">
  <ul class="os-locations">
    <?php foreach($locations as $location){ ?>
      <li>
        <a href="#" data-location-id="<?php echo $location->id; ?>">
          <span class="location-img-w" style="background-image: url(<?php echo $location->selection_image_url; ?>);"></span>
          <span class="location-name-w">
            <span class="location-name"><?php echo $location->name; ?></span>
            <?php if($location->full_address){ ?>
              <span class="location-desc"><?php echo $location->full_address; ?></span>
            <?php } ?>
          </span>
        </a>
      </li>
    <?php } ?>
  </ul>
</div>
<?php if(!isset($no_params)) include '_booking_params.php'; ?>