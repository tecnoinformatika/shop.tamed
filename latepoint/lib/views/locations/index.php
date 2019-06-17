<div class="os-locations-list">
	<?php 
	if($locations){
	foreach($locations as $location){ ?>
		<div class="os-location os-location-status-active">
		  <div class="os-location-header">
		    <h3 class="location-name"><?php echo $location->name; ?></h3>
		  </div>
		  <div class="os-location-body">
		    <?php if($location->full_address){ ?>
			    <div class="os-location-address">
            <iframe width="100%" height="140" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.it/maps?q=<?php echo urlencode($location->full_address); ?>&output=embed"></iframe>
					</div>
        <?php } ?>
		    <div class="os-location-agents">
		      <div class="label"><?php _e('Agents:', 'latepoint'); ?></div>
		      <?php if($location->connected_agents){ ?>
			      <div class="agents-avatars">
			      <?php foreach($location->connected_agents as $agent){ ?>
	            <div class="agent-avatar" style="background-image: url(<?php echo $agent->avatar_url; ?>)"></div>
	          <?php } ?>
	          </div>
	        <?php } ?>
		    </div>
		    <div class="os-location-info">
		      <div class="location-info-row">
		        <div class="label"><?php _e('Address:', 'latepoint'); ?></div>
		        <div class="value"><strong><?php echo $location->full_address; ?></strong></div>
		      </div>
		    </div>
		  </div>
		  <div class="os-location-foot">
		    <a href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('locations', 'edit_form'), ['id' => $location->id] ); ?>" class="latepoint-btn latepoint-btn-block latepoint-btn-primary">
		      <i class="latepoint-icon latepoint-icon-edit-3"></i>
		      <span><?php _e('Edit Location', 'latepoint'); ?></span>
		    </a>
		  </div>
		</div>
	<?php }
	} ?>
	<a class="create-location-link-w" href="<?php echo OsRouterHelper::build_link(OsRouterHelper::build_route_name('locations', 'new_form') ) ?>">
	  <div class="create-location-link-i">
	    <div class="add-location-graphic-w">
	      <div class="add-location-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
	    </div>
	    <div class="add-location-label"><?php _e('Add Location', 'latepoint'); ?></div>
	  </div>
	</a>
</div>