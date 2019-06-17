<?php 

try{
		
	//-------------------------------------------------------------
		
	if(is_admin()){		//load admin part
		require_once $currentFolder."/unitecreator_admin.php";
		require_once GlobalsUC::$pathProvider . "provider_admin.class.php";
		require_once GlobalsUC::$pathProvider . "core/provider_core_admin.class.php";
		
		new UniteProviderCoreAdminUC_VC($mainFilepath);
		
	}else{		//load front part
		require_once GlobalsUC::$pathProvider . "provider_front.class.php";
		require_once GlobalsUC::$pathProvider . "core/provider_core_front.class.php";
		
		new UniteProviderCoreFrontUC_VC($mainFilepath);
	}

	
	}catch(Exception $e){
		$message = $e->getMessage();
		$trace = $e->getTraceAsString();
		echo "Unlimited Addons Error: <b>".$message."</b>";
	
		if(GlobalsUC::SHOW_TRACE == true)
			dmp($trace);
	}
	
	
?>