function checkboxlimit(checkgroup, limit, officenumber){
	var checkgroup=checkgroup;
	var limit=limit;
	for (var i=0; i<checkgroup.length; i++){
		checkgroup[i].onclick=function(){
		var checkedcount=0;
		for (var i=0; i<checkgroup.length; i++)
			checkedcount+=(checkgroup[i].checked)? 1 : 0;
		if (checkedcount>limit){
                        document.getElementById('hiddenCandidateWarningBox_'+officenumber).style.display="block";
			this.checked=false;
		}
                else{
                        document.getElementById('hiddenCandidateWarningBox_'+1).style.display="none";
                }
            }
	}
}

