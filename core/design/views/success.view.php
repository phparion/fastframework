<?php
############################
# Written By: Haroon Ahmad #
# www.haroonahmad.co.uk    #
############################
#######################################################################
# success message view
########################################################################

 if(is_array($this->success) && count($this->success) > 0 ) {
?>
<div style="border:#009900 4px solid; padding:10px;" class="sliding">

<h2>Operation successful!</h2>
<ol>
<?php
 	  foreach($this->success as $msg) { 
	   ?>
        <li><?php echo $msg; ?></li>
       <?php
	  } //end foreach 
?>
 </ol>
</div>

<?php
 }
?>