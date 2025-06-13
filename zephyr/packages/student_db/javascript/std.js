function insert_std()
{
	serialized = group_serialize("name","roll","class","blood_grp");
	load_action_smartly("insert_student",serialized, "stdlist");
}

function update_std()
{
	serialized = group_serialize("name","roll","class","blood_grp");
	load_action_smartly("update_student",serialized, "canvas");
}

function delete_std(roll)
{
	if(confirm("Are you sure that you want to delete it?")==true)
	{	
		load_action_smartly("delete_student",roll, "stdlist");
	}
}