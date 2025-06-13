// JavaScript Document
function confirmSubmit(){
	var agree=confirm("Are you sure you wish to cancel this room? \n Kindly note that, by cancelling, it means you don\'t want University Accomodation \n and your room will be available to other students. \n You wont be considered for accommodation this Academic Year.");
	if(agree)
		return true ;
	else
		return false ;
}