<?php 

$i = 0;
    $lengthOfCandidates = count($candidatesbyoffice);
    $limit = 1000; //what?!
    ?>
     <script type="text/javascript">
  
function checkboxlimit(checkgroup, limit, officenumber){
	var checkgroup=checkgroup;
	var limit=limit;
	for (var i=0; i<checkgroup.length; i++){
		checkgroup[i].onclick=function(){
		var checkedcount=0;
		for (var i=0; i<checkgroup.length; i++)
			checkedcount+=(checkgroup[i].checked)? 1 : 0;
		if (checkedcount>limit){

                        document.getElementById("hiddenCandidateWarningBox_"+officenumber).style.display="block";
			this.checked=false;
                        var makeBoxDisappear=setInterval(function() {boxdisappears()}, 5000);
                        function boxdisappears() {
                            document.getElementById("hiddenCandidateWarningBox_"+officenumber).style.display="none";
                        }		}
                else{
                        document.getElementById("hiddenCandidateWarningBox_"+officenumber).style.display="none";
                }
            }
	}
}
<?php
    //while($i < $lengthOfCandidates){
    foreach($candidatesbyoffice as $cbo){
        $limit = $cbo->number;
        $officenumber = $cbo->id;
        echo 'checkboxlimit(document.querySelectorAll(".candidate_office_'.$i.'"), '. $limit .' , ' . $officenumber .');';
        $i++;
    }
    echo '</script>';