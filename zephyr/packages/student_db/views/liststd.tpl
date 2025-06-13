<fieldset><legend>all students</legend>
<table width="100%" border="0" cellspacing="1" cellpadding="5" class="lb">
  <tr>
    <td width="23%"><strong>Name</strong></td>
    <td width="20%"><strong>Roll</strong></td>
    <td width="23%"><strong>Class</strong></td>
    <td width="20%"><strong>Blood Group </strong></td>
    <td width="14%"><strong>Action</strong></td>
  </tr>
  {foreach item=std from=$students}
  <tr>
    <td>&nbsp;{$std.name}</td>
    <td>&nbsp;{$std.roll}</td>
    <td>&nbsp;{$std.class}</td>
    <td>&nbsp;{$std.blood_grp}</td>
    <td><a href="javascript:load_action_smartly('edit','{$std.roll}','canvas');">Edit</a> | <a href="javascript:delete_std('{$std.roll}');">Delete</a> </td>
  </tr>
  {/foreach}
</table>
</fieldset>