<?php 

 
class withdrawalController{

 
    function withdrawalForm(){
        global $baseDir;
        require BaseDir::getFullPath('pages/user/withdrawal_form.php');
    }

}
   