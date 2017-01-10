<?php
############################
# Written By: Haroon Ahmad #
# www.haroonahmad.co.uk    #
############################
#######################################################################
# errors display view
########################################################################

 if(is_array($this->errors) && count($this->errors) > 0 ) {
?>
<div style="border:#900 4px solid; padding:10px;">

<h2>Please fix the following errors,</h2>
<ol>
<?php
 	  foreach($this->errors as $error) { 
	   ?>
        <li><?php echo $error; ?></li>
       <?php
	  } //end foreach 
?>
 </ol>
</div>

<?php
 }
?>