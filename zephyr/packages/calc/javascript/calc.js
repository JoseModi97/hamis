function add()
{
	serialized = group_serialize("a","b");
	load_action_value('add', serialized,'result');
}