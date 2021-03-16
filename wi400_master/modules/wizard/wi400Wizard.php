<?php

	if (!isset($_SESSION["WI400_WIZARD"])){

		// Inizializzazione wizard
		if (isset($_GET["f"]) && trim($_GET["f"]) != ""){
			
			$wi400Wizard = new wi400Wizard($_GET["f"]);
			
		} else{
			
			echo "FATAL ERROR: WIZARD NON DEFINITO!";
			exit();
			
		}
	}
	
	$wizardSteps = $wi400Wizard->getSteps();

	if ($messageContext->getSeverity() != "ERROR"){
		if (isset($_GET["f"]) && trim($_GET["f"]) == "PREV"
				&& $wi400Wizard->getCounter() > 0){
			
			$wi400Wizard->setCounter($wi400Wizard->getCounter() - 1);
			
		}else if (isset($_GET["f"]) && trim($_GET["f"]) == "NEXT" 
				&& ($wi400Wizard->getCounter() + 1) < sizeof($wizardSteps)){
				
			$wi400Wizard->setCounter($wi400Wizard->getCounter() + 1);
		}
	}
	
	if (isset($_GET["f"]) && trim($_GET["f"]) == "END"){

		$wizardStep = $wi400Wizard->getEnd();
		$actionContext->setAction($wizardStep['action']);
		if (isset($wizardStep['form'])){
			$actionContext->setForm($wizardStep['form']);
		}
		if (isset($wizardStep['gateway'])){
			$actionContext->setGateway($wizardStep['gateway']);
		}
		sessionUnregister("WI400_WIZARD");

	}else{
		
		$stepCounter = 0;
		foreach ($wi400Wizard->getSteps() as $wizardStep){
			if ($wi400Wizard->getCounter() == $stepCounter){
				$actionContext->setAction($wizardStep['action']);
				if (isset($wizardStep['form'])){
					$actionContext->setForm($wizardStep['form']);
				}
				if (isset($wizardStep['gateway'])){
					$actionContext->setGateway($wizardStep['gateway']);
				}
				$wi400Wizard->setCurrentStep($wizardStep);
				break;
			}
			$stepCounter++;
		}
		
		$_SESSION["WI400_WIZARD"] = $wi400Wizard;
		
	}
	//header("Location: ".$appBase."index.php?t=".$actionContext->getAction()."&f=".$actionContext->getForm()."&g=".$actionContext->getGateway());
	goHeader($appBase."index.php?t=".$actionContext->getAction()."&f=".$actionContext->getForm()."&g=".$actionContext->getGateway());
	exit();
	
?>