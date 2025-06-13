<h1>All Students</h1>
{foreach item=student from=$students name=std}
{$smarty.foreach.std.iteration}.{$student.name}<br>
{/foreach}